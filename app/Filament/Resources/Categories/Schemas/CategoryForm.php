<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(CategoryResource::labelFor('name'))
                            ->required(),
                        Select::make('ad_id')
                            ->label(CategoryResource::labelFor('ad_id'))
                            ->relationship('ad', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('order')
                            ->label(CategoryResource::labelFor('order'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->unique(
                                table: Category::class,
                                column: 'order',
                                ignorable: fn(?Category $record) => $record
                            ),
                        Toggle::make('visibility')
                            ->label(CategoryResource::labelFor('visibility'))
                            ->required(),
                    ])
            ]);
    }
}
