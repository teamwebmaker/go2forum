<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserRegistrationService
{
    /**
     * Register a new user or restore a soft-deleted one by email.
     *
     * @param array<string, mixed> $attributes
     * @return array{user: User, reactivated: bool}
     */
    public function registerOrReactivate(array $attributes, string $signupUnavailableMessage): array
    {
        $reactivatedViaSignup = false;

        $user = DB::transaction(function () use ($attributes, $signupUnavailableMessage, &$reactivatedViaSignup): User {
            $existingUser = User::query()
                ->withTrashed()
                ->where('email', (string) $attributes['email'])
                ->lockForUpdate()
                ->first();

            if ($existingUser && $existingUser->trashed()) {
                if ($existingUser->role !== 'user') {
                    throw ValidationException::withMessages([
                        'email' => [$signupUnavailableMessage],
                    ]);
                }

                $existingUser->restore();
                $existingUser->forceFill([
                    'name' => $attributes['name'],
                    'surname' => $attributes['surname'],
                    'nickname' => $attributes['nickname'],
                    'phone' => $attributes['phone'],
                    'password' => Hash::make((string) $attributes['password']),
                    // Re-verification required after reactivation.
                    'email_verified_at' => null,
                    'phone_verified_at' => null,
                ])->save();

                $reactivatedViaSignup = true;

                return $existingUser->fresh();
            }

            return User::create([
                'name' => $attributes['name'],
                'surname' => $attributes['surname'],
                'nickname' => $attributes['nickname'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'],
                'password' => Hash::make((string) $attributes['password']),
            ]);
        });

        return [
            'user' => $user,
            'reactivated' => $reactivatedViaSignup,
        ];
    }
}
