<?php

namespace App\Filament\Admin\Resources\BookPromotions\Widgets;

use App\Models\BookPromotion;
use Filament\Widgets\Widget;

class PromotionRoiDashboard extends Widget
{
    protected static string $view = 'filament.widgets.promotion-roi-dashboard';  // ✅ Corregido: static

    public ?BookPromotion $record = null;

    protected function getViewData(): array
    {
        if (! $this->record) {
            return [
                'record' => null,
                'totalCosts' => 0,
                'totalRevenue' => 0,
                'roi' => 0,
                'roiPercentage' => 0,
                'dailyResults' => collect(),
                'chartData' => [],
            ];
        }

        $dailyResults = $this->record->dailyResults()
            ->select(['date', 'net_royalties', 'paid_units', 'free_units_promo'])
            ->orderBy('date')
            ->get();

        $totalCosts = $this->record->total_costs;
        $totalRevenue = $this->record->total_revenue;
        $roi = $this->record->calculateROI();

        return [
            'record' => $this->record,
            'totalCosts' => $totalCosts,
            'totalRevenue' => $totalRevenue,
            'roi' => $roi,
            'roiPercentage' => $totalCosts > 0 ? round((($roi / $totalCosts) * 100), 2) : 0,
            'dailyResults' => $dailyResults,
            'chartData' => $dailyResults->map(fn ($r) => [
                'date' => $r->date->format('Y-m-d'),
                'net_royalties' => (float) $r->net_royalties,
                'paid_units' => $r->paid_units,
            ])->values()->all(),
        ];
    }
}
