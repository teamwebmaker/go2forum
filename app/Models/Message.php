<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const EDIT_WINDOW_HOURS = 24;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'reply_to_message_id',
        'client_token',
        'content',
        'original_content',
        'edited_content',
        'edited_at',
        'is_trashed',
        'trashed_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
        'is_trashed' => 'boolean',
        'trashed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Message $message): void {
            if (!$message->reply_to_message_id) {
                return;
            }

            if ($message->exists && !$message->isDirty('reply_to_message_id') && !$message->isDirty('conversation_id')) {
                return;
            }

            // Note: relational CHECK constraints for self-referenced conversation parity
            // are not portable across our supported drivers, so this guard is enforced
            // in application logic (service + model event) as a second line of defense.
            $replyTarget = static::query()
                ->withTrashed()
                ->select(['id', 'conversation_id', 'is_trashed'])
                ->find($message->reply_to_message_id);

            if (!$replyTarget) {
                throw ValidationException::withMessages([
                    'reply_to_message_id' => ['არჩეული მესიჯი პასუხისთვის ვერ მოიძებნა.'],
                ]);
            }

            if ((int) $replyTarget->conversation_id !== (int) $message->conversation_id) {
                throw ValidationException::withMessages([
                    'reply_to_message_id' => ['პასუხის მესიჯი ამავე მიმოწერიდან უნდა იყოს.'],
                ]);
            }

            if ((bool) $replyTarget->is_trashed) {
                throw ValidationException::withMessages([
                    'reply_to_message_id' => ['არჩეული მესიჯი პასუხისთვის ვერ მოიძებნა.'],
                ]);
            }
        });
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(self::class, 'reply_to_message_id')->withTrashed();
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'reply_to_message_id');
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function likes()
    {
        return $this->hasMany(MessageLike::class, 'message_id');
    }

    public function isEditableBy(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        if ($this->trashed()) {
            return false;
        }

        if ((int) $this->sender_id !== $userId) {
            return false;
        }

        if (!$this->created_at) {
            return false;
        }

        return $this->created_at->greaterThanOrEqualTo(now()->subHours(self::EDIT_WINDOW_HOURS));
    }

    public function scopeNotTrashed($query)
    {
        return $query->where('is_trashed', false);
    }

    public function scopeOnlyInTrash($query)
    {
        return $query->where('is_trashed', true);
    }

    public function moveToTrash(): bool
    {
        $messageId = (int) $this->getKey();

        return DB::transaction(function () use ($messageId): bool {
            $message = static::query()
                ->withTrashed()
                ->whereKey($messageId)
                ->lockForUpdate()
                ->first();

            if (!$message) {
                return false;
            }

            if ($message->is_trashed) {
                return true;
            }

            $message->forceFill([
                'is_trashed' => true,
                'trashed_at' => now(),
            ])->save();

            static::syncConversationDerivedState((int) $message->conversation_id);

            return true;
        });
    }

    public function restoreFromTrash(): bool
    {
        $messageId = (int) $this->getKey();

        return DB::transaction(function () use ($messageId): bool {
            $message = static::query()
                ->withTrashed()
                ->whereKey($messageId)
                ->lockForUpdate()
                ->first();

            if (!$message) {
                return false;
            }

            if (!$message->is_trashed) {
                return true;
            }

            $message->forceFill([
                'is_trashed' => false,
                'trashed_at' => null,
            ])->save();

            static::syncConversationDerivedState((int) $message->conversation_id);

            return true;
        });
    }

    public static function syncConversationDerivedState(int $conversationId): void
    {
        if ($conversationId <= 0) {
            return;
        }

        $conversation = Conversation::query()
            ->select(['id', 'kind', 'topic_id'])
            ->whereKey($conversationId)
            ->first();

        if (!$conversation) {
            return;
        }

        $lastMessageAt = static::query()
            ->where('conversation_id', $conversationId)
            ->whereNull('deleted_at')
            ->where('is_trashed', false)
            ->max('created_at');

        Conversation::query()
            ->whereKey($conversationId)
            ->update([
                'last_message_at' => $lastMessageAt,
            ]);

        if ($conversation->isTopic() && $conversation->topic_id) {
            $topicMessagesCount = static::query()
                ->where('conversation_id', $conversationId)
                ->whereNull('deleted_at')
                ->where('is_trashed', false)
                ->count();

            Topic::query()
                ->whereKey($conversation->topic_id)
                ->update([
                    'messages_count' => $topicMessagesCount,
                ]);
        }
    }
}
