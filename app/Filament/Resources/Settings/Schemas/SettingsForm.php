<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Filament\Resources\Settings\SettingsResource;
use Filament\Schemas\Components\Section;
class SettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Toggle::make('is_phone_verification_enabled')
                            ->label(SettingsResource::labelFor('is_phone_verification_enabled')),
                        Toggle::make('is_email_verification_enabled')
                            ->label(SettingsResource::labelFor('is_email_verification_enabled')),
                    ])
            ]);
    }
}
