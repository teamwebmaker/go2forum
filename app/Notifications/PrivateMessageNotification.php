<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PrivateMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Conversation $conversation,
        protected Message $message,
        protected User $sender,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $preview = $this->message->content
            ? Str::limit(strip_tags($this->message->content), 50)
            : 'გამოაგზავნა დანართი.';

        return [
            'conversation_id' => $this->conversation->id,
            'message_id' => $this->message->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->full_name ?? $this->sender->name,
            'preview' => $preview,
        ];
    }
}

