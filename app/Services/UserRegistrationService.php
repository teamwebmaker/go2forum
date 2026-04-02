<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRegistrationService
{
    /**
     * Register a new user account.
     *
     * @param array<string, mixed> $attributes
     */
    public function register(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            return User::create([
                'name' => $attributes['name'],
                'surname' => $attributes['surname'],
                'nickname' => $attributes['nickname'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'],
                'password' => Hash::make((string) $attributes['password']),
            ]);
        });
    }
}
