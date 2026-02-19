<?php

namespace App\Jobs;

use App\Services\MessageServiceSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPrivateMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $senderId,
        protected int $messageId,
        protected int $conversationId,
    ) {
    }

    public function handle(MessageServiceSupport $support): void
    {
        $support->notifyPrivateReceiver(
            $this->senderId,
            $this->messageId,
            $this->conversationId
        );
    }
}

