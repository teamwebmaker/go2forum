<?php

namespace App\Filament\Resources\SiteAlerts\Pages;

use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteAlerts extends ListRecords
{
    protected static string $resource = SiteAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
