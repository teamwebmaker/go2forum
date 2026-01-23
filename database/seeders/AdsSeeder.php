<?php

namespace Database\Seeders;

use App\Models\Ads;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AdsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 3; $i++) {
            Ads::create([
                'name' => $faker->sentence(3),
                'image' => "ad_image-{$i}.jpg",
                'link' => $faker->url(),
                'visibility' => true,
            ]);
        }
    }
}
