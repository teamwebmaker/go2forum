<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('models.messages.titles.list');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
