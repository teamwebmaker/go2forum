<?php

namespace App\Filament\Resources\SiteAlerts\Pages;

use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteAlert extends EditRecord
{
    protected static string $resource = SiteAlertResource::class;

    public function getHeading(): string
    {
        return __('models.site_alerts.titles.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('models.site_alerts.actions.delete.heading')),
        ];
    }
}
