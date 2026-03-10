<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicDocumentUserView extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_document_id',
        'user_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(PublicDocument::class, 'public_document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
