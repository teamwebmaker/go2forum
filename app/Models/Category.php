<?php

namespace App\Models;

use App\Models\Ads;
use App\Support\SlugGenerator;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = [
        'name',
        'visibility',
    ];

    protected $casts = [
        'visibility' => 'boolean',
    ];

    /**
     * Model events.
     */
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            $source = $category->name;

            if (!$source) {
                return;
            }

            // Generate slug only on create OR when name changes
            if (!$category->exists || $category->isDirty(['name'])) {
                $category->slug = static::uniqueSlug($source);
            }
        });
    }

    /*
    |---------------------------------
    | Relationships
    |---------------------------------
    */

    public function ads()
    {
        return $this->belongsTo(Ads::class, 'ad_id');
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
    | Helpers
    |---------------------------------
    */

    /**
     * Generate a unique slug for this model.
     */
    public static function uniqueSlug(string $value, string $column = 'slug'): string
    {
        return SlugGenerator::unique(static::class, $value, $column);
    }
}
