<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
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
        $slug = $baseSlug;
        $counter = 2;

        $query = is_string($model) ? $model::query() : $model->newQuery();

        // Ignore the current record when updating
        if ($model instanceof Model && $model->getKey()) {
            $query->whereKeyNot($model->getKey());
        }

        while ($query->where($column, $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
