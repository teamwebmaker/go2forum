<?php

namespace App\Filament\Resources\PublicDocuments\Pages;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;


class EditPublicDocument extends EditRecord
{
    protected static string $resource = PublicDocumentResource::class;

    public function getHeading(): string
    {
        return __('models.public_documents.titles.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->modalHeading(__('models.public_documents.actions.delete.heading'))
        ];
    }
}
