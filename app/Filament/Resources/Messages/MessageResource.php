<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Filament\Resources\Messages\Pages\ViewMessage;
use App\Filament\Resources\Messages\Schemas\MessageForm;
use App\Filament\Resources\Messages\Schemas\MessageInfolist;
use App\Filament\Resources\Messages\Tables\MessagesTable;
use App\Models\Message;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleOvalLeftEllipsis;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationParentItem = 'საუბრები';
    protected static bool $isGloballySearchable = false;

    public static function labelFor(string $field): string
    {
        return __("models.messages.fields.$field");
    }

    public static function getNavigationLabel(): string
    {
        return __('models.messages.plural');
    }

    public static function getModelLabel(): string
    {
        return __('models.messages.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.messages.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return MessageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withTrashed()
            ->with([
                'conversation:id,kind',
                'sender:id,name,surname',
            ])
            ->withCount([
                'attachments',
                'likes',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            // 'view' => ViewMessage::route('/{record}'),
            'edit' => EditMessage::route('/{record}/edit'),
        ];
    }
}
