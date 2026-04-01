<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Pages\Trash;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordClasses(fn(User $record): ?string => $record->trashed() ? 'resource-row-trashed' : null)
            ->recordUrl(fn(User $record): string => $record->trashed()
                ? Trash::getUrl([
                    'tab' => 'users',
                    'users_q' => 'id:' . $record->id,
                ])
                : UserResource::getUrl('edit', ['record' => $record]))
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
                TextColumn::make('nickname')
                    ->label(UserResource::labelFor('nickname'))
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
                    ->icon(fn(bool $state): string => $state ? 'heroicon-o-star' : 'heroicon-o-minus')
                    ->color(fn(bool $state): string => $state ? 'info' : 'gray'),
                IconColumn::make('is_top_commentator')
                    ->label(UserResource::labelFor('is_top_commentator'))
                    ->icon(fn(bool $state): string => $state ? 'heroicon-o-check-badge' : 'heroicon-o-minus')
                    ->color(fn(bool $state): string => $state ? 'warning' : 'gray'),
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
                TextColumn::make('deleted_at')
                    ->label(UserResource::labelFor('deleted_at'))
                    ->dateTime()
                    ->badge()
                    ->color(fn($state): string => filled($state) ? 'warning' : 'gray')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label(__('models.messages.filters.in_trash'))
                    ->placeholder(__('models.messages.filters.not_in_trash_only'))
                    ->trueLabel(__('models.messages.filters.all'))
                    ->falseLabel(__('models.messages.filters.in_trash_only')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn(User $record): bool => $record->trashed()),
                DeleteAction::make()
                    ->modalHeading(__('models.users.actions.delete.heading'))
                    ->hidden(fn(User $record): bool => $record->trashed()),
                RestoreAction::make()
                    ->visible(fn(User $record): bool => $record->trashed()),
            ])
            ->toolbarActions([
                Action::make('exportUsers')
                    ->label(__('models.users.actions.export.label'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->modalHeading(__('models.users.actions.export.heading'))
                    ->modalDescription(__('models.users.actions.export.description'))
                    ->modalSubmitActionLabel(__('models.users.actions.export.submit'))
                    ->form([
                        Toggle::make('select_all')
                            ->label(__('models.users.actions.export.select_all'))
                            ->default(false)
                            ->live(),
                        Select::make('user_ids')
                            ->label(__('models.users.actions.export.users'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn(): array => self::userOptions())
                            ->getSearchResultsUsing(fn(string $search): array => self::userOptions($search))
                            ->getOptionLabelsUsing(fn(array $values): array => self::userOptionsByIds($values))
                            ->disabled(fn(Get $get): bool => (bool) $get('select_all'))
                            ->required(fn(Get $get): bool => !(bool) $get('select_all')),
                    ])
                    ->action(function (array $data) {
                        $selectAll = (bool) ($data['select_all'] ?? false);
                        $selectedIds = collect($data['user_ids'] ?? [])
                            ->filter(fn($id) => filled($id))
                            ->map(fn($id): int => (int) $id)
                            ->unique()
                            ->values()
                            ->all();

                        if (!$selectAll && empty($selectedIds)) {
                            Notification::make()
                                ->danger()
                                ->title(__('models.users.actions.export.empty'))
                                ->send();

                            return null;
                        }

                        $query = self::baseUsersQuery()
                            ->select(['name', 'surname', 'nickname', 'email', 'phone']);

                        if (!$selectAll) {
                            $query->whereKey($selectedIds);
                        }

                        $users = $query
                            ->orderBy('name')
                            ->orderBy('surname')
                            ->get();

                        $fileName = 'users_export_' . now()->format('Ymd_His') . '.xlsx';

                        return response()->streamDownload(function () use ($users, $fileName): void {
                            $writer = app(Writer::class);
                            $writer->openToBrowser($fileName);
                            $writer->addRow(Row::fromValues(['name', 'surname', 'nickname', 'email', 'phone']));

                            foreach ($users as $user) {
                                $writer->addRow(Row::fromValues([
                                    (string) ($user->name ?? ''),
                                    (string) ($user->surname ?? ''),
                                    (string) ($user->nickname ?? ''),
                                    (string) ($user->email ?? ''),
                                    (string) ($user->phone ?? ''),
                                ]));
                            }

                            $writer->close();
                        }, $fileName, [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->chunkSelectedRecords(100)
                        ->using(function (DeleteBulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                            $isFirstException = true;

                            $records->each(function (User $record) use ($action, &$isFirstException): void {
                                try {
                                    if (!$record->trashed()) {
                                        $record->delete();
                                    }
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
                    BulkAction::make('restoreSelected')
                        ->label(__('models.trash.actions.restore_selected'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (BulkAction $action, EloquentCollection|Collection|LazyCollection $records): void {
                            $isFirstException = true;

                            $records->each(function (User $record) use ($action, &$isFirstException): void {
                                try {
                                    if ($record->trashed()) {
                                        $record->restore();
                                    }
                                } catch (\Throwable $exception) {
                                    $action->reportBulkProcessingFailure();

                                    if ($isFirstException) {
                                        report($exception);
                                        $isFirstException = false;
                                    }
                                }
                            });
                        }),
                ]),
            ]);
    }

    protected static function baseUsersQuery()
    {
        $query = User::query();
        $userId = Auth::id();

        if ($userId) {
            $query->whereKeyNot($userId);
        }

        return $query;
    }

    protected static function userOptions(?string $search = null): array
    {
        $query = self::baseUsersQuery()
            ->select(['id', 'name', 'surname', 'nickname', 'email'])
            ->orderBy('name')
            ->orderBy('surname');

        if (filled($search)) {
            $searchTerm = trim((string) $search);

            $query->where(function ($subQuery) use ($searchTerm): void {
                $subQuery
                    ->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('nickname', 'like', "%{$searchTerm}%");
            });
        }

        return $query
            ->limit(100)
            ->get()
            ->mapWithKeys(fn(User $user): array => [
                $user->id => self::userLabel($user),
            ])
            ->all();
    }

    protected static function userOptionsByIds(array $ids): array
    {
        $normalizedIds = collect($ids)
            ->filter(fn($id) => filled($id))
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($normalizedIds === []) {
            return [];
        }

        return self::baseUsersQuery()
            ->select(['id', 'name', 'surname', 'nickname', 'email'])
            ->whereKey($normalizedIds)
            ->get()
            ->mapWithKeys(fn(User $user): array => [
                $user->id => self::userLabel($user),
            ])
            ->all();
    }

    protected static function userLabel(User $user): string
    {
        $fullName = trim((string) $user->full_name);
        $nickname = trim((string) ($user->nickname ?? ''));

        if ($nickname !== '') {
            return "{$fullName} ({$nickname})";
        }

        if ($fullName !== '') {
            return $fullName;
        }

        return (string) ($user->email ?? $user->id);
    }
}
