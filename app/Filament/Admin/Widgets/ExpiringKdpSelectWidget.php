<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KdpSelectPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ExpiringKdpSelectWidget extends Widget
{
    protected static string $view = 'filament.widgets.expiring-kdp-select';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function getExpiringPeriods(): array
    {
        $user = auth()->user();
        $cacheKey = $user->dashboardCacheNamespace().':expiring-kdp';

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            return KdpSelectPeriod::where('status', 'active')
                ->when(
                    ! $user->canViewAllAuthorData(),
                    fn ($query) => $query->whereHas(
                        'publication.work',
                        fn ($workQuery) => $workQuery->where('user_id', $user->getKey()),
                    ),
                )
                ->whereDate('end_date', '<=', now()->addDays(30))
                ->whereDate('end_date', '>=', now())
                ->with('publication.work')
                ->orderBy('end_date')
                ->limit(10)
                ->get()
                ->map(fn (KdpSelectPeriod $period) => [
                    'work_title' => $period->publication->work->title_public ?? 'N/A',
                    'end_date' => $period->end_date->format('d/m/Y'),
                    'remaining_days' => max(0, (int) ceil(now()->diffInDays($period->end_date, false))),
                    'free_days_remaining' => $period->getRemainingFreeDays(),
                ])
                ->values()
                ->all();
        });
    }
}
