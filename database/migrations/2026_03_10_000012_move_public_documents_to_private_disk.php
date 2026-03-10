<?php

use App\Models\PublicDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration {
    public function up(): void
    {
        $sourceDisk = Storage::disk('public');
        $targetDisk = Storage::disk(PublicDocument::STORAGE_DISK);

        DB::table('public_documents')
            ->select(['id', 'document'])
            ->whereNotNull('document')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($sourceDisk, $targetDisk): void {
                foreach ($rows as $row) {
                    $filename = ltrim((string) $row->document, '/');
                    if ($filename === '') {
                        continue;
                    }

                    $path = PublicDocument::STORAGE_DIR . '/' . $filename;

                    if ($sourceDisk->exists($path) && !$targetDisk->exists($path)) {
                        $stream = $sourceDisk->readStream($path);
                        if ($stream !== false) {
                            $targetDisk->writeStream($path, $stream);
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }
                    }

                    if ($sourceDisk->exists($path)) {
                        $sourceDisk->delete($path);
                    }
                }
            });
    }

    public function down(): void
    {
        $sourceDisk = Storage::disk(PublicDocument::STORAGE_DISK);
        $targetDisk = Storage::disk('public');

        DB::table('public_documents')
            ->select(['id', 'document'])
            ->whereNotNull('document')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($sourceDisk, $targetDisk): void {
                foreach ($rows as $row) {
                    $filename = ltrim((string) $row->document, '/');
                    if ($filename === '') {
                        continue;
                    }

                    $path = PublicDocument::STORAGE_DIR . '/' . $filename;

                    if ($sourceDisk->exists($path) && !$targetDisk->exists($path)) {
                        $stream = $sourceDisk->readStream($path);
                        if ($stream !== false) {
                            $targetDisk->writeStream($path, $stream);
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }
                    }
                }
            });
    }
};

