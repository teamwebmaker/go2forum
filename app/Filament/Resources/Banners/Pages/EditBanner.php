<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    public function getHeading(): string
    {
        return __('models.banners.titles.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->modalHeading(__('models.banners.actions.view.heading')),
            DeleteAction::make()
                ->modalHeading(__('models.banners.actions.delete.heading')),
        ];
    }
}
