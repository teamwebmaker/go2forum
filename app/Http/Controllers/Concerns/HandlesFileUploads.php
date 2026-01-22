<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

        $relativePath = rtrim($destinationPath, '/');
        $relativePath = $relativePath ? $relativePath . '/' : '';
        $absolutePath = public_path($relativePath);

        // Ensure the destination exists before moving the file.
        File::ensureDirectoryExists($absolutePath);

        $fileName = sprintf(
            '%s.%s',
            Str::uuid()->toString(),
            $uploadedFile->getClientOriginalExtension()
        );

        $uploadedFile->move($absolutePath, $fileName);

        if ($oldFile) {
            $oldRelative = ltrim($oldFile, '/');
            $candidates = [
                public_path($oldRelative),
                public_path($relativePath . $oldRelative),
            ];

            foreach ($candidates as $path) {
                if (File::exists($path)) {
                    File::delete($path);
                    break;
                }
            }
        }

        return $relativePath . $fileName;
    }

    /**
     * Remove a previously stored uploaded file if it exists.
     */
    protected function deleteUploadedFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $relative = ltrim($relativePath, '/');
        $candidate = public_path($relative);

        if (File::exists($candidate)) {
            File::delete($candidate);
        }
    }
}
