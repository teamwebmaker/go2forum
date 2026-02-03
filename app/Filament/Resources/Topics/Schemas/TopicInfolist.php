<?php

namespace App\Filament\Resources\Topics\Schemas;

use App\Filament\Resources\Topics\TopicResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TopicInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.full_name')
                    ->label(TopicResource::labelFor('user_id'))
                    ->placeholder('-'),
                TextEntry::make('category.name')
                    ->label(TopicResource::labelFor('category_id'))
                    ->placeholder('-'),
                TextEntry::make('title')
                    ->label(TopicResource::labelFor('title')),
                TextEntry::make('status')
                    ->label(TopicResource::labelFor('status'))
                    ->formatStateUsing(fn(string $state) => __('models.topics.statuses.' . $state))
                    ->badge()
                    ->color(fn($record) => $record->status_color),
                TextEntry::make('slug')
                    ->label(TopicResource::labelFor('slug')),
                TextEntry::make('messages_count')
                    ->label(TopicResource::labelFor('messages_count'))
                    ->numeric(),
                IconEntry::make('pinned')
                    ->label(TopicResource::labelFor('pinned'))
                    ->boolean(),
                IconEntry::make('visibility')
                    ->label(TopicResource::labelFor('visibility'))
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(TopicResource::labelFor('created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(TopicResource::labelFor('updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
