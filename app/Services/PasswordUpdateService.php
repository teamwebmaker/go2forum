<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PasswordUpdateService
{
    public function update(User $user, string $newPassword): void
    {
        $currentSessionId = Session::getId();

        $user->forceFill([
            'password' => $newPassword,
            'remember_token' => Str::random(60),
        ])->save();

        // Remove all other sessions for this user when using database sessions
        if (config('session.driver') === 'database') {
            $table = config('session.table', 'sessions');

            DB::table($table)
                ->where('user_id', $user->getAuthIdentifier())
                ->where('id', '!=', $currentSessionId)
                ->delete();
        }

        Auth::logoutOtherDevices($newPassword);

        // Rotate current session id and delete the old row (true)
        Session::regenerate(true);
    }
}
