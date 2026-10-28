<?php

namespace App\Jobs;

use App\Services\MessageServiceSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTopicReplyNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    // Keep this below the database queue's default 90-second retry_after.
    public int $timeout = 60;

    public function __construct(
        protected int $senderId,
        protected int $messageId,
        protected int $topicId,
    ) {}

    public function handle(MessageServiceSupport $support): void
    {
        $support->notifyTopicSubscribers(
            $this->senderId,
            $this->messageId,
            $this->topicId
        );
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }
}
