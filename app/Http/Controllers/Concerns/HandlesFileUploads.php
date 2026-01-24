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

        if ($oldFile) {
            Storage::disk('public')->delete($oldFile);
        }

        // $storedPath = $relativeDir ? $relativeDir . '/' . $fileName : $fileName;

        return $fileName;
    }

    /**
     * Remove a previously stored uploaded file if it exists.
     */
    protected function deleteUploadedFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $normalized = ltrim($relativePath, '/');

        Storage::disk('public')->delete($normalized);
    }
}
