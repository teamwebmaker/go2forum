<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\Message;
use App\Services\MessageDeletionService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    public function getHeading(): string
    {
        return __('models.messages.titles.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::OutlinedPencilSquare)
                ->iconPosition(IconPosition::Before),
            DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash)
                ->iconPosition(IconPosition::Before)
                ->hidden(false)
                ->modalHeading(__('models.messages.actions.delete.heading'))
                ->modalDescription(__('models.messages.actions.delete.description'))
                ->using(function (Message $record, MessageDeletionService $messageDeletionService): bool {
                    $messageDeletionService->deleteByAdmin($record);

                    return true;
                }),
        ];
    }
}
