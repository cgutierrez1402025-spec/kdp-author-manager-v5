<?php

namespace App\Services;

use App\Models\BookPromotion;
use App\Models\User;
use Illuminate\Support\Collection;

class PromotionAnalyticsService
{
    public function calculateTotalCost(int $promotionId): float
    {
        return (float) BookPromotion::find($promotionId)?->costs()->sum('amount') ?? 0;
    }

    public function calculateTotalRevenue(int $promotionId): float
    {
        return (float) BookPromotion::find($promotionId)?->dailyResults()->sum('net_royalties') ?? 0;
    }

    public function calculateROI(int $promotionId): float
    {
        $costs = $this->calculateTotalCost($promotionId);
        $revenue = $this->calculateTotalRevenue($promotionId);

        if ($costs == 0) {
            return 0;
        }

        $profit = $revenue - $costs;

        return round(($profit / $costs) * 100, 2);
    }

    public function getDailyPerformance(int $promotionId): array
    {
        $promotion = BookPromotion::find($promotionId);

        if (! $promotion) {
            return [];
        }

        return $promotion->dailyResults()
            ->select(['date', 'paid_units', 'free_units_promo', 'kenp_pages_read', 'net_royalties'])
            ->orderBy('date')
            ->get()
            ->map(fn ($result) => [
                'date' => $result->date->format('Y-m-d'),
                'units' => $result->paid_units + $result->free_units_promo + $result->free_units_price_match,
                'kenp_pages' => $result->kenp_pages_read,
                'royalties' => (float) $result->net_royalties,
            ])
            ->values()
            ->all();
    }

    public function getPromotionStats(int $promotionId): array
    {
        $promotion = BookPromotion::with(['dailyResults', 'costs'])->find($promotionId);

        if (! $promotion) {
            return [
                'success' => false,
                'error' => 'Promotion not found',
            ];
        }

        return [
            'success' => true,
            'total_cost' => $this->calculateTotalCost($promotionId),
            'total_revenue' => $this->calculateTotalRevenue($promotionId),
            'roi_percentage' => $this->calculateROI($promotionId),
            'total_units' => $promotion->dailyResults->sum('paid_units')
                + $promotion->dailyResults->sum('free_units_promo')
                + $promotion->dailyResults->sum('free_units_price_match'),
            'total_kenp_pages' => $promotion->dailyResults->sum('kenp_pages_read'),
            'daily_performance' => $this->getDailyPerformance($promotionId),
        ];
    }

    public function getAllActivePromotionsWithROI(?User $user = null): Collection
    {
        $query = BookPromotion::active()
            ->with(['publication.work', 'marketplace'])
            ->when(
                $user && ! $user->canViewAllAuthorData(),
                fn ($query) => $query->whereHas(
                    'publication.work',
                    fn ($workQuery) => $workQuery->where('user_id', $user->getKey()),
                ),
            );

        return $query->get()
            ->map(fn ($promotion) => [
                'id' => $promotion->id,
                'promotion_name' => $promotion->promotion_name,
                'work_title' => $promotion->publication->work->title_public ?? 'N/A',
                'marketplace' => $promotion->marketplace->name ?? 'N/A',
                'roi' => $this->calculateROI($promotion->id),
                'total_cost' => $this->calculateTotalCost($promotion->id),
                'total_revenue' => $this->calculateTotalRevenue($promotion->id),
                'end_date' => $promotion->end_date,
            ]);
    }
}
