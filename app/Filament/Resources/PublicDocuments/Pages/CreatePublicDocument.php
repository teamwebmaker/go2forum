<?php

namespace App\Filament\Resources\PublicDocuments\Pages;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicDocument extends CreateRecord
{
    protected static string $resource = PublicDocumentResource::class;
}
