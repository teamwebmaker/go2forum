<?php

namespace App\Filament\Resources\Conversations\Tables;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Services\ConversationDeletionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(ConversationResource::labelFor('id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('kind')
                    ->label(ConversationResource::labelFor('kind'))
                    ->formatStateUsing(fn(string $state): string => __('models.conversations.kinds.' . $state))
                    ->badge()
                    ->color(fn(string $state): string => $state === Conversation::KIND_TOPIC ? 'info' : 'success'),
                TextColumn::make('topic.title')
                    ->label(ConversationResource::labelFor('topic_id'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('participants_count')
                    ->label(ConversationResource::labelFor('participants_count'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon(Heroicon::OutlinedUsers)
                    ->iconPosition(IconPosition::Before)
                    ->action(self::makeParticipantsAction('participantsFromTable'))
                    ->tooltip(__('models.conversations.actions.participants.label')),

                TextColumn::make('messages_count')
                    ->label(ConversationResource::labelFor('messages_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->label(ConversationResource::labelFor('last_message_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(ConversationResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(ConversationResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label(ConversationResource::labelFor('kind'))
                    ->options([
                        Conversation::KIND_TOPIC => __('models.conversations.kinds.topic'),
                        Conversation::KIND_PRIVATE => __('models.conversations.kinds.private'),
                    ])
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_topic')
                    ->label(ConversationResource::labelFor('topic_id'))
                    ->trueLabel(__('models.conversations.filters.with_topic'))
                    ->falseLabel(__('models.conversations.filters.without_topic'))
                    ->queries(
                        true: fn($query) => $query->whereNotNull('topic_id'),
                        false: fn($query) => $query->whereNull('topic_id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon(Heroicon::OutlinedEye)
                    ->iconPosition(IconPosition::Before),
                DeleteAction::make()
                    ->icon(Heroicon::OutlinedTrash)
                    ->iconPosition(IconPosition::Before)
                    ->modalHeading(__('models.conversations.actions.delete.heading'))
                    ->modalDescription(__('models.conversations.actions.delete.description'))
                    ->using(function (Conversation $record, ConversationDeletionService $conversationDeletionService): bool {
                        $conversationDeletionService->deleteByAdmin($record);

                        return true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('models.conversations.actions.delete.label'))
                        ->chunkSelectedRecords(100)
                        ->using(function (DeleteBulkAction $action, EloquentCollection|Collection|LazyCollection $records, ConversationDeletionService $conversationDeletionService): void {
                            $isFirstException = true;

                            $records->each(function (Conversation $record) use ($action, $conversationDeletionService, &$isFirstException): void {
                                try {
                                    $conversationDeletionService->deleteByAdmin($record);
                                } catch (\Throwable $exception) {
                                    $action->reportBulkProcessingFailure();

                                    if ($isFirstException) {
                                        report($exception);
                                        $isFirstException = false;
                                    }
                                }
                            });
                        })
                        ->modalHeading(__('models.conversations.actions.delete.headingBulk'))
                        ->modalDescription(__('models.conversations.actions.delete.description')),
                ]),
            ]);
    }

    protected static function formatPrivateUsers(Conversation $record): string
    {
        if (!$record->isPrivate()) {
            return '-';
        }

        $names = collect([
            $record->directUser1?->full_name,
            $record->directUser2?->full_name,
        ])->filter()->values();

        if ($names->isEmpty()) {
            return '-';
        }

        return $names->implode(' / ');
    }

    protected static function participantNames(Conversation $record): array
    {
        return $record->participants
            ->map(fn($participant) => $participant->user?->full_name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected static function makeParticipantsAction(string $name): Action
    {
        return Action::make($name)
            ->label(__('models.conversations.actions.participants.label'))
            ->icon(Heroicon::OutlinedUsers)
            ->iconPosition(IconPosition::Before)
            ->color('gray')
            ->modalHeading(fn(Conversation $record): string => __('models.conversations.actions.participants.heading', [
                'count' => $record->participants_count ?? 0,
            ]))
            ->modalDescription(__('models.conversations.actions.participants.description'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('models.conversations.actions.participants.close'))
            ->modalWidth(Width::ThreeExtraLarge)
            ->slideOver()
            ->visible(fn(Conversation $record): bool => ($record->participants_count ?? 0) > 0)
            ->modalContent(fn(Conversation $record) => view('filament.conversations.participants-modal', [
                'participants' => self::participantsForModal($record),
            ]));
    }

    protected static function participantsForModal(Conversation $record): array
    {
        return $record->participants()
            ->with('user:id,name,surname')
            ->get()
            ->map(function (Model $participant): array {
                return [
                    'key' => "{$participant->conversation_id}-{$participant->user_id}",
                    'name' => self::participantDisplayName($participant),
                    'joined_at' => $participant->joined_at?->format('Y-m-d H:i'),
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    protected static function participantDisplayName(Model $participant): string
    {
        $firstName = trim((string) ($participant->user?->name ?? ''));
        $lastName = trim((string) ($participant->user?->surname ?? ''));

        $fullName = trim(collect([$firstName, $lastName])->filter()->implode(' '));

        if ($fullName !== '') {
            return $fullName;
        }

        return __('models.conversations.actions.participants.user_fallback', [
            'id' => $participant->user_id,
        ]);
    }
}
