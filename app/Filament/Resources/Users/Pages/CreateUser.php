<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Pages\Trash;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected bool $createInTrashOnCreate = false;

    public function getHeading(): string
    {
        return __('models.users.titles.create');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->createInTrashOnCreate = (bool) ($data['create_in_trash'] ?? false);
        unset($data['create_in_trash']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->createInTrashOnCreate) {
            $this->record->delete();
        }
    }

    protected function getRedirectUrl(): string
    {
        if ($this->createInTrashOnCreate && filled($this->record?->getKey())) {
            return Trash::getUrl([
                'tab' => 'users',
                'users_q' => 'id:' . $this->record->getKey(),
            ]);
        }

        return parent::getRedirectUrl();
    }
}
