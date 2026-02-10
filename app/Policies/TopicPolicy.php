<?php

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;
use App\Support\TopicAccessRules;

class TopicPolicy
{
    public function create(User $user): bool
    {
        return !$user->is_blocked;
    }

    public function view(?User $user, Topic $topic): bool
    {
        return TopicAccessRules::canView($topic);
    }

    public function post(User $user, Topic $topic): bool
    {
        if ($user->is_blocked) {
            return false;
        }

        return $this->view($user, $topic)
            && $topic->status === 'active';
    }

    public function subscribe(User $user, Topic $topic): bool
    {
        return $this->view($user, $topic);
    }
}
