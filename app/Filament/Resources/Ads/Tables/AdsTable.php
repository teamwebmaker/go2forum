<?php

namespace App\Filament\Resources\Ads\Tables;

use App\Filament\Resources\Ads\AdsResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(AdsResource::labelFor('name'))
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(AdsResource::labelFor('image'))
                    ->disk('public'),
                TextColumn::make('link')
                    ->label(AdsResource::labelFor('link'))
                    ->limit(30)
                    ->tooltip(fn($record) => $record->link)
                    ->searchable(),
                TextColumn::make('categories')
                    ->label(__('models.categories.plural'))
                    ->getStateUsing(fn($record) => $record->categories()->pluck('name')->unique()->implode(', '))
                    ->limit(40)
                    ->tooltip(fn($record) => $record->categories()->pluck('name')->unique()->implode(', '))
                    ->wrap()
                    ->searchable(),
                IconColumn::make('visibility')
                    ->label(AdsResource::labelFor('visibility'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(AdsResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(AdsResource::labelFor('updated_at'))
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
                        ->modalHeading(__('models.ads.actions.delete.headingBulk')),
                ]),
            ]);
    }
}
