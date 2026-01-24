<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileUploads
{
    /**
     * Store an uploaded file and optionally replace the previous one.
     */
    protected function handleFileUpload(
        Request $request,
        string $fieldName,
        string $destinationPath,
        ?string $oldFile = null,
    ): ?string {
        $uploadedFile = $request->file($fieldName);

        if (!$uploadedFile) {
            return null;
        }

        $relativeDir = trim($destinationPath, '/');

        $fileName = sprintf(
            '%s.%s',
            Str::uuid()->toString(),
            $uploadedFile->getClientOriginalExtension()
        );

        // Store on the public disk
        Storage::disk('public')->putFileAs($relativeDir, $uploadedFile, $fileName);

        // Normalize old file path (it might be stored as just the filename)
        if ($oldFile) {
            $oldNormalized = ltrim($oldFile, '/');
            if ($relativeDir && !str_contains($oldNormalized, '/')) {
                $oldNormalized = $relativeDir . '/' . $oldNormalized;
            }
            Storage::disk('public')->delete($oldNormalized);
        }

        $storedPath = $relativeDir ? $relativeDir . '/' . $fileName : $fileName;

        return $fileName;
    }

    /**
     * Remove a previously stored uploaded file if it exists.
     */
    protected function deleteUploadedFile(?string $relativePath, ?string $fallbackDir = null): void
    {
        if (!$relativePath) {
            return;
        }

        $normalized = ltrim($relativePath, '/');
        $candidates = [$normalized];

        if ($fallbackDir && !str_contains($normalized, '/')) {
            $candidates[] = trim($fallbackDir, '/') . '/' . $normalized;
        }

        foreach ($candidates as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                break;
            }
        }
    }
}
