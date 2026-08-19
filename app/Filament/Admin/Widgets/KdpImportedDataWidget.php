<?php

namespace App\Filament\Admin\Widgets;

use App\Models\ImportBatch;
use App\Models\KdpReportRow;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class KdpImportedDataWidget extends Widget
{
    protected static string $view = 'filament.widgets.kdp-imported-data';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getDashboardData(): array
    {
        $rows = $this->rowsQuery();
        $latestBatch = ImportBatch::query()
            ->when(! auth()->user()->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->latest()
            ->first();

        $revenueByCurrency = (clone $rows)
            ->where('row_kind', 'royalty')
            ->whereNotNull('currency')
            ->selectRaw('currency, SUM(total_earnings) total')
            ->groupBy('currency')
            ->havingRaw('SUM(COALESCE(total_earnings, 0)) <> 0')
            ->orderByDesc('total')
            ->get()
            ->map(fn (KdpReportRow $row) => ['currency' => $row->currency, 'total' => (float) $row->total])
            ->all();

        $topTitles = (clone $rows)
            ->where('row_kind', 'royalty')
            ->whereNotNull('title')
            ->selectRaw('title, SUM(COALESCE(net_units_sold, 0)) units')
            ->groupBy('title')
            ->havingRaw('SUM(COALESCE(net_units_sold, 0)) <> 0')
            ->orderByDesc('units')
            ->get()
            ->map(fn (KdpReportRow $row) => ['title' => $row->title, 'units' => (int) $row->units])
            ->all();

        $kenpTitles = (clone $rows)
            ->where('row_kind', 'kenp')
            ->whereNotNull('title')
            ->selectRaw('title, SUM(COALESCE(kenp_read, 0)) pages')
            ->groupBy('title')
            ->havingRaw('SUM(COALESCE(kenp_read, 0)) <> 0')
            ->orderByDesc('pages')
            ->get()
            ->map(fn (KdpReportRow $row) => ['title' => $row->title, 'pages' => (int) $row->pages])
            ->all();

        $marketplaces = (clone $rows)
            ->where('row_kind', 'royalty')
            ->whereNotNull('marketplace')
            ->selectRaw('marketplace, SUM(COALESCE(net_units_sold, 0)) units')
            ->groupBy('marketplace')
            ->havingRaw('SUM(COALESCE(net_units_sold, 0)) <> 0')
            ->orderByDesc('units')
            ->get()
            ->map(fn (KdpReportRow $row) => ['marketplace' => $row->marketplace, 'units' => (int) $row->units])
            ->all();

        $dailyUnits = (clone $rows)
            ->where('row_kind', 'royalty')
            ->whereNotNull('transaction_date')
            ->selectRaw('transaction_date, SUM(COALESCE(net_units_sold, 0)) units')
            ->groupBy('transaction_date')
            ->havingRaw('SUM(COALESCE(net_units_sold, 0)) <> 0')
            ->orderByDesc('transaction_date')
            ->limit(14)
            ->get()
            ->sortBy('transaction_date')
            ->map(fn (KdpReportRow $row) => [
                'date' => $row->transaction_date->format('d/m'),
                'units' => (int) $row->units,
            ])
            ->values()
            ->all();

        return [
            'has_data' => (clone $rows)->exists(),
            'latest_batch' => $latestBatch,
            'total_units' => (int) (clone $rows)->where('row_kind', 'royalty')->sum('net_units_sold'),
            'total_kenp' => (int) (clone $rows)->where('row_kind', 'kenp')->sum('kenp_read'),
            'titles' => (int) (clone $rows)->whereNotNull('asin')->distinct()->count('asin'),
            'revenue_by_currency' => $revenueByCurrency,
            'top_titles' => $topTitles,
            'kenp_titles' => $kenpTitles,
            'marketplaces' => $marketplaces,
            'daily_units' => $dailyUnits,
        ];
    }

    private function rowsQuery(): Builder
    {
        return KdpReportRow::query()
            ->when(! auth()->user()->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }
}
