<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SettingsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IconEntry::make('is_phone_verification_enabled')
                    ->label('ტელეფონის ვალიდაცია')
                    ->boolean(),
                IconEntry::make('is_email_verification_enabled')
                    ->label('ელ.ფოსტის ვალიდაცია')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('შეიქმნა')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('ბოლოს განახლდა')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
