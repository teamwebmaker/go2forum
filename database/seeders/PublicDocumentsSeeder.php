<?php

namespace Database\Seeders;

use App\Models\PublicDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'name' => 'Terms of Service',
                'document' => 'test.pdf',
                'link' => null,
            ],
            [
                'name' => 'Privacy Policy',
                'document' => null,
                'link' => 'https://example.com/privacy',
            ],
            [
                'name' => 'Community Guidelines',
                'document' => 'test2.pdf',
                'link' => null,
            ],
        ];

        foreach ($documents as $doc) {
            DB::table('public_documents')->updateOrInsert(
                ['name' => $doc['name']],
                [
                    'document' => $doc['document'],
                    'link' => $doc['link'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            if (!filled($doc['document'])) {
                continue;
            }

            $path = PublicDocument::STORAGE_DIR . '/' . ltrim((string) $doc['document'], '/');
            $sourceDisk = Storage::disk('public');
            $targetDisk = Storage::disk(PublicDocument::STORAGE_DISK);

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
    }
}
