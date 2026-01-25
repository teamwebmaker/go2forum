<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicDocument extends Model
{
    protected $fillable = [
        'name',
        'document',
        'link',
    ];
}
