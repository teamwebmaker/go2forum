<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BannerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('key')
                    ->label(BannerResource::labelFor('key')),
                TextEntry::make('title')
                    ->label(BannerResource::labelFor('title')),
                TextEntry::make('subtitle')
                    ->label(BannerResource::labelFor('subtitle'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                ImageEntry::make('resolved_image_url')
                    ->label(BannerResource::labelFor('image'))
                    ->placeholder('-')
                    ->height(140)
                    ->width(320)
                    ->extraAttributes(['class' => 'mx-auto max-w-full']),
                TextEntry::make('position')
                    ->label(BannerResource::labelFor('position')),
                TextEntry::make('overlay_class')
                    ->label(BannerResource::labelFor('overlay_class')),
                TextEntry::make('container_class')
                    ->label(BannerResource::labelFor('container_class')),
                IconEntry::make('visibility')
                    ->label(BannerResource::labelFor('visibility'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextEntry::make('created_at')
                    ->label(BannerResource::labelFor('created_at'))
                    ->since()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(BannerResource::labelFor('updated_at'))
                    ->since()
                    ->placeholder('-'),
            ]);
    }
}
