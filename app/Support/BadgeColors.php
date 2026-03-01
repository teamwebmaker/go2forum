<?php

namespace App\Support;

class BadgeColors
{
    /**
     * Return neutral Tailwind text color class when user has any badge.
     */
    /**
     * Accepts a User or any object with is_expert / is_top_commentator flags.
     */
    public static function forUser(object|null $user): ?string
    {
        if (!$user) {
            return null;
        }

        $isExpert = (bool) ($user->is_expert ?? false);
        $isTop = (bool) ($user->is_top_commentator ?? false);

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

    /**
     * Return icon name for badge based on user flags.
     */
    public static function iconForUser(object|null $user): ?string
    {
        if (!$user) {
            return null;
        }

        $isExpert = (bool) ($user->is_expert ?? false);
        $isTop = (bool) ($user->is_top_commentator ?? false);

        if ($isExpert) {
            return 'star';
        }

        if ($isTop) {
            return 'check-badge';
        }

        return null;
    }
}
