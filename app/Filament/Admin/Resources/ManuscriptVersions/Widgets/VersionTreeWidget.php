<?php

namespace App\Filament\Admin\Resources\ManuscriptVersions\Widgets;

use App\Models\ManuscriptVersion;
use Filament\Widgets\Widget;

class VersionTreeWidget extends Widget
{
    protected static string $view = 'filament.widgets.version-tree';

    public ?ManuscriptVersion $record = null;

    protected function getViewData(): array
    {
        $tree = $this->buildVersionTree($this->record);

        return [
            'versions' => $tree === [] ? [] : [$tree],
        ];
    }

    protected function buildVersionTree(?ManuscriptVersion $version, int $level = 0): array
    {
        if (! $version) {
            return [];
        }

        $children = ManuscriptVersion::where('parent_version_id', $version->id)
            ->orderBy('version_number')
            ->get();

        $tree = [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'status' => $version->status,
            'is_final' => $version->is_final,
            'is_published' => $version->is_published,
            'created_at' => $version->created_at,
            'level' => $level,
            'children' => [],
        ];

        foreach ($children as $child) {
            $tree['children'][] = $this->buildVersionTree($child, $level + 1);
        }

        return $tree;
    }
}
