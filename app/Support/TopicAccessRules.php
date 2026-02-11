<?php

namespace App\Support;

use App\Models\Topic;
use Illuminate\Support\Facades\Auth;

class TopicAccessRules
{
    public static function canView(Topic $topic): bool
    {
        $topic->loadMissing('category');

        return Auth::check() && (bool) $topic->visibility
            && (bool) $topic->category?->visibility
            && $topic->status !== 'disabled';
    }
}

