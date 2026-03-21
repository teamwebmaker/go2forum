<?php

namespace App\Filament\Resources\SiteAlerts\Tables;

use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use App\Models\SiteAlert;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SiteAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label(SiteAlertResource::labelFor('title'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('content')
                    ->label(SiteAlertResource::labelFor('content'))
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('type')
                    ->label(SiteAlertResource::labelFor('type'))
                    ->formatStateUsing(fn(string $state): string => __("models.site_alerts.types.$state"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        SiteAlert::TYPE_SUCCESS => 'success',
                        SiteAlert::TYPE_ERROR => 'danger',
                        SiteAlert::TYPE_WARNING => 'warning',
                        default => 'info',
                    })
                    ->searchable(),
                IconColumn::make('is_closable')
                    ->label(SiteAlertResource::labelFor('is_closable'))
                    ->boolean(),
                TextColumn::make('audience')
                    ->label(SiteAlertResource::labelFor('audience'))
                    ->formatStateUsing(fn(string $state): string => __("models.site_alerts.audiences.$state"))
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(SiteAlertResource::labelFor('is_active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(SiteAlertResource::labelFor('sort_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(SiteAlertResource::labelFor('created_at'))
                    ->dateTime()
                    ->timezone(SiteAlert::ADMIN_TIMEZONE)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(SiteAlertResource::labelFor('updated_at'))
                    ->dateTime()
                    ->timezone(SiteAlert::ADMIN_TIMEZONE)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(SiteAlertResource::labelFor('type'))
                    ->options([
                        SiteAlert::TYPE_SUCCESS => __('models.site_alerts.types.success'),
                        SiteAlert::TYPE_ERROR => __('models.site_alerts.types.error'),
                        SiteAlert::TYPE_WARNING => __('models.site_alerts.types.warning'),
                        SiteAlert::TYPE_INFO => __('models.site_alerts.types.info'),
                    ])
                    ->searchable()
                    ->preload(),
                SelectFilter::make('audience')
                    ->label(SiteAlertResource::labelFor('audience'))
                    ->options([
                        SiteAlert::AUDIENCE_ALL => __('models.site_alerts.audiences.all'),
                        SiteAlert::AUDIENCE_AUTH => __('models.site_alerts.audiences.auth'),
                        SiteAlert::AUDIENCE_GUEST => __('models.site_alerts.audiences.guest'),
                    ])
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(SiteAlertResource::labelFor('is_active'))
                    ->trueLabel(__('models.site_alerts.filters.active_only'))
                    ->falseLabel(__('models.site_alerts.filters.inactive_only'))
                    ->queries(
                        true: fn($query) => $query->where('is_active', true),
                        false: fn($query) => $query->where('is_active', false),
                    ),
                TernaryFilter::make('is_closable')
                    ->label(SiteAlertResource::labelFor('is_closable'))
                    ->trueLabel(__('models.site_alerts.filters.closable_only'))
                    ->falseLabel(__('models.site_alerts.filters.non_closable_only'))
                    ->queries(
                        true: fn($query) => $query->where('is_closable', true),
                        false: fn($query) => $query->where('is_closable', false),
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(__('models.site_alerts.actions.view.heading')),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(__('models.site_alerts.actions.delete.headingBulk')),
                ]),
            ]);
    }
}
