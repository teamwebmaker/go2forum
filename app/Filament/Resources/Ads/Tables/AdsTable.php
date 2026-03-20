<?php

namespace App\Filament\Resources\Ads\Tables;

use App\Filament\Resources\Ads\AdsResource;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->getStateUsing(fn($record) => $record->categories->pluck('name')->unique()->implode(', '))
                    ->limit(40)
                    ->tooltip(fn($record) => $record->categories->pluck('name')->unique()->implode(', '))
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchTerm = trim($search);

                        return $query->whereHas('categories', function (Builder $categoriesQuery) use ($searchTerm): void {
                            $categoriesQuery->where('name', 'like', "%{$searchTerm}%");
                        });
                    }),
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
                SelectFilter::make('category_id')
                    ->label(__('models.categories.singular'))
                    ->options(fn(): array => Category::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        $categoryId = $data['value'] ?? null;

                        if (blank($categoryId)) {
                            return $query;
                        }

                        return $query->whereHas('categories', fn(Builder $categoriesQuery) => $categoriesQuery->whereKey($categoryId));
                    }),
                TernaryFilter::make('visibility')
                    ->label(AdsResource::labelFor('visibility'))
                    ->queries(
                        true: fn(Builder $query) => $query->where('visibility', true),
                        false: fn(Builder $query) => $query->where('visibility', false),
                    ),
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
