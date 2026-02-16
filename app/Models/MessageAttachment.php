<?php

namespace App\Models;

use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'attachment_type',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected static function booted(): void
    {
        static::deleted(function (MessageAttachment $attachment): void {
            DB::afterCommit(function () use ($attachment): void {
                try {
                    FileUploadService::deleteUploadedFile(
                        $attachment->path,
                        null,
                        $attachment->disk ?: 'public'
                    );
                } catch (\Throwable $exception) {
                    report($exception);
                }
            });
        });
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
