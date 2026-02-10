<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    public const KIND_TOPIC = 'topic';
    public const KIND_PRIVATE = 'private';

    protected $fillable = [
        'kind',
        'topic_id',
        'direct_user1_id',
        'direct_user2_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function directUser1()
    {
        return $this->belongsTo(User::class, 'direct_user1_id');
    }

    public function directUser2()
    {
        return $this->belongsTo(User::class, 'direct_user2_id');
    }

    public function isTopic(): bool
    {
        return $this->kind === self::KIND_TOPIC;
    }

    public function isPrivate(): bool
    {
        return $this->kind === self::KIND_PRIVATE;
    }
}
