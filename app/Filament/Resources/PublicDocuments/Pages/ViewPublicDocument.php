<?php

namespace App\Filament\Resources\PublicDocuments\Pages;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPublicDocument extends ViewRecord
{
    protected static string $resource = PublicDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
