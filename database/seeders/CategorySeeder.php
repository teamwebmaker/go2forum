<?php

namespace Database\Seeders;

use App\Models\Ads;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $ads = Ads::all();

        for ($i = 1; $i <= 3; $i++) {
            $category = new Category([
                'id' => $i,
                'name' => ucfirst($faker->unique()->words(2, true)),
                'order' => $i,
                'visibility' => true,
            ]);

            if ($ads->isNotEmpty()) {
                $category->ad_id = $ads->random()->id;
            }

            $category->save();
        }
    }
}
