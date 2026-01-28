<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('role')
                    ->label(UserResource::labelFor('role'))
                    ->badge(),
                TextEntry::make('name')
                    ->label(UserResource::labelFor('name')),
                TextEntry::make('surname')
                    ->label(UserResource::labelFor('surname')),
                TextEntry::make('email')
                    ->label(UserResource::labelFor('email')),
                TextEntry::make('phone')
                    ->label(UserResource::labelFor('phone'))
                    ->placeholder('-'),
                ImageEntry::make('image')
                    ->label(UserResource::labelFor('image'))
                    ->disk('public')
                    ->placeholder('-'),
                IconEntry::make('is_expert')
                    ->label(UserResource::labelFor('is_expert'))
                    ->boolean(),
                IconEntry::make('is_top_commentator')
                    ->label(UserResource::labelFor('is_top_commentator'))
                    ->boolean(),
                IconEntry::make('is_blocked')
                    ->label(UserResource::labelFor('is_blocked'))
                    ->boolean(),
                TextEntry::make('email_verified_at')
                    ->label(UserResource::labelFor('email_verified_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('phone_verified_at')
                    ->label(UserResource::labelFor('phone_verified_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(UserResource::labelFor('created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(UserResource::labelFor('updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
