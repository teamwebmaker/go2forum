<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ExpertTopicSubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReconcileExpertTopicSubscriptions extends Command
{
    protected $signature = 'experts:reconcile-topic-subscriptions {--chunk=500 : Users processed per database chunk}';

    protected $description = 'Remove stale or conflicting expert-managed topic subscription state';

    public function handle(ExpertTopicSubscriptionService $service): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $normalized = 0;

        User::query()
            ->withTrashed()
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('is_expert', false)
                        ->where(function (Builder $query): void {
                            $query->where('expert_subscribes_to_all_topics', true)
                                ->orWhereExists(fn ($query) => $query
                                    ->selectRaw('1')
                                    ->from('expert_topic_subscriptions')
                                    ->whereColumn('expert_topic_subscriptions.user_id', 'users.id'));
                        });
                })->orWhere(function (Builder $query): void {
                    $query->where('is_expert', true)
                        ->where('expert_subscribes_to_all_topics', true)
                        ->whereExists(fn ($query) => $query
                            ->selectRaw('1')
                            ->from('expert_topic_subscriptions')
                            ->whereColumn('expert_topic_subscriptions.user_id', 'users.id'));
                });
            })
            ->chunkById($chunkSize, function ($users) use ($service, &$normalized): void {
                DB::transaction(function () use ($users, $service, &$normalized): void {
                    foreach ($users as $user) {
                        $service->normalize($user);
                        $normalized++;
                    }
                });
            });

        $this->info("Normalized expert topic subscriptions for {$normalized} user(s).");

        return self::SUCCESS;
    }
}
