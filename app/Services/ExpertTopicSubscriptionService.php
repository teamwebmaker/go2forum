<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpertTopicSubscriptionService
{
    /**
     * Keep expert subscription state internally consistent.
     *
     * Personal topic subscriptions live in a separate table and are never
     * changed here.
     */
    public function normalize(User $user): void
    {
        if (! $user->is_expert) {
            DB::table('expert_topic_subscriptions')
                ->where('user_id', $user->id)
                ->delete();

            if ($user->expert_subscribes_to_all_topics) {
                $user->forceFill([
                    'expert_subscribes_to_all_topics' => false,
                ])->saveQuietly();
            }

            return;
        }

        if ($user->expert_subscribes_to_all_topics) {
            DB::table('expert_topic_subscriptions')
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
