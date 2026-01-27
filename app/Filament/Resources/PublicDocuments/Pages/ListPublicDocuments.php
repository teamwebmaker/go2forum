<?php

namespace App\Filament\Resources\PublicDocuments\Pages;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicDocuments extends ListRecords
{
    protected static string $resource = PublicDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
