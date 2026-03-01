<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('models.dashboard.filters.heading'))
                    ->schema([
                        Select::make('period')
                            ->label(__('models.dashboard.filters.period'))
                            ->options([
                                'all' => __('models.dashboard.filters.options.all'),
                                'yesterday' => __('models.dashboard.filters.options.yesterday'),
                                '1' => __('models.dashboard.filters.options.past_1_month'),
                                '3' => __('models.dashboard.filters.options.past_3_months'),
                                '6' => __('models.dashboard.filters.options.past_6_months'),
                                '12' => __('models.dashboard.filters.options.past_12_months'),
                            ])
                            ->default('all')
                            ->native(false)
                            ->live(),
                    ])
                    ->columns(1),
            ]);
    }
}
