<?php

namespace App\Filament\Resources\PublicDocuments\Schemas;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use App\Models\PublicDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
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
                    ->required(),
                TextInput::make('link')
                    ->label(PublicDocumentResource::labelFor('link'))
                    ->default(null),
                FileUpload::make('document')
                    ->label(PublicDocumentResource::labelFor('document'))
                    ->helperText('დაშვებულია მხოლოდ PDF (მაქს 15MB).')
                    ->disk('public')
                    ->directory(PublicDocumentResource::STORAGE_DIR)
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(15_000) // ~15MB
                    ->downloadable()
                    ->openable()
                    ->previewable(false)
                    ->panelLayout('integrated')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, callable $get): string {
                        $base = Str::slug(
                            $get('name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                        );

                        $ext = $file->getClientOriginalExtension() ?: 'pdf';

                        return "{$base}.{$ext}";
                    })
                    // Store only the filename (basename) in DB
                    ->dehydrateStateUsing(fn($state) => $state ? basename($state) : null)
                    // When editing, prepend directory so the file can be located
                    ->formatStateUsing(fn($state) => $state ? PublicDocumentResource::STORAGE_DIR . "/{$state}" : null)
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
                        ignorable: fn(?PublicDocument $record) => $record
                    ),
                Toggle::make('visibility')
                    ->label(PublicDocumentResource::labelFor('visibility'))
                    ->required(),
            ]);
    }
}
