<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BookPromotion;
use App\Models\KdpReportRow;
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

            $revenuePeriod = sprintf('%04d-%02d', $currentYear, $currentMonth);
            $revenueByCurrency = [];
            $revenueSource = 'royalty_entries';
            if ((float) $monthlyRevenue === 0.0) {
                $latestKdpPeriod = KdpReportRow::query()
                    ->where('row_kind', 'royalty')
                    ->whereNotNull('report_period')
                    ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->where('user_id', $user->getKey()))
                    ->max('report_period');

                if ($latestKdpPeriod) {
                    $revenueByCurrency = KdpReportRow::query()
                        ->where('row_kind', 'royalty')
                        ->where('report_period', $latestKdpPeriod)
                        ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->where('user_id', $user->getKey()))
                        ->whereNotNull('currency')
                        ->selectRaw('currency, SUM(COALESCE(total_earnings, 0)) AS total')
                        ->groupBy('currency')
                        ->havingRaw('SUM(COALESCE(total_earnings, 0)) <> 0')
                        ->pluck('total', 'currency')
                        ->map(fn ($total): float => (float) $total)
                        ->all();
                    $monthlyRevenue = $revenueByCurrency['EUR'] ?? (reset($revenueByCurrency) ?: 0);
                    $revenuePeriod = (string) $latestKdpPeriod;
                    $revenueSource = 'kdp_report_rows';
                }
            }

            return [
                'total_works' => $works->count(),
                'monthly_revenue' => $monthlyRevenue,
                'revenue_by_currency' => $revenueByCurrency,
                'revenue_currency' => array_key_exists('EUR', $revenueByCurrency) ? 'EUR' : (array_key_first($revenueByCurrency) ?? 'EUR'),
                'revenue_source' => $revenueSource,
                'revenue_period' => $revenuePeriod,
                'active_publications' => $publications->where('status', 'published')->count(),
                'active_promotions' => $promotions->active()->count(),
                'revenue_change' => $previousRevenue > 0
                    ? (($monthlyRevenue - $previousRevenue) / $previousRevenue) * 100
                    : null,
            ];
        });
    }
}
