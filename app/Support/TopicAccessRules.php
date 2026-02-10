<?php

namespace App\Support;

use App\Models\Topic;

class TopicAccessRules
{
    public static function canView(Topic $topic): bool
    {
        $topic->loadMissing('category');

        return (bool) $topic->visibility
            && (bool) $topic->category?->visibility
            && $topic->status !== 'disabled';
    }
}

