<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\Message;
use Filament\Actions\Action;
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
        ];
    }
}
