<?php

namespace App\Filament\Resources\Ads\Pages;

use App\Filament\Resources\Ads\AdsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
class EditAds extends EditRecord
{
    protected static string $resource = AdsResource::class;


    public function getHeading(): string
    {
        return __('models.ads.titles.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->modalHeading(__('models.ads.actions.view.heading')),
            DeleteAction::make()
                ->modalHeading(__('models.ads.actions.delete.heading'))
        ];
    }
}
