<?php

namespace App\Filament\Resources\Topics\Tables;

use App\Filament\Resources\Topics\TopicResource;
use App\Models\Conversation;
use App\Models\Topic;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class TopicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordClasses(fn(Topic $record): ?string => $record->trashed() ? 'resource-row-trashed' : null)
            ->columns([
                TextColumn::make('user.full_name')
                    ->label(TopicResource::labelFor('user_id'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchTerm = trim($search);

                        return $query->whereHas('user', function (Builder $userQuery) use ($searchTerm): void {
                            $userQuery
                                ->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('surname', 'like', "%{$searchTerm}%")
                                ->orWhere('nickname', 'like', "%{$searchTerm}%");
                        });
                    }),
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
                TextColumn::make('messages_count')
                    ->label(TopicResource::labelFor('messages_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conversation.last_message_at')
                    ->label(TopicResource::labelFor('last_message_at'))
                    ->dateTime()
                    ->sortable(query: fn($query, string $direction) => $query->orderBy(
                        Conversation::query()
                            ->select('last_message_at')
                            ->whereColumn('topic_id', 'topics.id')
                            ->limit(1),
                        $direction
                    ))
                    ->placeholder('-'),
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
                TextColumn::make('deleted_at')
                    ->label(TopicResource::labelFor('deleted_at'))
                    ->dateTime()
                    ->badge()
                    ->color(fn($state): string => filled($state) ? 'warning' : 'gray')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(TopicResource::labelFor('status'))
                    ->options([
                        'active' => __('models.topics.statuses.active'),
                        'closed' => __('models.topics.statuses.closed'),
                        'disabled' => __('models.topics.statuses.disabled'),
                    ])
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
                SelectFilter::make('category')
                    ->label(TopicResource::labelFor('category_id'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make()
                    ->label(__('models.messages.filters.in_trash'))
                    ->placeholder(__('models.messages.filters.not_in_trash_only'))
                    ->trueLabel(__('models.messages.filters.all'))
                    ->falseLabel(__('models.messages.filters.in_trash_only')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn(Topic $record): bool => $record->trashed()),
                DeleteAction::make('trashTopic')
                    ->label(__('models.topics.actions.delete_only.label'))
                    ->modalHeading(__('models.topics.actions.delete_only.heading'))
                    ->modalDescription(__('models.topics.actions.delete_only.description'))
                    ->hidden(fn(Topic $record): bool => $record->trashed()),
                RestoreAction::make()
                    ->visible(fn(Topic $record): bool => $record->trashed()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('models.topics.actions.delete_only.label'))
                        ->chunkSelectedRecords(100)
                        ->using(function (DeleteBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                            $isFirstException = true;

                            $records->each(function (Topic $record) use ($action, &$isFirstException): void {
                                try {
                                    if (!$record->trashed()) {
                                        $record->delete();
                                    }
                                } catch (\Throwable $exception) {
                                    $action->reportBulkProcessingFailure();

                                    if ($isFirstException) {
                                        report($exception);
                                        $isFirstException = false;
                                    }
                                }
                            });
                        })
                        ->modalHeading(__('models.topics.actions.delete_only.headingBulk'))
                        ->modalDescription(__('models.topics.actions.delete_only.description')),
                    BulkAction::make('restoreSelected')
                        ->label(__('models.trash.actions.restore_selected'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (BulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                            $isFirstException = true;

                            $records->each(function (Topic $record) use ($action, &$isFirstException): void {
                                try {
                                    if ($record->trashed()) {
                                        $record->restore();
                                    }
                                } catch (\Throwable $exception) {
                                    $action->reportBulkProcessingFailure();

                                    if ($isFirstException) {
                                        report($exception);
                                        $isFirstException = false;
                                    }
                                }
                            });
                        }),
                ]),
            ]);
    }
}
