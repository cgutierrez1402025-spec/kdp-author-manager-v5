<?php

namespace App\Exports;

use App\Models\BookPromotion;
use Illuminate\Support\Collection;

class PromotionsROIReport
{
    public function getPromotionsWithROI(): Collection
    {
        $promotions = BookPromotion::with(['publication.work', 'marketplace'])->get();

        return $promotions->map(function ($promotion) {
            $costs = $promotion->costs()->sum('amount');
            $revenue = $promotion->dailyResults()->sum('net_royalties');
            $roi = $costs > 0 ? (($revenue - $costs) / $costs) * 100 : 0;

            return [
                'promotion_name' => $promotion->promotion_name,
                'work_title' => $promotion->publication->work->title_public ?? 'N/A',
                'marketplace' => $promotion->marketplace->name ?? 'N/A',
                'start_date' => $promotion->start_date,
                'end_date' => $promotion->end_date,
                'total_cost' => $costs,
                'total_revenue' => $revenue,
                'roi_percentage' => round($roi, 2),
            ];
        });
    }
}
