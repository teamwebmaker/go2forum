<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'is_phone_verification_enabled',
        'is_email_verification_enabled'
    ];

    protected $casts = [
        'is_phone_verification_enabled' => 'boolean',
        'is_email_verification_enabled' => 'boolean',
    ];


    public static function shouldEmailVerify(): bool
    {
        return (bool) static::value('is_email_verification_enabled');
    }


    public static function shouldPhoneVerify(): bool
    {
        return (bool) static::value('is_phone_verification_enabled');
    }
}
