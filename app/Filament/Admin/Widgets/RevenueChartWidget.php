<?php

namespace App\Filament\Admin\Widgets;

use App\Models\RoyaltyEntry;
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
            $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

            $entries = RoyaltyEntry::selectRaw('year, month, SUM(total_royalty) as total')
                ->when(
                    ! $user->canViewAllAuthorData(),
                    fn ($query) => $query->whereHas(
                        'publication.work',
                        fn ($workQuery) => $workQuery->where('user_id', $user->getKey()),
                    ),
                )
                ->where(function ($query) {
                    $query->where('year', '>', now()->subYear()->year)
                        ->orWhere(function ($q) {
                            $q->where('year', now()->subYear()->year)
                                ->where('month', '>=', now()->subMonths(6)->month);
                        });
                })
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(fn ($e) => "{$e->year}-{$e->month}");

            $labels = [];
            $data = [];

            foreach ($months as $date) {
                $key = $date->format('Y-n');
                $labels[] = ucfirst($date->locale('es')->translatedFormat('M Y'));
                $data[] = (float) ($entries->get($key)?->total ?? 0);
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });
    }
}
