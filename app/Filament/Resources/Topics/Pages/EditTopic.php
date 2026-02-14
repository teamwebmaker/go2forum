<?php

namespace App\Filament\Resources\Topics\Pages;

use App\Filament\Resources\Topics\TopicResource;
use App\Models\Topic;
use App\Services\TopicDeletionService;
use Filament\Actions\DeleteAction;
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
                ->modalDescription(__('models.topics.actions.delete_only.description')),
            DeleteAction::make('deleteWithThreadData')
                ->label(__('models.topics.actions.delete_with_thread.label'))
                ->modalHeading(__('models.topics.actions.delete_with_thread.heading'))
                ->modalDescription(__('models.topics.actions.delete_with_thread.description'))
                ->using(function (Topic $record, TopicDeletionService $topicDeletionService): bool {
                    $topicDeletionService->deleteWithThreadData($record);

                    return true;
                }),
        ];
    }
}
