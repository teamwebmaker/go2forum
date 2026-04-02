<?php

namespace App\Filament\Resources\Conversations\Pages;

use App\Filament\Resources\Conversations\ConversationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    public function getHeading(): string
    {
        return __('models.conversations.titles.view');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
