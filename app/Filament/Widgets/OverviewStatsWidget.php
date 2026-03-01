<?php

namespace App\Filament\Widgets;

use App\Models\Ads;
use App\Models\Category;
use App\Models\Message;
use App\Models\PublicDocument;
use App\Models\Topic;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class OverviewStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $totalUsers = $this->countFor(User::query());
        $totalCategories = $this->countFor(Category::query());
        $totalPublicDocuments = $this->countFor(PublicDocument::query());
        $totalTopics = $this->countFor(Topic::query());
        $totalMessages = $this->countFor(Message::query());
        $totalAds = $this->countFor(Ads::query());

        return [
            Stat::make(__('models.users.plural'), $totalUsers)
                ->icon('heroicon-o-user-group')
                ->color('primary'),

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

    protected function countFor(Builder $query): int
    {
        return $this->applyPeriodFilter($query)->count();
    }

    protected function applyPeriodFilter(Builder $query): Builder
    {
        $period = (string) ($this->pageFilters['period'] ?? 'all');

        if ($period === 'yesterday') {
            return $query->whereBetween('created_at', [
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay(),
            ]);
        }

        if (!in_array($period, ['1', '3', '6', '12'], true)) {
            return $query;
        }

        return $query->where('created_at', '>=', now()->subMonthsNoOverflow((int) $period)->startOfDay());
    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'lg' => 2];
    }

}
