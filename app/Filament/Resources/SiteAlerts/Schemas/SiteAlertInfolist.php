<?php

namespace App\Filament\Resources\SiteAlerts\Schemas;

use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use App\Models\SiteAlert;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SiteAlertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label(SiteAlertResource::labelFor('title'))
                    ->placeholder('-'),
                TextEntry::make('content')
                    ->label(SiteAlertResource::labelFor('content'))
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->label(SiteAlertResource::labelFor('type'))
                    ->formatStateUsing(fn(string $state): string => __("models.site_alerts.types.$state"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        SiteAlert::TYPE_SUCCESS => 'success',
                        SiteAlert::TYPE_ERROR => 'danger',
                        SiteAlert::TYPE_WARNING => 'warning',
                        default => 'info',
                    }),
                TextEntry::make('audience')
                    ->label(SiteAlertResource::labelFor('audience'))
                    ->formatStateUsing(fn(string $state): string => __("models.site_alerts.audiences.$state"))
                    ->badge()
                    ->color('gray'),
                IconEntry::make('is_active')
                    ->label(SiteAlertResource::labelFor('is_active'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconEntry::make('is_closable')
                    ->label(SiteAlertResource::labelFor('is_closable'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextEntry::make('sort_order')
                    ->label(SiteAlertResource::labelFor('sort_order'))
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label(SiteAlertResource::labelFor('created_at'))
                    ->since()
                    ->timezone(SiteAlert::ADMIN_TIMEZONE)
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(SiteAlertResource::labelFor('updated_at'))
                    ->since()
                    ->timezone(SiteAlert::ADMIN_TIMEZONE)
                    ->placeholder('-'),
            ]);
    }
}
