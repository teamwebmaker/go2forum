<?php

namespace App\Filament\Resources\Ads\Schemas;

use App\Filament\Resources\Ads\AdsResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(AdsResource::labelFor('name')),
                ImageEntry::make('image')
                    ->label(AdsResource::labelFor('image'))
                    ->disk('public')
                    ->height(140)
                    ->width(320)
                    ->extraAttributes(['class' => 'mx-auto max-w-full']),
                TextEntry::make('link')
                    ->label(AdsResource::labelFor('link'))
                    ->url(fn($record) => $record->link, shouldOpenInNewTab: true)
                    ->copyable()
                    ->extraAttributes(['class' => 'underline text-blue-600']),
                TextEntry::make('categories')
                    ->label(__('models.categories.plural'))
                    ->state(fn($record) => $record->categories()->pluck('name')->unique()->implode(', '))
                    ->wrap(),
                IconEntry::make('visibility')
                    ->label(AdsResource::labelFor('visibility'))
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(AdsResource::labelFor('created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(AdsResource::labelFor('updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
