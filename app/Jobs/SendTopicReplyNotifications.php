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

    public function __construct(
        protected int $senderId,
        protected int $messageId,
        protected int $topicId,
    ) {
    }

    public function handle(MessageServiceSupport $support): void
    {
        $support->notifyTopicSubscribers(
            $this->senderId,
            $this->messageId,
            $this->topicId
        );
    }
}

