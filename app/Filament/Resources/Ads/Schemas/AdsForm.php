<?php

namespace App\Filament\Resources\Ads\Schemas;

use App\Filament\Resources\Ads\AdsResource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class AdsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(AdsResource::labelFor('name'))
                    ->required(),

                FileUpload::make('image')
                    ->label(AdsResource::labelFor('image'))
                    ->image()
                    ->disk('public')
                    ->directory('ads')
                    ->visibility('public')
                    ->imageEditor()
                    ->acceptedFileTypes([
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                    ])
                    ->maxSize(20) // 20 KB
                    ->helperText('დაშვებული ფორმატები: PNG, JPG, WEBP, მაქს ზომა 20KB.')
                    ->imagePreviewHeight('100')
                    ->downloadable(),
                TextInput::make('link')
                    ->label(AdsResource::labelFor('link'))
                    ->required(),
                Toggle::make('visibility')
                    ->label(AdsResource::labelFor('visibility'))
                    ->required(),
            ]);
    }
}
