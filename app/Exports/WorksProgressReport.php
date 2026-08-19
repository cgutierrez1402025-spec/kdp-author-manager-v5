<?php

namespace App\Exports;

use App\Models\Work;
use Illuminate\Support\Collection;

class WorksProgressReport
{
    public function getWorkProgress(): Collection
    {
        $works = Work::with(['checklists.items', 'manuscriptVersions'])->get();

        return $works->map(function ($work) {
            $checklistProgress = 0;
            $checklists = $work->checklists;

            if ($checklists->count() > 0) {
                $totalItems = $checklists->sum(fn ($c) => $c->items->count());
                $checkedItems = $checklists->sum(fn ($c) => $c->items->where('is_checked', true)->count());
                $checklistProgress = $totalItems > 0 ? ($checkedItems / $totalItems) * 100 : 0;
            }

            $manuscriptProgress = 0;
            $versions = $work->manuscriptVersions;

            if ($versions->count() > 0) {
                $finalVersions = $versions->where('is_final', true)->count();
                $publishedVersions = $versions->where('is_published', true)->count();
                $manuscriptProgress = $publishedVersions > 0 ? 100 : ($finalVersions > 0 ? 75 : 25);
            }

            $overallProgress = ($checklistProgress + $manuscriptProgress) / 2;

            return [
                'title' => $work->title_public,
                'status' => $work->status,
                'checklist_progress' => round($checklistProgress, 1),
                'manuscript_progress' => $manuscriptProgress,
                'overall_progress' => round($overallProgress, 1),
                'final_draft_count' => $versions->where('is_final', true)->count(),
                'published_count' => $versions->where('is_published', true)->count(),
            ];
        });
    }
}
