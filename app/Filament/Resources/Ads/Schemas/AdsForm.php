<?php

namespace App\Filament\Resources\Ads\Schemas;

use App\Filament\Resources\Ads\AdsResource;
use App\Services\ImageUploadService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(AdsResource::labelFor('name'))
                    ->required(),
                TextInput::make('link')
                    ->label(AdsResource::labelFor('link'))
                    ->required(),
                FileUpload::make('image')
                    ->label(AdsResource::labelFor('image'))
                    ->image()
                    ->disk('public')
                    ->directory('ads')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                    ])
                    ->maxSize(50) // KB
                    ->imageEditor()
                    ->imagePreviewHeight('100')
                    ->helperText('დაშვებული ფორმატები: PNG, JPG, WEBP, მაქს ზომა 50KB.')
                    ->downloadable()
                    ->saveUploadedFileUsing(function ($file, callable $get, $record) {
                        $old = $record?->image ?? $get('image');

                        return ImageUploadService::handleOptimizedImageUpload(
                            file: $file,
                            destinationPath: 'ads',
                            oldFile: $old,
                            webpQuality: 80,
                            optimize: true,
                            disk: 'public',
                        );
                    }),
                Toggle::make('visibility')
                    ->label(AdsResource::labelFor('visibility'))
                    ->required(),
            ]);
    }
}
