<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function getActivities(): array
    {
        $user = auth()->user();
        $cacheKey = $user->dashboardCacheNamespace().':recent-activity';

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            return Activity::with(['causer', 'subject'])
                ->when(
                    ! $user->canViewAllAuthorData(),
                    fn ($query) => $query
                        ->where('causer_type', User::class)
                        ->where('causer_id', $user->getKey()),
                )
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (Activity $log) => [
                    'user' => $log->causer->name ?? 'Sistema',
                    'action' => match ($log->event) {
                        'created' => 'creó',
                        'updated' => 'actualizó',
                        'deleted' => 'eliminó',
                        default => $log->event ?? 'registró',
                    },
                    'description' => $this->activityDescription($log),
                    'created_at' => $log->created_at->locale('es')->diffForHumans(),
                ])
                ->values()
                ->all();
        });
    }

    private function activityDescription(Activity $activity): string
    {
        $subject = $activity->subject;

        if ($subject?->title_public || $subject?->title || $subject?->name) {
            return (string) ($subject->title_public ?? $subject->title ?? $subject->name);
        }

        $model = class_basename((string) $activity->subject_type);
        $labels = [
            'WorkLanguage' => 'idioma de obra',
            'ManuscriptVersion' => 'versión de manuscrito',
            'Publication' => 'publicación',
            'KdpMetadata' => 'metadatos KDP',
            'RoyaltyEntry' => 'registro de regalías',
            'BookPromotion' => 'promoción',
            'PromotionCost' => 'coste de promoción',
            'PromotionDailyResult' => 'resultado diario',
        ];

        return ($labels[$model] ?? str($model)->headline()->lower()->toString())
            .' #'.($activity->subject_id ?? '—');
    }
}
