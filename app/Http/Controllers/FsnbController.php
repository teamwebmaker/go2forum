<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FsnbController extends Controller
{
    public function __invoke(?string $path = null): BinaryFileResponse
    {
        $basePath = realpath(resource_path('fsnb'));

        if ($basePath === false) {
            abort(404);
        }

        $relativePath = $path ?: 'index.html';
        $filePath = realpath($basePath.DIRECTORY_SEPARATOR.$relativePath);

        if (
            $filePath === false
            || ! str_starts_with($filePath, $basePath.DIRECTORY_SEPARATOR)
            || ! is_file($filePath)
        ) {
            abort(404);
        }

        $contentType = match (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            'html' => 'text/html; charset=UTF-8',
            'js' => 'text/javascript; charset=UTF-8',
            default => 'application/octet-stream',
        };

        return response()->file($filePath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="'.basename($filePath).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
