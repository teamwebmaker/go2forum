<?php

namespace App\Filament\Resources\Topics\Schemas;

use App\Filament\Resources\Topics\TopicResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TopicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(TopicResource::labelFor('user_id'))
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('category_id')
                    ->label(TopicResource::labelFor('category_id'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('title')
                    ->label(TopicResource::labelFor('title'))
                    ->required(),
                Select::make('status')
                    ->label(TopicResource::labelFor('status'))
                    ->options([
                        'active' => __('models.topics.statuses.active'),
                        'closed' => __('models.topics.statuses.closed'),
                        'disabled' => __('models.topics.statuses.disabled'),
                    ])
                    ->default('active')
                    ->required(),
                Grid::make(1)->schema([
                    Toggle::make('pinned')
                        ->label(TopicResource::labelFor('pinned'))
                        ->required(),
                    Toggle::make('visibility')
                        ->label(TopicResource::labelFor('visibility'))
                        ->default(true)
                        ->required(),
                ])
            ]);
    }
}
