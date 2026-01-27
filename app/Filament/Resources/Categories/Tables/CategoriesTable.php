<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(CategoryResource::labelFor('name'))
                    ->searchable(),
                ImageColumn::make('ad.image')
                    ->label(CategoryResource::labelFor('ad_id'))
                    ->disk('public'),
                TextColumn::make('topics_count')
                    ->label(CategoryResource::labelFor('topics_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order')
                    ->label(CategoryResource::labelFor('order'))
                    ->sortable(),
                IconColumn::make('visibility')
                    ->label(CategoryResource::labelFor('visibility'))
                    ->boolean(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(CategoryResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(CategoryResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(__('models.categories.actions.delete.headingBulk')),
                ]),
            ]);
    }
}
