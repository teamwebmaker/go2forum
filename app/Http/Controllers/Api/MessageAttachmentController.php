<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageAttachment;
use App\Services\MessageServiceSupport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageAttachmentController extends Controller
{
    public function download(
        MessageAttachment $attachment,
        Request $request,
        MessageServiceSupport $messageServiceSupport
    ) {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $attachment->loadMissing('message.conversation');
        $conversation = $attachment->message?->conversation;

        if (!$conversation) {
            abort(404);
        }

        try {
            $messageServiceSupport->authorizeConversationRead($conversation, $user->id);
        } catch (AuthorizationException) {
            abort(403);
        }

        if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404);
        }

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name ?: basename($attachment->path),
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }
}
