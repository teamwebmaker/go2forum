<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Ads;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(CategoryResource::labelFor('name'))
                    ->required(),
                Select::make('ad_id')
                    ->label(CategoryResource::labelFor('ad_id'))
                    ->relationship('ad', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Toggle::make('visibility')
                    ->label(CategoryResource::labelFor('visibility'))
                    ->required(),
            ]);
    }
}
