<?php

namespace App\Filament\Resources\PublicDocuments\Schemas;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class PublicDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextEntry::make('name')
                ->label(PublicDocumentResource::labelFor('name'))
                ->weight('semibold'),
            TextEntry::make('document')
                ->label(PublicDocumentResource::labelFor('document'))
                ->placeholder('-')
                ->icon('heroicon-o-document-text')
                ->badge()
                ->color('gray')
                ->url(fn($record) => filled($record->document)
                    ? Storage::disk('public')->url(
                        PublicDocumentResource::STORAGE_DIR . '/' . $record->document
                    )
                    : null)
                ->openUrlInNewTab()
                ->copyable()
                ->copyMessage('კოპირებულია'),
            TextEntry::make('link')
                ->label(PublicDocumentResource::labelFor('link'))
                ->placeholder('-')
                ->icon('heroicon-o-link')
                ->url(fn($record) => filled($record->link) ? $record->link : null)
                ->openUrlInNewTab()
                ->copyable()
                ->copyMessage('კოპირებულია'),
            TextEntry::make('order')
                ->label(PublicDocumentResource::labelFor('order'))
                ->badge()
                ->numeric(),
            IconEntry::make('visibility')
                ->label(PublicDocumentResource::labelFor('visibility'))
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger'),

            TextEntry::make('created_at')
                ->label(PublicDocumentResource::labelFor('created_at'))
                ->since() // looks nicer than raw datetime
                ->placeholder('-'),

            TextEntry::make('updated_at')
                ->label(PublicDocumentResource::labelFor('updated_at'))
                ->since()
                ->placeholder('-'),

        ]);
    }
}
