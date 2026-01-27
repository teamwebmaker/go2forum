<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicDocument extends Model
{
    protected $fillable = [
        'name',
        'document',
        'visibility',
        'order',
        'link',
    ];

    protected $casts = [
        'visibility' => 'boolean',
    ];

    public function scopeVisible($query)
    {
        return $query->where('visibility', true);
    }
}
