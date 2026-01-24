<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        }
    }
}
