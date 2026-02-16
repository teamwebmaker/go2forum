<?php

namespace App\Filament\Resources\Conversations\Pages;

use App\Filament\Resources\Conversations\ConversationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('models.conversations.titles.list');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
