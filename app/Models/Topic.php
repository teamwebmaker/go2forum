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
        static::saving(function (Topic $topic) {
            if (!$topic->title) {
                return;
            }

            if (!$topic->exists || $topic->isDirty('title')) {
                $topic->slug = SlugGenerator::unique(static::class, $topic->title, 'slug');
            }
        });
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
