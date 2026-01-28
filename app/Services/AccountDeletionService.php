<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AccountDeletionService
{
    public function delete(User $user): void
    {
        $userId = $user->getAuthIdentifier();
        $currentSessionId = Session::getId();

        // Remove all session rows for this user (database driver)
        if (config('session.driver') === 'database') {
            $table = config('session.table', 'sessions');
            DB::table($table)
                ->where(function ($q) use ($userId, $currentSessionId) {
                    $q->where('user_id', $userId)
                        ->orWhere('id', $currentSessionId);
                })
                ->delete();
        }

        Auth::logout();

        // Delete user record (cleanup hooks handle avatar removal)
        $user->delete();

        Session::invalidate();
        Session::regenerateToken();
    }
}
