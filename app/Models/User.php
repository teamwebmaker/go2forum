<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;


class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmailTrait;


    public const AVATAR_DIR = 'images/avatars/';


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'image',
        'is_expert',
        'is_top_commentator',
        'password',
    ];

    /**
     * Get the user's initials (first letters of name and surname, uppercased).
     */
    public function getInitialsAttribute(): string
    {
        $parts = array_filter([
            $this->name ? mb_substr(trim((string) $this->name), 0, 1) : null,
            $this->surname ? mb_substr(trim((string) $this->surname), 0, 1) : null,
        ]);

        if (empty($parts)) {
            return '?';
        }

        return mb_strtoupper(implode('', $parts), 'UTF-8');
    }

    /**
     * Full avatar URL (public disk).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $path = ltrim($this->image, '/');
        if (!str_contains($path, '/')) {
            $path = trim(self::AVATAR_DIR, '/') . '/' . $path;
        }

        return $disk->url($path);
    }

    /**
     * Normalize email casing before persisting to keep uniqueness consistent.
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = is_string($value)
            ? mb_strtolower(trim($value))
            : $value;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',

            'is_expert' => 'boolean',
            'is_top_commentator' => 'boolean',
        ];
    }

    // public function markEmailAsVerified()
    // {
    //     return $this->forceFill([
    //         'email_verified_at' => $this->freshTimestamp(),
    //     ])->save();
    // }

    // Temporary check only email
    public function isVerified()
    {
        $emailEnabled = Settings::value('is_email_verification_enabled') ?? false;
        $phoneEnabled = Settings::value('is_phone_verification_enabled') ?? false;

        if ($emailEnabled && $phoneEnabled) {
            return $this->hasVerifiedEmail() && !is_null($this->phone_verified_at);
        }

        if ($emailEnabled) {
            return $this->hasVerifiedEmail();
        }

        if ($phoneEnabled) {
            return !is_null($this->phone_verified_at);
        }

        return true;
    }

    public function shouldVerify()
    {
        if (Settings::value('is_email_verification_enabled') || Settings::value('is_phone_verification_enabled')) {
            return true;
        } else {
            return false;
        }
    }
}
