<?php

namespace App\Filament\Resources\Topics;

use App\Filament\Resources\Topics\Pages\CreateTopic;
use App\Filament\Resources\Topics\Pages\EditTopic;
use App\Filament\Resources\Topics\Pages\ListTopics;
use App\Filament\Resources\Topics\Schemas\TopicForm;
use App\Filament\Resources\Topics\Schemas\TopicInfolist;
use App\Filament\Resources\Topics\Tables\TopicsTable;
use App\Models\Topic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PencilSquare;

    protected static ?string $recordTitleAttribute = 'title';
    protected static bool $isGloballySearchable = false;
    protected static ?int $navigationSort = 3;

    public static function labelFor(string $field): string
    {
        return __("models.topics.fields.$field");
    }

    public static function getNavigationLabel(): string
    {
        return __('models.topics.plural');
    }

    public static function getModelLabel(): string
    {
        return __('models.topics.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.topics.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return TopicForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TopicInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TopicsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTopics::route('/'),
            'create' => CreateTopic::route('/create'),
            'edit' => EditTopic::route('/{record}/edit'),
        ];
    }
}
