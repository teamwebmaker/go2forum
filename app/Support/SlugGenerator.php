<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * Create a unique slug for any Eloquent model/column.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string  $model   Model instance or class name
     * @param  string                                      $value   Source text (name, title, etc.)
     * @param  string                                      $column  Column to store the slug (default: slug)
     */
    public static function unique(Model|string $model, string $value, string $column = 'slug'): string
    {
        $baseSlug = Str::slug($value);
        if ($baseSlug === '') {
            $baseSlug = 'item';
        }

        $slug = $baseSlug;
        $counter = 2;

        $instance = is_string($model) ? new $model() : $model;
        $query = $instance->newQuery();

        // Include soft deleted records when the model uses SoftDeletes,
        // because unique DB indexes still include trashed rows.
        if (in_array(SoftDeletes::class, class_uses_recursive($instance), true)) {
            $query->withTrashed();
        }

        // Ignore the current record when updating
        if ($instance->getKey()) {
            $query->whereKeyNot($instance->getKey());
        }

        while ((clone $query)->where($column, $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
