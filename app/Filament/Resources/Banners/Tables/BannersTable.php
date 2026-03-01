<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(BannerResource::labelFor('key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(BannerResource::labelFor('title'))
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('resolved_image_url')
                    ->label(BannerResource::labelFor('image')),
                TextColumn::make('position')
                    ->label(BannerResource::labelFor('position'))
                    ->searchable(),
                TextColumn::make('overlay_class')
                    ->label(BannerResource::labelFor('overlay_class'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('container_class')
                    ->label(BannerResource::labelFor('container_class'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('visibility')
                    ->label(BannerResource::labelFor('visibility'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(BannerResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(BannerResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(__('models.banners.actions.delete.headingBulk')),
                ]),
            ]);
    }
}
