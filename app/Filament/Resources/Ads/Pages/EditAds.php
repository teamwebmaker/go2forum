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

    // Delete old image if new image is uploaded
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Old file path from DB
        $old = $this->record->image;

        // New file path from form (can be null if user clicked X)
        $new = $data['image'] ?? null;

        // If image changed OR removed => delete old file
        if ($old && $old !== $new) {
            Storage::disk('public')->delete($old);
        }

        return $data;
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
