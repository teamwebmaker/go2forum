<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteAlert extends Model
{
    public const ADMIN_TIMEZONE = 'Asia/Tbilisi';

    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_INFO = 'info';

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_AUTH = 'auth';
    public const AUDIENCE_GUEST = 'guest';

    protected $fillable = [
        'title',
        'content',
        'type',
        'is_closable',
        'audience',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_closable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeVisibleFor(Builder $query, ?Authenticatable $viewer): Builder
    {
        $audiences = $viewer
            ? [self::AUDIENCE_ALL, self::AUDIENCE_AUTH]
            : [self::AUDIENCE_ALL, self::AUDIENCE_GUEST];

        return $query
            ->where('is_active', true)
            ->whereIn('audience', $audiences)
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public function getDismissStorageKeyAttribute(): string
    {
        $updatedAtTimestamp = $this->updated_at?->timestamp ?? 0;

        return "site-alert-{$this->id}-{$updatedAtTimestamp}";
    }

    /**
     * @return array<int, string>
     */
    public static function availableTypes(): array
    {
        return [
            self::TYPE_SUCCESS,
            self::TYPE_ERROR,
            self::TYPE_WARNING,
            self::TYPE_INFO,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function availableAudiences(): array
    {
        return [
            self::AUDIENCE_ALL,
            self::AUDIENCE_AUTH,
            self::AUDIENCE_GUEST,
        ];
    }
}
