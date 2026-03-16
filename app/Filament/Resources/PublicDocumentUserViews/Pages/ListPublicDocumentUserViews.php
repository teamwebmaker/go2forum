<?php

namespace App\Filament\Resources\PublicDocumentUserViews\Pages;

use App\Filament\Resources\PublicDocumentUserViews\PublicDocumentUserViewResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPublicDocumentUserViews extends ListRecords
{
    protected static string $resource = PublicDocumentUserViewResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('models.public_document_user_views.titles.list');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
