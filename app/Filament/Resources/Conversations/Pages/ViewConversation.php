<?php

namespace App\Filament\Resources\Conversations\Pages;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Services\ConversationDeletionService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    public function getHeading(): string
    {
        return __('models.conversations.titles.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash)
                ->iconPosition(IconPosition::Before)
                ->modalHeading(__('models.conversations.actions.delete.heading'))
                ->modalDescription(__('models.conversations.actions.delete.description'))
                ->using(function (
                    Conversation $record,
                    ConversationDeletionService $conversationDeletionService
                ): bool {
                    $conversationDeletionService->deleteByAdmin($record);

                    return true;
                }),
        ];
    }
}
