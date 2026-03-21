<?php

namespace App\Filament\Resources\SiteAlerts;

use App\Filament\Resources\SiteAlerts\Pages\CreateSiteAlert;
use App\Filament\Resources\SiteAlerts\Pages\EditSiteAlert;
use App\Filament\Resources\SiteAlerts\Pages\ListSiteAlerts;
use App\Filament\Resources\SiteAlerts\Schemas\SiteAlertForm;
use App\Filament\Resources\SiteAlerts\Schemas\SiteAlertInfolist;
use App\Filament\Resources\SiteAlerts\Tables\SiteAlertsTable;
use App\Models\SiteAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SiteAlertResource extends Resource
{
    protected static ?string $model = SiteAlert::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $recordTitleAttribute = 'id';
    protected static bool $isGloballySearchable = false;
    protected static ?int $navigationSort = 8;

    public static function labelFor(string $field): string
    {
        return __("models.site_alerts.fields.$field");
    }

    public static function getNavigationLabel(): string
    {
        return __('models.site_alerts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('models.site_alerts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.site_alerts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return SiteAlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteAlertsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteAlertInfolist::configure($schema);
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
            'index' => ListSiteAlerts::route('/'),
            'create' => CreateSiteAlert::route('/create'),
            'edit' => EditSiteAlert::route('/{record}/edit'),
        ];
    }
}
