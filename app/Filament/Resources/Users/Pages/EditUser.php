<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\AccountDeletionService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainPassword = null;
    protected bool $passwordChanged = false;

    public function getHeading(): string
    {
        return __('models.users.titles.edit');
    }
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->using(function (User $record, AccountDeletionService $accountDeletionService): bool {
                    $accountDeletionService->deleteByAdmin($record);

                    return true;
                })
                ->modalHeading(__('models.users.actions.delete.heading'))
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            // $this->plainPassword = $data['password'];
            $this->passwordChanged = true;
        } else {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * Delete sessions when user password updates
     */
    protected function afterSave(): void
    {
        if (!$this->passwordChanged) {
            return;
        }

        // Invalidate all sessions for this user
        DB::table('sessions')->where('user_id', $this->record->getKey())->delete();

        // Clear remember token to force re-login for remember-me cookies
        $this->record->forceFill(['remember_token' => null])->saveQuietly();

        // If the admin edited their own account, also end current session
        // if (Auth::id() === $this->record->getKey() && $this->plainPassword) {
        //     Auth::logoutOtherDevices($this->plainPassword);
        //     Auth::logout();
        //     request()->session()->invalidate();
        //     request()->session()->regenerateToken();
        // }
        // $this->plainPassword = null;

        $this->passwordChanged = false;
    }
}
