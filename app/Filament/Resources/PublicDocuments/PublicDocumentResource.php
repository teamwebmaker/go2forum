<?php

namespace App\Filament\Resources\PublicDocuments;

use App\Models\PublicDocument;

use App\Filament\Resources\PublicDocuments\Pages\CreatePublicDocument;
use App\Filament\Resources\PublicDocuments\Pages\EditPublicDocument;
use App\Filament\Resources\PublicDocuments\Pages\ListPublicDocuments;
use App\Filament\Resources\PublicDocuments\Schemas\PublicDocumentForm;
use App\Filament\Resources\PublicDocuments\Schemas\PublicDocumentInfolist;
use App\Filament\Resources\PublicDocuments\Tables\PublicDocumentsTable;

use BackedEnum;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PublicDocumentResource extends Resource
{
    public const STORAGE_DIR = 'documents/public_documents';

    protected static ?string $model = PublicDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;


    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    protected static ?int $navigationSort = 4;


    public static function labelFor(string $field): string
    {
        return __("models.public_documents.fields.$field");
    }

    public static function getNavigationLabel(): string
    {
        return __('models.public_documents.plural');
    }

    public static function getModelLabel(): string
    {
        return __('models.public_documents.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.public_documents.plural');
    }
    public static function form(Schema $schema): Schema
    {
        return PublicDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PublicDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicDocumentsTable::configure($table);
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
            'index' => ListPublicDocuments::route('/'),
            'create' => CreatePublicDocument::route('/create'),
            // 'view' => ViewPublicDocument::route('/{record}'),
            'edit' => EditPublicDocument::route('/{record}/edit'),
        ];
    }
}
