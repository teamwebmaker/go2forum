<?php

namespace App\Http\Controllers;

use App\Models\PublicDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicDocumentController extends Controller
{
    public function open(PublicDocument $publicDocument, Request $request): StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        $this->abortIfNotVisible($publicDocument, $user);

        if ($publicDocument->requires_auth_to_view) {
            $this->trackRestrictedView($publicDocument, $user?->id);
        }

        if (!$publicDocument->canBeViewedBy($user)) {
            abort(403);
        }

        $fileLocation = $publicDocument->resolveStorageLocation();
        if ($fileLocation) {
            return Storage::disk($fileLocation['disk'])->response(
                $fileLocation['path'],
                basename($fileLocation['path']),
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($fileLocation['path']) . '"',
                    'X-Content-Type-Options' => 'nosniff',
                    'Cache-Control' => 'private, no-store, no-cache, max-age=0',
                    'Pragma' => 'no-cache',
                ]
            );
        }

        if (filled($publicDocument->link) && filter_var($publicDocument->link, FILTER_VALIDATE_URL)) {
            return redirect()->away($publicDocument->link, 302, [
                'Cache-Control' => 'private, no-store, no-cache, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        }

        abort(404);
    }

    public function download(PublicDocument $publicDocument, Request $request): StreamedResponse
    {
        $this->abortIfNotVisible($publicDocument, $request->user());

        if (!$publicDocument->canBeDownloadedBy($request->user())) {
            abort(403);
        }

        $fileLocation = $publicDocument->resolveStorageLocation();
        if (!$fileLocation) {
            abort(404);
        }

        return Storage::disk($fileLocation['disk'])->download(
            $fileLocation['path'],
            basename($fileLocation['path']),
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, no-cache, max-age=0',
                'Pragma' => 'no-cache',
            ]
        );
    }

    public function trackView(PublicDocument $publicDocument, Request $request)
    {
        $this->abortIfNotVisible($publicDocument, $request->user());

        if ($publicDocument->requires_auth_to_view) {
            $this->trackRestrictedView($publicDocument, $request->user()?->id);
        }

        return response()->noContent()->withHeaders([
            'Cache-Control' => 'private, no-store, no-cache, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function abortIfNotVisible(PublicDocument $publicDocument, ?User $user): void
    {
        if (!$publicDocument->visibility && !($user?->role === 'admin')) {
            abort(404);
        }
    }

    private function trackRestrictedView(PublicDocument $publicDocument, ?int $userId): void
    {
        if (!$publicDocument->requires_auth_to_view) {
            return;
        }

        if (!$userId) {
            PublicDocument::query()->whereKey($publicDocument->id)->increment('views_count');
            return;
        }

        DB::transaction(function () use ($publicDocument, $userId): void {
            $inserted = DB::table('public_document_user_views')->insertOrIgnore([
                'public_document_id' => $publicDocument->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted > 0) {
                PublicDocument::query()->whereKey($publicDocument->id)->increment('views_count');
            }
        });
    }
}
