<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Store an uploaded file and optionally replace the previous one.
     * Returns the stored relative path.
     */
    public static function handleFileUpload(
        UploadedFile $file,
        string $destinationPath,
        ?string $oldFile = null,
        string $disk = 'public',
    ): string {
        $relativeDir = trim($destinationPath, '/');

        $fileName = sprintf(
            '%s.%s',
            Str::uuid()->toString(),
            $file->getClientOriginalExtension()
        );

        Storage::disk($disk)->putFileAs($relativeDir, $file, $fileName);

        if ($oldFile) {
            $oldNormalized = ltrim($oldFile, '/');
            if ($relativeDir && !str_contains($oldNormalized, '/')) {
                $oldNormalized = $relativeDir . '/' . $oldNormalized;
            }
            Storage::disk($disk)->delete($oldNormalized);
        }

        return $relativeDir ? $relativeDir . '/' . $fileName : $fileName;
    }


    /**
     * Remove a previously stored uploaded file if it exists.
     */
    public static function deleteUploadedFile(
        ?string $relativePath,
        ?string $fallbackDir = null,
        string $disk = 'public',
    ): void
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
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                break;
            }
        }
    }
}
