<?php

namespace App\Filament\Resources\Topics\Tables;

use App\Filament\Resources\Topics\TopicResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TopicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label(TopicResource::labelFor('user_id')),
                TextColumn::make('category.name')
                    ->label(TopicResource::labelFor('category_id'))
                    ->badge(),
                TextColumn::make('title')
                    ->label(TopicResource::labelFor('title'))
                    ->limit(35)
                    ->searchable(),
                TextColumn::make('status')
                    ->label(TopicResource::labelFor('status'))
                    ->formatStateUsing(fn(string $state) => __('models.topics.statuses.' . $state))
                    ->badge()
                    ->color(fn($record) => $record->status_color),
                TextColumn::make('slug')
                    ->label(TopicResource::labelFor('slug'))
                    ->limit(35)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('messages_count')
                    ->label(TopicResource::labelFor('messages_count'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('pinned')
                    ->label(TopicResource::labelFor('pinned'))
                    ->boolean(),
                IconColumn::make('visibility')
                    ->label(TopicResource::labelFor('visibility'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(TopicResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(TopicResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(TopicResource::labelFor('category_id'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_category')
                    ->label(TopicResource::labelFor('category_id'))
                    ->trueLabel(__('models.topics.filters.with_category'))
                    ->falseLabel(__('models.topics.filters.without_category'))
                    ->queries(
                        true: fn($query) => $query->whereNotNull('category_id'),
                        false: fn($query) => $query->whereNull('category_id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(__('models.topics.actions.delete.headingBulk')),
                ]),
            ]);
    }
}
