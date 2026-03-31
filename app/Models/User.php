<?php

namespace App\Models;

use Filament\Notifications\Notification as FilamentNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmailTrait, SoftDeletes;


    // Default directory (on "public" disk) where user avatar files are stored.
    public const AVATAR_DIR = 'avatars/';

    protected $fillable = [
        'name',
        'surname',
        'nickname',
        'email',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'image',
        'is_expert',
        'is_top_commentator',
        'is_blocked',
        'password',
    ];

    /**
     * Accessor: Get the user's initials.
     *
     * Builds initials from the first character of `name` and `surname`,
     * @return string e.g. "JD" or "?"
     */
    public function getInitialsAttribute(): string
    {
        // Collect first letters (if present) from name and surname
        $parts = array_filter([
            $this->name ? mb_substr(trim((string) $this->name), 0, 1) : null,
            $this->surname ? mb_substr(trim((string) $this->surname), 0, 1) : null,
        ]);

        // Fallback when no initials can be derived
        if (empty($parts)) {
            return '?';
        }

        // Uppercase
        return mb_strtoupper(implode('', $parts), 'UTF-8');
    }

    /**
     * Accessor: Get the full public URL for user's avatar.
     *
     * Supports:
     * - `image` stored as a full/relative path like "avatars/foo.jpg"
     * - `image` stored as a filename only like "foo.jpg" (prepends AVATAR_DIR)
     *
     * Returns null when user has no avatar.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        // No avatar stored
        if (!$this->image) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        // Normalize leading slashes so Storage::url behaves consistently
        $path = ltrim($this->image, '/');

        // If only a filename is stored (no folder separators), assume default avatar folder
        if (!str_contains($path, '/')) {
            $path = trim(self::AVATAR_DIR, '/') . '/' . $path;
        }

        // Return a public URL to the stored file
        return $disk->url($path);
    }

    /**
     * Mutator: Normalize email casing before saving.
     *
     * This helps enforce uniqueness rules consistently (e.g. "A@B.com" == "a@b.com").
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = is_string($value)
            ? mb_strtolower(trim($value))
            : $value;
    }

    public function setNicknameAttribute($value): void
    {
        $this->attributes['nickname'] = is_string($value)
            ? mb_strtolower(trim($value))
            : $value;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            // Timestamps
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'deleted_at' => 'datetime',

            // Automatically hash on set (Laravel "hashed" cast)
            'password' => 'hashed',

            // Flags
            'is_expert' => 'boolean',
            'is_top_commentator' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    /**
     * Model event hooks.
     *
     * On force delete: also delete avatar file from storage (public disk)
     */
    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->is_expert && $user->is_top_commentator) {
                throw ValidationException::withMessages([
                    'is_expert' => 'მომხმარებელი ერთდროულად ვერ იქნება ექსპერტიც და ტოპ კომენტატორიც.',
                    'is_top_commentator' => 'მომხმარებელი ერთდროულად ვერ იქნება ექსპერტიც და ტოპ კომენტატორიც.',
                ]);
            }
        });

        static::forceDeleted(function (User $user) {
            // Nothing to cleanup
            if (!$user->image) {
                return;
            }

            // Normalize stored path
            $path = ltrim($user->image, '/');

            // If only filename stored, prepend default avatar directory
            if (!str_contains($path, '/')) {
                $path = trim(self::AVATAR_DIR, '/') . '/' . $path;
            }

            // Delete avatar file (ignore if missing)
            Storage::disk('public')->delete($path);
        });

        static::deleted(function (User $user): void {
            // This hook should only enforce logout semantics for soft-deletes.
            if ($user->isForceDeleting()) {
                return;
            }

            static::invalidateSessionsOnSoftDelete($user);
        });

        static::restored(function (User $restoredUser): void {
            $admins = static::query()
                ->where('role', 'admin')
                ->whereNull('deleted_at')
                ->get(['id', 'name', 'surname', 'nickname', 'email', 'role']);

            if ($admins->isEmpty()) {
                return;
            }

            Notification::sendNow(
                $admins,
                FilamentNotification::make()
                ->title('მომხმარებელი აღდგენილია')
                ->body("ანგარიში #{$restoredUser->id} ({$restoredUser->email}) აღდგა.")
                ->warning()
                ->toDatabase()
            );
        });
    }

    protected static function invalidateSessionsOnSoftDelete(User $user): void
    {
        $userId = (int) $user->getKey();
        if ($userId <= 0) {
            return;
        }

        // Invalidate persistent remember-me cookies for this account.
        // We update directly to avoid triggering additional model events.
        static::query()
            ->withTrashed()
            ->whereKey($userId)
            ->update([
                'remember_token' => Str::random(60),
            ]);

        // Remove all active sessions for this user when database sessions are used.
        if (config('session.driver') === 'database') {
            $table = (string) config('session.table', 'sessions');

            if (Schema::hasTable($table)) {
                DB::table($table)
                    ->where('user_id', $userId)
                    ->delete();
            }
        }

        // If the deleted user is the current request actor, also invalidate current session.
        if ((int) Auth::id() === $userId) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();
        }
    }

    /**
     * Check if the user is considered "verified" based on app settings.
     *
     */
    public function isVerified()
    {
        $emailEnabled = Settings::value('is_email_verification_enabled') ?? false;
        $phoneEnabled = Settings::value('is_phone_verification_enabled') ?? false;

        // Require both email and phone verification
        if ($emailEnabled && $phoneEnabled) {
            return $this->hasVerifiedEmail() && !is_null($this->phone_verified_at);
        }

        // Require only email verification
        if ($emailEnabled) {
            return $this->hasVerifiedEmail();
        }

        // Require only phone verification
        if ($phoneEnabled) {
            return !is_null($this->phone_verified_at);
        }

        // No verification required by settings
        return true;
    }

    /**
     * Should the app ask this user to verify something (email/phone)?
     *
     * Returns true if either email verification OR phone verification is enabled.
     */
    public function shouldVerify()
    {
        // If any verification mechanism is enabled, verification should be required
        if (Settings::value('is_email_verification_enabled') || Settings::value('is_phone_verification_enabled')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gate Filament admin access.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Only allow admins to access the admin panel
        return (bool) ($this->role === 'admin' ?? false);
    }


    //

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->surname}";
    }

    /*
    |---------------------------------
    | Relationships
    |---------------------------------
    */

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function publicDocumentViews()
    {
        return $this->hasMany(PublicDocumentUserView::class);
    }
}
