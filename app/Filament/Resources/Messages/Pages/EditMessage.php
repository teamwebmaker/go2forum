<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\Message;
use App\Services\MessageDeletionService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

class EditMessage extends EditRecord
{
    protected static string $resource = MessageResource::class;

    public function getHeading(): string
    {
        return __('models.messages.titles.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::OutlinedEye)
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
