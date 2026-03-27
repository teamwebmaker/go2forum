<?php

namespace App\Filament\Resources\PublicDocumentUserViews;

use App\Filament\Resources\PublicDocumentUserViews\Pages\ListPublicDocumentUserViews;
use App\Filament\Resources\PublicDocumentUserViews\Tables\PublicDocumentUserViewsTable;
use App\Models\PublicDocumentUserView;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PublicDocumentUserViewResource extends Resource
{
    protected static ?string $model = PublicDocumentUserView::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;
    protected static string|\UnitEnum|null $navigationGroup = 'კონტენტი';
    protected static ?string $recordTitleAttribute = 'id';
    protected static bool $isGloballySearchable = false;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationParentItem = 'დოკუმენტები';

    public static function labelFor(string $field): string
    {
        return __("models.public_document_user_views.fields.$field");
    }

    public static function getNavigationLabel(): string
    {
        return __('models.public_document_user_views.plural');
    }

    public static function getModelLabel(): string
    {
        return __('models.public_document_user_views.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.public_document_user_views.plural');
    }

    public static function table(Table $table): Table
    {
        return PublicDocumentUserViewsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,name,requires_auth_to_view',
                'user:id,name,surname,nickname',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicDocumentUserViews::route('/'),
        ];
    }
}
