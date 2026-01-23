<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{

    protected $fillable = [
        'name',
        'image',
        'link',
        'visibility',
    ];

    protected $casts = [
        'visibility' => 'boolean',
    ];

    /*
    |---------------------------------
    | Relationships
    |---------------------------------
    */

    public function categories()
    {
        return $this->hasMany(Category::class, 'ad_id');
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
}
