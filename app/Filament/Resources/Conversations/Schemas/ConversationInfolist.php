<?php

namespace App\Filament\Resources\Conversations\Schemas;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label(ConversationResource::labelFor('id'))
                    ->numeric(),
                TextEntry::make('kind')
                    ->label(ConversationResource::labelFor('kind'))
                    ->formatStateUsing(fn(string $state): string => __('models.conversations.kinds.' . $state))
                    ->badge()
                    ->color(fn(string $state): string => $state === Conversation::KIND_TOPIC ? 'info' : 'success'),
                TextEntry::make('topic.title')
                    ->label(ConversationResource::labelFor('topic_id'))
                    ->placeholder('-'),
                TextEntry::make('participants_count')
                    ->label(ConversationResource::labelFor('participants_count'))
                    ->numeric()
                    ->placeholder('-'),
                Actions::make([
                    self::makeParticipantsAction(),
                ]),
                TextEntry::make('messages_count')
                    ->label(ConversationResource::labelFor('messages_count'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('last_message_at')
                    ->label(ConversationResource::labelFor('last_message_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(ConversationResource::labelFor('created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(ConversationResource::labelFor('updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
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

    protected static function makeParticipantsAction(): Action
    {
        return Action::make('participants')
            ->label(__('models.conversations.actions.participants.label'))
            ->icon(Heroicon::OutlinedUsers)
            ->iconPosition(IconPosition::Before)
            ->modalHeading(fn(Conversation $record): string => __('models.conversations.actions.participants.heading', [
                'count' => ($record->participants_count ?? null) ?: $record->participants()->count(),
            ]))
            ->modalDescription(__('models.conversations.actions.participants.description'))
            ->slideOver()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('models.conversations.actions.participants.close'))
            ->modalWidth(Width::ThreeExtraLarge)
            ->visible(fn(Conversation $record): bool => $record->participants()->exists())
            ->modalContent(fn(Conversation $record) => view('filament.conversations.participants-modal', [
                'participants' => self::participantsForModal($record),
            ]));
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
