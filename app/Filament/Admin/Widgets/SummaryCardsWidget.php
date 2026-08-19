<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BookPromotion;
use App\Models\Publication;
use App\Models\RoyaltyEntry;
use App\Models\Work;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class SummaryCardsWidget extends Widget
{
    protected static string $view = 'filament.widgets.summary-cards';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $user = auth()->user();
        $cacheKey = $user->dashboardCacheNamespace().':summary';

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $previousMonth = now()->subMonth();

            $works = Work::query()
                ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->where('user_id', $user->getKey()));
            $publications = Publication::query()
                ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->whereHas('work', fn ($workQuery) => $workQuery->where('user_id', $user->getKey())));
            $royalties = RoyaltyEntry::query()
                ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->whereHas('publication.work', fn ($workQuery) => $workQuery->where('user_id', $user->getKey())));
            $promotions = BookPromotion::query()
                ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->whereHas('publication.work', fn ($workQuery) => $workQuery->where('user_id', $user->getKey())));

            $monthlyRevenue = (clone $royalties)->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->sum('total_royalty');
            $previousRevenue = (clone $royalties)->where('month', $previousMonth->month)
                ->where('year', $previousMonth->year)
                ->sum('total_royalty');

            return [
                'total_works' => $works->count(),
                'monthly_revenue' => $monthlyRevenue,
                'active_publications' => $publications->where('status', 'published')->count(),
                'active_promotions' => $promotions->active()->count(),
                'revenue_change' => $previousRevenue > 0
                    ? (($monthlyRevenue - $previousRevenue) / $previousRevenue) * 100
                    : null,
            ];
        });
    }
}
