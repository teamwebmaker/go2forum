<?php

namespace App\Support;

use App\Models\User;

class BadgeColors
{
    /**
     * Return Tailwind text color class for badge based on user flags.
     */
    /**
     * Accepts a User or any object with is_expert / is_top_commentator flags.
     */
    public static function forUser(object|null $user): ?string
    {
        if (!$user) {
            return null;
        }

        $isExpert = $user->is_expert;
        $isTop = $user->is_top_commentator;


        if ($isExpert && $isTop) {
            return 'text-orange-500';
        }

        if ($isExpert) {
            return 'text-sky-500';
        }

        if ($isTop) {
            return 'text-amber-500';
        }

        return null;
    }
}
