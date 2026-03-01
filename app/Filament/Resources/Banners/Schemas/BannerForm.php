<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Filament\Resources\Banners\BannerResource;
use App\Models\Banner;
use App\Services\ImageUploadService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(BannerResource::labelFor('key'))
                    ->required()
                    ->unique(
                        table: Banner::class,
                        column: 'key',
                        ignorable: fn(?Banner $record) => $record
                    ),
                TextInput::make('title')
                    ->label(BannerResource::labelFor('title'))
                    ->required(),
                Textarea::make('subtitle')
                    ->label(BannerResource::labelFor('subtitle'))
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label(BannerResource::labelFor('image'))
                    ->image()
                    ->disk('public')
                    ->directory(BannerResource::STORAGE_DIR)
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                    ])
                    ->maxSize(1024) // 1MB
                    ->imageEditor()
                    ->imagePreviewHeight('100')
                    ->helperText('დაშვებული ფორმატები: PNG, JPG, WEBP, მაქს ზომა 1MB.')
                    ->downloadable()
                    ->saveUploadedFileUsing(function ($file, callable $get, $record) {
                        $old = $record?->image ?? $get('image');

                        return ImageUploadService::handleOptimizedImageUpload(
                            file: $file,
                            destinationPath: BannerResource::STORAGE_DIR,
                            oldFile: $old,
                            webpQuality: 80,
                            optimize: true,
                            disk: 'public',
                        );
                    }),
                Grid::make(2)
                    ->schema([
                        TextInput::make('position_x')
                            ->label(BannerResource::labelFor('position') . ' X')
                            ->type('range')
                            ->live()
                            ->default(
                                fn(?Banner $record, Get $get): int => self::extractPositionPart(
                                    $record?->position ?? $get->string('position', true),
                                    0,
                                    50,
                                )
                            )
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->hint(fn(Get $get): string => (($get->integer('position_x', true) ?? 50) . '%'))
                            ->dehydrated(false),
                        TextInput::make('position_y')
                            ->label(BannerResource::labelFor('position') . ' Y')
                            ->type('range')
                            ->live()
                            ->default(
                                fn(?Banner $record, Get $get): int => self::extractPositionPart(
                                    $record?->position ?? $get->string('position', true),
                                    1,
                                    40,
                                )
                            )
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->hint(fn(Get $get): string => (($get->integer('position_y', true) ?? 40) . '%'))
                            ->dehydrated(false),
                    ]),
                Hidden::make('position')
                    ->default('50% 40%')
                    ->dehydrateStateUsing(
                        fn($state, Get $get): string => self::formatPosition(
                            $get->integer('position_x', true) ?? 50,
                            $get->integer('position_y', true) ?? 40,
                        )
                    ),
                TextInput::make('overlay_class')
                    ->label(BannerResource::labelFor('overlay_class'))
                    ->required()
                    ->default('bg-cyan-950/70'),
                TextInput::make('container_class')
                    ->label(BannerResource::labelFor('container_class'))
                    ->required()
                    ->default('mb-2'),
                Toggle::make('visibility')
                    ->label(BannerResource::labelFor('visibility'))
                    ->default(true)
                    ->required(),
            ]);
    }

    private static function extractPositionPart(?string $position, int $index, int $fallback): int
    {
        if (!filled($position)) {
            return $fallback;
        }

        $parts = preg_split('/\s+/', trim($position)) ?: [];
        $rawPart = $parts[$index] ?? null;

        if (!filled($rawPart)) {
            return $fallback;
        }

        $value = (int) round((float) str_replace('%', '', (string) $rawPart));

        return max(0, min(100, $value));
    }

    private static function formatPosition(int $x, int $y): string
    {
        $x = max(0, min(100, $x));
        $y = max(0, min(100, $y));

        return "{$x}% {$y}%";
    }
}
