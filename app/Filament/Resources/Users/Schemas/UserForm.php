<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Actions\Action;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\ImageUploadService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)->schema([
                    TextInput::make('name')
                        ->label(UserResource::labelFor('name'))
                        ->required(),
                    TextInput::make('surname')
                        ->label(UserResource::labelFor('surname'))
                        ->required(),
                ]),
                Grid::make(1)->schema([
                    TextInput::make('email')
                        ->label(UserResource::labelFor('email'))
                        ->email()
                        ->required(),
                    TextInput::make('phone')
                        ->label(UserResource::labelFor('phone'))
                        ->tel()
                        ->default(null),
                ]),
                FileUpload::make('image')
                    ->label(UserResource::labelFor('image'))
                    ->image()
                    ->disk('public')
                    ->directory(trim(User::AVATAR_DIR, '/'))
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                    ])
                    ->maxSize(1024) // 1MB
                    ->imageEditor()
                    ->imagePreviewHeight('100')
                    ->helperText('დაშვებული ფორმატები: PNG, JPG, WEBP, მაქს ზომა 1MB.')
                    ->downloadable()
                    ->saveUploadedFileUsing(function ($file, callable $get, $record) {
                        $old = $record?->image ?? $get('image');

                        return ImageUploadService::handleOptimizedImageUpload(
                            file: $file,
                            destinationPath: User::AVATAR_DIR,
                            oldFile: $old,
                            webpQuality: 80,
                            optimize: true,
                            disk: 'public',
                            maxWidth: 256,
                            maxHeight: 256,
                        );
                    }),

                Grid::make(1)->schema([
                    DateTimePicker::make('email_verified_at')
                        ->label(UserResource::labelFor('email_verified_at'))
                        ->nullable()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                        ->suffixAction(
                            Action::make('clearEmailVerifiedAt')
                                ->icon('heroicon-m-x-mark')
                                ->color('gray')
                                ->tooltip(__('models.users.actions.clear_email_verified_at'))
                                ->visible(fn($state): bool => filled($state))
                                ->action(fn(Set $set): mixed => $set('email_verified_at', null)),
                            isInline: true
                        ),
                    DateTimePicker::make('phone_verified_at')
                        ->label(UserResource::labelFor('phone_verified_at'))
                        ->nullable()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                        ->suffixAction(
                            Action::make('clearPhoneVerifiedAt')
                                ->icon('heroicon-m-x-mark')
                                ->color('gray')
                                ->tooltip(__('models.users.actions.clear_phone_verified_at'))
                                ->visible(fn($state): bool => filled($state))
                                ->action(fn(Set $set): mixed => $set('phone_verified_at', null)),
                            isInline: true
                        ),
                ]),
                TextInput::make('password')
                    ->label(UserResource::labelFor('password'))
                    ->password()
                    // Required only on create
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->rule('sometimes')
                    ->rule('min:8')
                    // Allow blank password on update (keeps current password)
                    ->dehydrated(fn($state, string $operation) => $operation === 'create' || filled($state)),

                Grid::make(2)->schema([
                    Toggle::make('is_expert')
                        ->label(UserResource::labelFor('is_expert'))
                        ->required(),
                    Toggle::make('is_top_commentator')
                        ->label(UserResource::labelFor('is_top_commentator'))
                        ->required(),
                    Toggle::make('is_blocked')
                        ->label(UserResource::labelFor('is_blocked'))
                        ->required(),
                ]),
            ]);
    }
}
