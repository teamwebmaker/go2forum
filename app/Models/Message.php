<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const EDIT_WINDOW_HOURS = 24;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'original_content',
        'edited_content',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
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
