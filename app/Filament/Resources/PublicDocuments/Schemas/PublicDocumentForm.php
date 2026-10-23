<?php

namespace App\Filament\Resources\PublicDocuments\Schemas;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use App\Models\PublicDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PublicDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(PublicDocumentResource::labelFor('name'))
                    ->required()
                    ->unique(
                        table: PublicDocument::class,
                        column: 'name',
                        ignorable: fn (?PublicDocument $record) => $record
                    ),
                TextInput::make('link')
                    ->label(PublicDocumentResource::labelFor('link'))
                    ->unique(
                        table: PublicDocument::class,
                        column: 'link',
                        ignorable: fn (?PublicDocument $record) => $record
                    )
                    ->default(null),
                FileUpload::make('document')
                    ->label(PublicDocumentResource::labelFor('document'))
                    ->helperText('დაშვებულია მხოლოდ PDF (მაქს 15MB).')
                    ->disk(PublicDocument::STORAGE_DISK)
                    ->directory(PublicDocumentResource::STORAGE_DIR)
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(15_000) // ~15MB
                    ->downloadable()
                    ->openable()
                    ->previewable(false)
                    ->panelLayout('integrated')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, callable $get): string {
                        return self::uniqueUploadedFilename($file, $get('name'));
                    })
                    // Store only the filename (basename) in DB
                    ->dehydrateStateUsing(fn ($state) => $state ? basename($state) : null)
                    // When editing, prepend directory so the file can be located
                    ->formatStateUsing(fn ($state) => $state ? PublicDocumentResource::STORAGE_DIR."/{$state}" : null)
                    ->nullable(),
                TextInput::make('order')
                    ->label(PublicDocumentResource::labelFor('order'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->unique(
                        table: PublicDocument::class,
                        column: 'order',
                        ignorable: fn (?PublicDocument $record) => $record
                    ),
                Toggle::make('visibility')
                    ->label(PublicDocumentResource::labelFor('visibility'))
                    ->required(),
                Toggle::make('requires_auth_to_view')
                    ->label(PublicDocumentResource::labelFor('requires_auth_to_view'))
                    ->default(false)
                    ->required(),
            ]);
    }

    private static function uniqueUploadedFilename(TemporaryUploadedFile $file, mixed $documentName): string
    {
        $base = Str::slug(
            filled($documentName)
                ? (string) $documentName
                : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        );
        $base = $base !== '' ? $base : 'document';
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $filename = "{$base}.{$extension}";
        $suffix = 2;

        while (Storage::disk(PublicDocument::STORAGE_DISK)->exists(
            PublicDocumentResource::STORAGE_DIR."/{$filename}"
        )) {
            $filename = "{$base}-{$suffix}.{$extension}";
            $suffix++;
        }

        return $filename;
    }
}
