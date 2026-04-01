<?php

namespace App\Filament\Resources\Messages\Tables;

use App\Filament\Pages\Trash;
use App\Filament\Resources\Messages\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordClasses(fn(Message $record): ?string => $record->trashed()
                ? 'message-row-deleted'
                : ($record->is_trashed ? 'message-row-trashed' : null))
            ->recordUrl(fn(Message $record): string => $record->is_trashed
                ? Trash::getUrl([
                    'tab' => 'messages',
                    'messages_q' => 'id:' . $record->id,
                ])
                : MessageResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label(MessageResource::labelFor('id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conversation_id')
                    ->label(MessageResource::labelFor('conversation_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sender.full_name')
                    ->label(MessageResource::labelFor('sender_id'))
                    ->placeholder('-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchTerm = trim($search);

                        return $query->whereHas('sender', function (Builder $senderQuery) use ($searchTerm): void {
                            $senderQuery
                                ->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('surname', 'like', "%{$searchTerm}%")
                                ->orWhere('nickname', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                        });
                    }),
                TextColumn::make('reply_to_message_id')
                    ->label(MessageResource::labelFor('reply_to_message_id'))
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('content')
                    ->label(MessageResource::labelFor('content'))
                    ->limit(90)
                    ->searchable(),
                TextColumn::make('original_content')
                    ->label(MessageResource::labelFor('original_content'))
                    ->limit(80)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('edited_content')
                    ->label(MessageResource::labelFor('edited_content'))
                    ->limit(80)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('edited_at')
                    ->label(MessageResource::labelFor('edited_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('attachments_count')
                    ->label(MessageResource::labelFor('attachments_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('likes_count')
                    ->label(MessageResource::labelFor('likes_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(MessageResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(MessageResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('deleted_at')
                //     ->label(MessageResource::labelFor('deleted_at'))
                //     ->dateTime()
                //     ->badge()
                //     ->color(fn($state): string => filled($state) ? 'danger' : 'gray')
                //     ->placeholder('-')
                //     ->sortable()
                //     ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('conversation_kind')
                    ->label(MessageResource::labelFor('conversation_kind'))
                    ->options([
                        Conversation::KIND_TOPIC => __('models.conversations.kinds.topic'),
                        Conversation::KIND_PRIVATE => __('models.conversations.kinds.private'),
                    ])
                    ->searchable()
                    ->preload()
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->whereHas('conversation', fn($conversationQuery) => $conversationQuery->where('kind', $value));
                    }),
                SelectFilter::make('conversation_id')
                    ->label(__('models.messages.filters.conversation'))
                    ->options(fn(): array => Conversation::query()
                        ->with('topic:id,title')
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(function (Conversation $conversation): array {
                            $context = $conversation->topic?->title;

                            if (blank($context)) {
                                $context = $conversation->isPrivate()
                                    ? __('models.conversations.kinds.private')
                                    : __('models.conversations.kinds.topic');
                            }

                            return [
                                $conversation->id => "#{$conversation->id} · {$context}",
                            ];
                        })
                        ->all())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('sender_id')
                    ->label(MessageResource::labelFor('sender_id'))
                    ->relationship('sender', 'name')
                    ->getOptionLabelFromRecordUsing(function (User $record): string {
                        $fullName = trim((string) $record->full_name);
                        $nickname = trim((string) ($record->nickname ?? ''));

                        if ($nickname !== '') {
                            return $fullName !== '' ? "{$fullName} (@{$nickname})" : "@{$nickname}";
                        }

                        return $fullName !== '' ? $fullName : (string) ($record->email ?? "#{$record->id}");
                    })
                    ->searchable(['name', 'surname', 'nickname', 'email'])
                    ->preload(),
                TernaryFilter::make('is_reply')
                    ->label(__('models.messages.filters.reply_to_message'))
                    ->trueLabel(__('models.messages.filters.with_reply'))
                    ->falseLabel(__('models.messages.filters.without_reply'))
                    ->queries(
                        true: fn($query) => $query->whereNotNull('reply_to_message_id'),
                        false: fn($query) => $query->whereNull('reply_to_message_id'),
                    ),
                TernaryFilter::make('is_edited')
                    ->label(__('models.messages.filters.edited'))
                    ->trueLabel(__('models.messages.filters.edited_only'))
                    ->falseLabel(__('models.messages.filters.not_edited_only'))
                    ->queries(
                        true: fn($query) => $query->whereNotNull('edited_at'),
                        false: fn($query) => $query->whereNull('edited_at'),
                    ),
                TernaryFilter::make('is_trashed')
                    ->label(__('models.messages.filters.in_trash'))
                    ->placeholder(__('models.messages.filters.all'))
                    ->trueLabel(__('models.messages.filters.in_trash_only'))
                    ->falseLabel(__('models.messages.filters.not_in_trash_only'))
                    ->default(false)
                    ->queries(
                        true: fn($query) => $query->where('is_trashed', true),
                        false: fn($query) => $query->where('is_trashed', false),
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon(Heroicon::OutlinedEye)
                    ->iconPosition(IconPosition::Before),
                EditAction::make()
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->iconPosition(IconPosition::Before)
                    ->hidden(fn(Message $record): bool => (bool) $record->is_trashed),
                DeleteAction::make()
                    ->icon(Heroicon::OutlinedTrash)
                    ->iconPosition(IconPosition::Before)
                    ->hidden(fn(Message $record): bool => (bool) $record->is_trashed)
                    ->modalHeading(__('models.messages.actions.delete.heading'))
                    ->modalDescription(__('models.messages.actions.trash.description'))
                    ->using(function (Message $record): bool {
                        $record->moveToTrash();

                        return true;
                    }),
                Action::make('restoreFromTrash')
                    ->label(__('models.trash.actions.restore'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn(Message $record): bool => (bool) $record->is_trashed)
                    ->action(function (Message $record): bool {
                        $record->restoreFromTrash();

                        return true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('models.messages.actions.delete.label'))
                        ->chunkSelectedRecords(100)
                        ->using(function (DeleteBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                            $isFirstException = true;

                            $records->each(function (Message $record) use ($action, &$isFirstException): void {
                                try {
                                    $record->moveToTrash();
                                } catch (\Throwable $exception) {
                                    $action->reportBulkProcessingFailure();

                                    if ($isFirstException) {
                                        report($exception);
                                        $isFirstException = false;
                                    }
                                }
                            });
                        })
                        ->modalHeading(__('models.messages.actions.delete.headingBulk'))
                        ->modalDescription(__('models.messages.actions.trash.description')),
                    BulkAction::make('restoreSelected')
                        ->label(__('models.trash.actions.restore_selected'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (BulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                            $isFirstException = true;

                            $records->each(function (Message $record) use ($action, &$isFirstException): void {
                                try {
                                    $record->restoreFromTrash();
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
