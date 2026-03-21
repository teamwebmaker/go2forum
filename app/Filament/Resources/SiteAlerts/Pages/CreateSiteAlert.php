<?php

namespace App\Filament\Resources\SiteAlerts\Pages;

use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteAlert extends CreateRecord
{
    protected static string $resource = SiteAlertResource::class;

    public function getHeading(): string
    {
        return __('models.site_alerts.titles.create');
    }
}
