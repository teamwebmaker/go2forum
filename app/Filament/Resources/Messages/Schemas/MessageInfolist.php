<?php

namespace App\Filament\Resources\Messages\Schemas;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\MessageAttachment;
use App\Models\Message;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class MessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label(MessageResource::labelFor('id'))
                    ->numeric(),
                TextEntry::make('conversation.id')
                    ->label(MessageResource::labelFor('conversation_id'))
                    ->numeric(),
                TextEntry::make('sender.full_name')
                    ->label(MessageResource::labelFor('sender_id'))
                    ->placeholder('-'),
                TextEntry::make('content')
                    ->label(MessageResource::labelFor('content'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('attachments_count')
                    ->label(MessageResource::labelFor('attachments_count'))
                    ->numeric()
                    ->placeholder('-'),

                
                ImageEntry::make('attachment_images')
                    ->label(MessageResource::labelFor('attachment_images'))
                    ->state(fn(Message $record): array => self::attachmentImageUrls($record))
                    ->checkFileExistence(false)
                    ->imageSize(72)
                    ->square()
                    ->stacked()
                    ->limit(6)
                    ->limitedRemainingText()
                    ->placeholder('-'),
                TextEntry::make('attachment_links')
                    ->label(MessageResource::labelFor('attachment_links'))
                    ->state(fn(Message $record): array => self::attachmentLinks($record))
                    ->html()
                    ->listWithLineBreaks()
                    ->limitList(8)
                    ->expandableLimitedList()
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),
                TextEntry::make('likes_count')
                    ->label(MessageResource::labelFor('likes_count'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(MessageResource::labelFor('created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(MessageResource::labelFor('updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->label(MessageResource::labelFor('deleted_at'))
                    ->dateTime()
                    ->visible(fn(Message $record): bool => $record->trashed()),
            ]);
    }

    protected static function attachmentImageUrls(Message $record): array
    {
        return $record->attachments
            ->filter(fn(MessageAttachment $attachment): bool => self::isImageAttachment($attachment))
            ->map(fn(MessageAttachment $attachment): ?string => self::attachmentUrl($attachment))
            ->filter()
            ->values()
            ->all();
    }

    protected static function attachmentLinks(Message $record): array
    {
        return $record->attachments
            ->map(function (MessageAttachment $attachment): string {
                $name = e((string) ($attachment->original_name ?: basename((string) $attachment->path)));
                $url = self::attachmentUrl($attachment);

                if (blank($url)) {
                    return $name;
                }

                return '<a href="' . e($url) . '" target="_blank" rel="noopener">' . $name . '</a>';
            })
            ->values()
            ->all();
    }

    protected static function isImageAttachment(MessageAttachment $attachment): bool
    {
        $type = (string) ($attachment->attachment_type ?? '');
        $mimeType = (string) ($attachment->mime_type ?? '');

        return $type === 'image' || str_starts_with($mimeType, 'image/');
    }

    protected static function attachmentUrl(MessageAttachment $attachment): ?string
    {
        $path = (string) ($attachment->path ?? '');

        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
            return $path;
        }

        try {
            return Storage::disk((string) ($attachment->disk ?: config('chat.attachments_disk', 'public')))
                ->url($path);
        } catch (\Throwable) {
            return null;
        }
    }
}
