<?php

namespace App\Filament\Resources\Conversations;

use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Filament\Resources\Conversations\Pages\ViewConversation;
use App\Filament\Resources\Conversations\Schemas\ConversationInfolist;
use App\Filament\Resources\Conversations\Tables\ConversationsTable;
use App\Models\Conversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'id';
    protected static bool $isGloballySearchable = false;
    protected static ?int $navigationSort = 4;

    public static function labelFor(string $field): string
    {
        return __("models.conversations.fields.$field");
    }

    public static function getNavigationLabel(): string
    {
        return __('models.conversations.plural');
    }

    public static function getModelLabel(): string
    {
        return __('models.conversations.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.conversations.plural');
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConversationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConversationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'topic:id,title',
                // 'directUser1:id,name,surname',
                // 'directUser2:id,name,surname',
                'participants:conversation_id,user_id,joined_at',
                'participants.user:id,name,surname',
            ])
            ->withCount([
                'participants',
                'messages',
            ]);
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
            'index' => ListConversations::route('/'),
            'view' => ViewConversation::route('/{record}'),
        ];
    }
}
