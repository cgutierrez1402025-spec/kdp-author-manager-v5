<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KdpReportRow;
use App\Models\RoyaltyEntry;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class RevenueChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.revenue-chart';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function getChartData(): array
    {
        $user = auth()->user();
        $cacheKey = $user->dashboardCacheNamespace().':revenue-chart';

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $royalties = RoyaltyEntry::query()
                ->when(
                    ! $user->canViewAllAuthorData(),
                    fn ($query) => $query->whereHas(
                        'publication.work',
                        fn ($workQuery) => $workQuery->where('user_id', $user->getKey()),
                    ),
                );

            if ((clone $royalties)->exists()) {
                $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
                $entries = (clone $royalties)
                    ->selectRaw("year, month, COALESCE(currency, 'EUR') AS currency, SUM(total_royalty) AS total")
                    ->groupBy('year', 'month', 'currency')
                    ->get();
                $source = 'royalty_entries';
            } else {
                $kdpRows = KdpReportRow::query()
                    ->where('row_kind', 'royalty')
                    ->whereNotNull('report_period')
                    ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->where('user_id', $user->getKey()));
                $latestPeriod = (clone $kdpRows)->max('report_period');
                $end = $latestPeriod ? Carbon::parse($latestPeriod)->startOfMonth() : now()->startOfMonth();
                $months = collect(range(5, 0))->map(fn ($i) => $end->copy()->subMonths($i));
                $entries = (clone $kdpRows)
                    ->whereBetween('report_period', [$months->first(), $months->last()->copy()->endOfMonth()])
                    ->whereNotNull('currency')
                    ->selectRaw('report_period, currency, SUM(COALESCE(total_earnings, 0)) AS total')
                    ->groupBy('report_period', 'currency')
                    ->get()
                    ->map(function (KdpReportRow $row): object {
                        return (object) [
                            'year' => $row->report_period->year,
                            'month' => $row->report_period->month,
                            'currency' => $row->currency,
                            'total' => $row->total,
                        ];
                    });
                $source = 'kdp_report_rows';
            }

            $labels = $months->map(fn (Carbon $date): string => ucfirst($date->locale('es')->translatedFormat('M Y')))->all();
            $currencies = $entries->groupBy('currency')
                ->filter(fn ($currencyEntries): bool => $currencyEntries->sum(fn ($entry): float => abs((float) $entry->total)) > 0)
                ->keys()
                ->filter()
                ->sortBy(fn (string $currency): int => $currency === 'EUR' ? 0 : 1);
            $series = $currencies->mapWithKeys(function (string $currency) use ($entries, $months): array {
                $currencyEntries = $entries->where('currency', $currency)
                    ->keyBy(fn ($entry): string => "{$entry->year}-{$entry->month}");

                return [$currency => $months->map(
                    fn (Carbon $date): float => (float) ($currencyEntries->get($date->format('Y-n'))?->total ?? 0)
                )->all()];
            })->all();

            $primaryCurrency = array_key_exists('EUR', $series) ? 'EUR' : (array_key_first($series) ?? 'EUR');

            return [
                'labels' => $labels,
                'data' => $series[$primaryCurrency] ?? array_fill(0, 6, 0),
                'currency' => $primaryCurrency,
                'series' => $series,
                'source' => $source,
            ];
        });
    }
}
