<?php

namespace App\Filament\Widgets;

use App\Models\Ads;
use App\Models\Category;
use App\Models\Message;
use App\Models\PublicDocument;
use App\Models\Topic;
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
        $totalTopics = Topic::count();
        $totalMessages = Message::count();
        $totalAds = Ads::count();

        return [
            Stat::make(__('models.users.stats.total'), $totalUsers)
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make(__('models.users.stats.new_current_month'), $monthlyTotal)
                ->description(__('models.users.stats.current_month_range'))
                ->chart($chart)
                ->color('success'),

            Stat::make(__('models.topics.plural'), $totalTopics)
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('warning'),

            Stat::make(__('models.messages.plural'), $totalMessages)
                ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                ->color('success'),

            Stat::make(__('models.categories.plural'), $totalCategories)
                ->icon('heroicon-o-rectangle-stack')
                ->color('warning'),

            Stat::make(__('models.ads.plural'), $totalAds)
                ->icon('heroicon-o-megaphone')
                ->color('danger'),

            Stat::make(__('models.public_documents.plural'), $totalPublicDocuments)
                ->icon('heroicon-o-document-text')
                ->color('info'),

        ];

    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'lg' => 2];
    }

}
