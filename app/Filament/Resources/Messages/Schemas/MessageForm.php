<?php

namespace App\Filament\Resources\Messages\Schemas;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\MessageAttachment;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->label(MessageResource::labelFor('content'))
                    ->rows(6)
                    ->default(null)
                    ->columnSpanFull(),
                Section::make(MessageResource::labelFor('attachments'))
                    ->collapsible()
                    ->schema([
                        Repeater::make('attachments')
                            ->label(MessageResource::labelFor('attachments'))
                            ->relationship('attachments')
                            ->defaultItems(0)
                            ->collapsible()
                            ->collapsed(fn(Repeater $component): bool => $component->getItemsCount() > 1)
                            ->reorderable(false)
                            ->addable(false)
                            ->deleteAction(function (Action $action): Action {
                                return $action
                                    ->requiresConfirmation()
                                    ->modalHeading(__('models.messages.actions.delete_attachment.heading'))
                                    ->modalDescription(__('models.messages.actions.delete_attachment.description'))
                                    ->modalSubmitActionLabel(__('models.messages.actions.delete_attachment.submit'))
                                    ->action(function (array $arguments, Repeater $component): void {
                                        $itemKey = (string) ($arguments['item'] ?? '');
                                        $existingRecords = $component->getCachedExistingRecords();
                                        $existingRecord = $existingRecords->get($itemKey);
                                        $rawItems = $component->getRawState();

                                        if ($existingRecord instanceof MessageAttachment) {
                                            $existingRecord->delete();
                                            $existingRecords->forget($itemKey);
                                            $existingRecords->forget('record-' . $existingRecord->getKey());
                                        } else {
                                            $recordId = data_get($rawItems, $itemKey . '.id');

                                            if (filled($recordId)) {
                                                $attachment = null;
                                                $relationship = $component->getRelationship();

                                                if ($relationship) {
                                                    $attachment = $relationship->whereKey($recordId)->first();
                                                }

                                                if ($attachment instanceof MessageAttachment) {
                                                    $attachment->delete();
                                                }

                                                $existingRecords->forget('record-' . $recordId);
                                            }
                                        }

                                        unset($rawItems[$itemKey]);

                                        $component->rawState($rawItems);
                                        $component->callAfterStateUpdated();

                                        $component->shouldPartiallyRenderAfterActionsCalled() ? $component->partiallyRender() : null;
                                    });
                            })
                            ->extraAttributes([
                                'class' => 'message-attachments-repeater',
                            ])
                            ->itemLabel(function (Repeater $component, string $uuid, array $state): ?string {
                                $record = $component->getCachedExistingRecords()->get($uuid);

                                if ($record instanceof MessageAttachment && filled($record->original_name)) {
                                    return (string) $record->original_name;
                                }

                                $stateName = data_get($state, 'original_name');

                                return filled($stateName) ? (string) $stateName : null;
                            })
                            ->schema([
                                TextEntry::make('attachment_file')
                                    ->label(MessageResource::labelFor('file'))
                                    ->state(fn(?MessageAttachment $record): HtmlString|string => self::attachmentPreview($record))
                                    ->html()
                                    ->columnSpanFull(),
                                TextInput::make('original_name')
                                    ->label(MessageResource::labelFor('original_name'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->maxLength(255),
                                TextInput::make('attachment_type')
                                    ->label(MessageResource::labelFor('attachment_type'))
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'image' => __('models.messages.types.image'),
                                        'document' => __('models.messages.types.document'),
                                        default => $state,
                                    })
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('mime_type')
                                    ->label(MessageResource::labelFor('mime_type'))
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('size_bytes')
                                    ->label(MessageResource::labelFor('size_bytes'))
                                    ->formatStateUsing(fn($state): ?string => self::formatMegabytes($state))
                                    ->suffix('MB')
                                    ->placeholder('-')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('disk')
                                    ->label(MessageResource::labelFor('disk'))
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('path')
                                    ->label(MessageResource::labelFor('path'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(string $operation): bool => $operation === 'edit'),
            ]);
    }

    protected static function attachmentPreview(?MessageAttachment $attachment): HtmlString|string
    {
        if (!$attachment) {
            return '-';
        }

        $name = e((string) ($attachment->original_name ?: basename((string) $attachment->path)));
        $url = self::attachmentUrl($attachment);

        if (blank($url)) {
            return $name;
        }

        $link = '<a href="' . e($url) . '" target="_blank" rel="noopener">' . $name . '</a>';

        if (self::isImageAttachment($attachment)) {
            return new HtmlString(
                '<div style="display:flex;gap:12px;align-items:center;">'
                . '<img src="' . e($url) . '" alt="' . $name . '" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">'
                . '<span>' . $link . '</span>'
                . '</div>'
            );
        }

        return new HtmlString($link);
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
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk((string) ($attachment->disk ?: config('chat.attachments_disk', 'public')));

            return $disk->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function formatMegabytes(mixed $bytes): ?string
    {
        if (!is_numeric($bytes)) {
            return null;
        }

        return number_format(((float) $bytes) / (1024 * 1024), 2, '.', '');
    }
}
