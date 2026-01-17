<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class PhoneVerificationOtp extends Model
{
    protected $fillable = [
        'user_id',
        'otp_hash',
        'phone_at_issue',
        'valid_for',
        'attempts',
        'issued_at',
        'last_sent_at',
        'expires_at',
        'confirmed_at',
        'confirmation_ip',
        'confirmation_user_agent',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];


    public function scopeActiveFor(Builder $q, int $userId, string $validFor): Builder
    {
        return $q->where('user_id', $userId)
            ->where('valid_for', $validFor)
            ->whereNull('confirmed_at')
            ->where('expires_at', '>', now());
    }

    public function scopeExpiredFor(Builder $q, int $userId, string $validFor): Builder
    {
        return $q->where('user_id', $userId)
            ->where('valid_for', $validFor)
            ->whereNull('confirmed_at')
            ->where('expires_at', '<', now());
    }

    public function scopeActiveForPhone(Builder $q, int $userId): Builder
    {
        return $q->activeFor($userId, 'phone');
    }
}
