<?php

namespace App\Filament\Resources\Topics\Pages;

use App\Filament\Resources\Topics\TopicResource;
use App\Models\Topic;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    protected static string $resource = TopicResource::class;

    public function getHeading(): string
    {
        return __('models.topics.titles.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make('deleteOnly')
                ->label(__('models.topics.actions.delete_only.label'))
                ->modalHeading(__('models.topics.actions.delete_only.heading'))
                ->modalDescription(__('models.topics.actions.delete_only.description'))
                ->hidden(fn(Topic $record): bool => $record->trashed()),
            RestoreAction::make()
                ->visible(fn(Topic $record): bool => $record->trashed()),
        ];
    }
}
