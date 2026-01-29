<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            AdsSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            PublicDocumentsSeeder::class,
            TopicSeeder::class,
        ]);
    }
}
