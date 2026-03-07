<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

    protected $casts = [
        'edited_at' => 'datetime',
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
                ->select(['id', 'conversation_id'])
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
}
