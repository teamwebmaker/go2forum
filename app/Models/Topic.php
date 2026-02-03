<?php

namespace App\Models;

use App\Support\SlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'status',
        'slug',
        'messages_count',
        'pinned',
        'visibility',
    ];

    protected $casts = [
        'pinned' => 'boolean',
        'visibility' => 'boolean',
        'messages_count' => 'integer',
    ];

    /*
    |---------------------------------
    | Model events
    |---------------------------------
    */

    protected static function booted(): void
    {
        // Generate slug
        static::saving(function (Topic $topic) {
            if (!$topic->title) {
                return;
            }

            if (!$topic->exists || $topic->isDirty('title')) {
                $topic->slug = SlugGenerator::unique(static::class, $topic->title, 'slug');
            }
        });

        static::created(function (Topic $topic) {
            static::syncCategoryCount($topic->category_id);
        });

        static::updated(function (Topic $topic) {
            if (!$topic->wasChanged('category_id')) {
                return;
            }

            static::syncCategoryCount($topic->getOriginal('category_id'));
            static::syncCategoryCount($topic->category_id);
        });

        static::deleted(function (Topic $topic) {
            static::syncCategoryCount($topic->category_id);
        });
    }

    protected static function syncCategoryCount(?int $categoryId): void
    {
        if (!$categoryId) {
            return;
        }

        Category::whereKey($categoryId)->update([
            'topics_count' => static::where('category_id', $categoryId)->count(),
        ]);
    }

    /*
    |---------------------------------
    | Scopes
    |---------------------------------
    */

    public function scopeVisible($query)
    {
        return $query->where('visibility', true);
    }

    //
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'closed' => 'gray',
            'disabled' => 'danger',
            default => 'secondary',
        };
    }


    /*
    |---------------------------------
    | Relationships
    |---------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
