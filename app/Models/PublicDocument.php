<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class PublicDocument extends Model
{
    public const STORAGE_DIR = 'documents/public_documents';
    public const STORAGE_DISK = 'local';

    protected $fillable = [
        'name',
        'document',
        'visibility',
        'order',
        'link',
        'requires_auth_to_view',
        'views_count',
    ];

    protected $casts = [
        'visibility' => 'boolean',
        'requires_auth_to_view' => 'boolean',
        'views_count' => 'integer',
    ];

    public function scopeVisible($query)
    {
        return $query->where('visibility', true);
    }

    public function userViews(): HasMany
    {
        return $this->hasMany(PublicDocumentUserView::class);
    }

    public function userViewFor(int $userId): HasOne
    {
        return $this->hasOne(PublicDocumentUserView::class)->where('user_id', $userId);
    }

    public function storagePath(): ?string
    {
        if (!filled($this->document)) {
            return null;
        }

        return self::STORAGE_DIR . '/' . ltrim((string) $this->document, '/');
    }

    /**
     * Resolve file location with backward compatibility.
     *
     * Files may exist on the old public disk (legacy) or the new private disk.
     * We prefer private storage when both exist.
     *
     * @return array{disk: string, path: string}|null
     */
    public function resolveStorageLocation(): ?array
    {
        $path = $this->storagePath();
        if (!$path) {
            return null;
        }

        foreach ([self::STORAGE_DISK, 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return [
                    'disk' => $disk,
                    'path' => $path,
                ];
            }
        }

        return null;
    }

    public function canBeViewedBy(?User $user): bool
    {
        if (!$this->visibility && !($user?->role === 'admin')) {
            return false;
        }

        if (!$this->requires_auth_to_view) {
            return true;
        }

        return (bool) $user;
    }

    public function canBeDownloadedBy(?User $user): bool
    {
        if (!$this->visibility && !($user?->role === 'admin')) {
            return false;
        }

        return !$this->requires_auth_to_view;
    }
}
