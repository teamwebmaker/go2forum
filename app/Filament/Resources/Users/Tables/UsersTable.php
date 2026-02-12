<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\AccountDeletionService;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Remove current user
            ->modifyQueryUsing(function ($query) {
                $userId = Auth::id();

                return $userId ? $query->whereKeyNot($userId) : $query;
            })
            ->columns([
                TextColumn::make('role')
                    ->label(UserResource::labelFor('role'))
                    ->badge(),
                TextColumn::make('name')
                    ->label(UserResource::labelFor('name'))
                    ->searchable(),
                TextColumn::make('surname')
                    ->label(UserResource::labelFor('surname'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(UserResource::labelFor('email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(UserResource::labelFor('phone'))
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(UserResource::labelFor('image')),
                IconColumn::make('is_expert')
                    ->label(UserResource::labelFor('is_expert'))
                    ->boolean(),
                IconColumn::make('is_top_commentator')
                    ->label(UserResource::labelFor('is_top_commentator'))
                    ->boolean(),
                IconColumn::make('is_blocked')
                    ->label(UserResource::labelFor('is_blocked'))
                    ->boolean(),
                TextColumn::make('email_verified_at')
                    ->label(UserResource::labelFor('email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone_verified_at')
                    ->label(UserResource::labelFor('phone_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(UserResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(UserResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->chunkSelectedRecords(100)
                    ->using(function (
                        DeleteBulkAction $action,
                        EloquentCollection | Collection | LazyCollection $records,
                        AccountDeletionService $accountDeletionService
                    ): void {
                        $isFirstException = true;

                        $records->each(function (User $record) use ($action, $accountDeletionService, &$isFirstException): void {
                            try {
                                $accountDeletionService->deleteByAdmin($record);
                            } catch (\Throwable $exception) {
                                $action->reportBulkProcessingFailure();

                                if ($isFirstException) {
                                    report($exception);
                                    $isFirstException = false;
                                }
                            }
                        });
                    })
                    ->modalHeading(__('models.users.actions.delete.headingBulk')),
            ]);
    }
}
