<?php

namespace App\Filament\Resources\SiteAlerts\Schemas;

use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use App\Models\SiteAlert;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SiteAlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label(SiteAlertResource::labelFor('title'))
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label(SiteAlertResource::labelFor('content'))
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('type')
                    ->label(SiteAlertResource::labelFor('type'))
                    ->options([
                        SiteAlert::TYPE_SUCCESS => __('models.site_alerts.types.success'),
                        SiteAlert::TYPE_ERROR => __('models.site_alerts.types.error'),
                        SiteAlert::TYPE_WARNING => __('models.site_alerts.types.warning'),
                        SiteAlert::TYPE_INFO => __('models.site_alerts.types.info'),
                    ])
                    ->default(SiteAlert::TYPE_INFO)
                    ->required()
                    ->native(false),
                Select::make('audience')
                    ->label(SiteAlertResource::labelFor('audience'))
                    ->options([
                        SiteAlert::AUDIENCE_ALL => __('models.site_alerts.audiences.all'),
                        SiteAlert::AUDIENCE_AUTH => __('models.site_alerts.audiences.auth'),
                        SiteAlert::AUDIENCE_GUEST => __('models.site_alerts.audiences.guest'),
                    ])
                    ->default(SiteAlert::AUDIENCE_ALL)
                    ->required()
                    ->native(false),
                Toggle::make('is_active')
                    ->label(SiteAlertResource::labelFor('is_active'))
                    ->default(true),
                Toggle::make('is_closable')
                    ->label(SiteAlertResource::labelFor('is_closable'))
                    ->default(false),
                TextInput::make('sort_order')
                    ->label(SiteAlertResource::labelFor('sort_order'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->unique(
                        table: SiteAlert::class,
                        column: 'sort_order',
                        ignorable: fn(?SiteAlert $record) => $record
                    )
                    ->default(0),
            ]);

    }
}
