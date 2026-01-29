<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['id' => 1, 'name' => 'Test', 'surname' => 'Example', 'email' => 'test@test.com', 'password' => 'test@test.com'],
            ['id' => 2, 'name' => 'Bob', 'surname' => 'Builder', 'email' => 'bob@example.com', 'password' => 'bob@example.com'],
            ['id' => 3, 'name' => 'Cara', 'surname' => 'Coder', 'email' => 'cara@example.com', 'password' => 'cara@example.com'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['id' => $data['id']],
                $data + [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
