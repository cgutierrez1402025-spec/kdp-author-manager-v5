<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TopWorksByRevenueWidget extends Widget
{
    protected static string $view = 'filament.widgets.top-works-by-revenue';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function getWorks(): array
    {
        $query = DB::table('royalty_entries')
            ->join('publications', 'publications.id', '=', 'royalty_entries.publication_id')
            ->join('works', 'works.id', '=', 'publications.work_id')
            ->selectRaw('works.id, works.title_public, SUM(royalty_entries.total_royalty) AS total_revenue')
            ->groupBy('works.id', 'works.title_public')
            ->orderByDesc('total_revenue')
            ->limit(5);

        if (! auth()->user()?->canViewAllAuthorData()) {
            $query->where('works.user_id', auth()->id());
        }

        return $query->get()
            ->map(fn ($work) => [
                'title' => $work->title_public,
                'revenue' => (float) $work->total_revenue,
            ])
            ->all();
    }
}
