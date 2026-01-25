<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\PublicDocument;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();

        // Get daily counts for current month
        $countsByDate = User::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        // Build chart array with zero-fill for missing days
        $chart = [];
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $dayKey = $cursor->toDateString();
            $chart[] = (int) ($countsByDate[$dayKey] ?? 0);
            $cursor->addDay();
        }

        $monthlyTotal = array_sum($chart);
        $totalUsers = User::count();

        $totalCategories = Category::count();
        $totalPublicDocuments = PublicDocument::count();
        return [
            Stat::make('სულ მომხმარებლები', $totalUsers)
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('ახალი მომხმარებლები', $monthlyTotal)
                ->description('ამჟამინდელი თვის ჭრილში')
                ->chart($chart)
                ->color('success'),

            Stat::make('კატეგორიები', $totalCategories)
                ->icon('heroicon-o-rectangle-stack')
                ->color('warning'),

            Stat::make('საჯარო დოკუმენტები', $totalPublicDocuments)
                ->icon('heroicon-o-document-text')
                ->color('info'),
        ];

    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'lg' => 2];
    }

}
