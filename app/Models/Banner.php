<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Banner extends Model
{
    public const HOME_KEY = 'home';

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'image',
        'position',
        'overlay_class',
        'container_class',
        'visibility',
    ];

    protected $casts = [
        'visibility' => 'boolean',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visibility', true);
    }

    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        if (!filled($this->image)) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://', '//', 'data:', '/'])) {
            return $this->image;
        }

        if (Str::startsWith($this->image, ['images/', 'storage/'])) {
            return asset($this->image);
        }

        if (!Storage::disk('public')->exists($this->image)) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    protected static function shouldDeleteFromPublicDisk(?string $path): bool
    {
        if (!filled($path)) {
            return false;
        }

        return !Str::startsWith($path, ['http://', 'https://', '//', 'data:', '/', 'images/', 'storage/']);
    }

    protected static function booted(): void
    {
        static::updating(function (Banner $banner): void {
            if (!$banner->isDirty('image')) {
                return;
            }

            $originalImage = $banner->getOriginal('image');

            if (!static::shouldDeleteFromPublicDisk($originalImage)) {
                return;
            }

            Storage::disk('public')->delete($originalImage);
        });

        // Delete only uploaded files from the public disk.
        static::deleted(function (Banner $banner): void {
            if (!static::shouldDeleteFromPublicDisk($banner->image)) {
                return;
            }

            Storage::disk('public')->delete($banner->image);
        });
    }
}
