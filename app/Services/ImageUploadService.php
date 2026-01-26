<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class ImageUploadService
{
    /**
     * Store (or re-store) an image as WebP (optionally resize + optimize) on the given disk/path.
     * Accepts either an UploadedFile or a path relative to the disk.
     * Returns the stored relative path (e.g. images/ads/uuid.webp).
     */
    public static function handleOptimizedImageUpload(
        UploadedFile|string $file,
        string $destinationPath,
        ?string $oldFile = null,
        int $webpQuality = 80,
        bool $optimize = true,
        string $disk = 'public',
        ?int $maxWidth = null,
        ?int $maxHeight = null,
    ): string {
        $relativeDir = trim($destinationPath, '/');

        $fileName = Str::uuid()->toString() . '.webp';
        $storedPath = $relativeDir ? $relativeDir . '/' . $fileName : $fileName;

        // Read source image
        if ($file instanceof UploadedFile) {
            $img = Image::read($file->getRealPath());
        } else {
            $sourcePath = ltrim($file, '/');
            $absolute = Storage::disk($disk)->path($sourcePath);
            if (!file_exists($absolute)) {
                throw new \RuntimeException("Source image [$absolute] not found.");
            }
            $img = Image::read($absolute);
        }

        // Optional bounding-box resize while keeping aspect ratio
        if ($maxWidth || $maxHeight) {
            $img = $img->scaleDown(
                width: $maxWidth,
                height: $maxHeight,
            );
        }

        $ok = Storage::disk($disk)->put($storedPath, (string) $img->toWebp($webpQuality));
        if (!$ok) {
            throw new \RuntimeException("Failed to store image at [$storedPath] on [$disk] disk.");
        }

        if ($optimize) {
            ImageOptimizer::optimize(Storage::disk($disk)->path($storedPath));
        }

        // Delete old file if provided
        if ($oldFile) {
            $oldNormalized = ltrim($oldFile, '/');
            if ($relativeDir && !str_contains($oldNormalized, '/')) {
                $oldNormalized = $relativeDir . '/' . $oldNormalized;
            }
            if ($oldNormalized !== $storedPath) {
                Storage::disk($disk)->delete($oldNormalized);
            }
        }

        return $storedPath;
    }
}
