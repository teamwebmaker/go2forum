<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['title' => 'დაწყება ფორუმზე', 'user_id' => 1, 'category_id' => 1, 'status' => 'active'],
            ['title' => 'საუკეთესო რესურსები 2026', 'user_id' => 2, 'category_id' => 2, 'status' => 'active'],
            ['title' => 'კითხვა რეკომენდაციებზე', 'user_id' => 3, 'category_id' => 3, 'status' => 'closed'],
            ['title' => 'ბაგების შეტყობინება', 'user_id' => 1, 'category_id' => 2, 'status' => 'active'],
            ['title' => 'იდეების გაზიარება', 'user_id' => 2, 'category_id' => 1, 'status' => 'active'],
        ];

        foreach ($topics as $data) {
            Topic::create($data + [
                'messages_count' => rand(0, 12),
                'pinned' => rand(0, 1),
                'visibility' => true,
            ]);
        }
    }
}
