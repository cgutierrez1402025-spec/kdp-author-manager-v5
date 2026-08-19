<?php

namespace App\Filament\Admin\Widgets;

use App\Services\PromotionAnalyticsService;
use Filament\Widgets\Widget;

class ActivePromotionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.active-promotions';

    protected static ?int $sort = 6;

    protected int $promotionsLimit = 4;

    protected int|string|array $columnSpan = 'full';

    public function getPromotionsProperty(): array
    {
        $service = app(PromotionAnalyticsService::class);

        return $service->getAllActivePromotionsWithROI(auth()->user())
            ->take($this->promotionsLimit)
            ->values()
            ->all();
    }
}
