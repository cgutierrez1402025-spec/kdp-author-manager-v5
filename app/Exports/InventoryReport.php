<?php

namespace App\Exports;

use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

class InventoryReport
{
    public function getInventoryTotals(): array
    {
        $locations = StockLocation::with('user')->get();

        $results = [];

        foreach ($locations as $location) {
            $incoming = StockMovement::where('to_location_id', $location->id)
                ->sum('quantity');

            $outgoing = StockMovement::where('from_location_id', $location->id)
                ->sum('quantity');

            $results[] = [
                'location' => $location->name,
                'type' => $location->type,
                'stock' => $incoming - $outgoing,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
            ];
        }

        return $results;
    }

    public function getLowStockAlerts(int $threshold = 10): Collection
    {
        return StockLocation::with(['stockMovements'])
            ->get()
            ->map(function ($location) use ($threshold) {
                $stock = $location->stockMovements->sum('quantity');

                return [
                    'location' => $location->name,
                    'stock' => $stock,
                    'below_threshold' => $stock < $threshold,
                ];
            })
            ->filter(fn ($item) => $item['below_threshold']);
    }
}
