<?php

namespace App\Filament\Admin\Resources\SourceUsages\Pages;

use App\Filament\Admin\Resources\SourceUsages\SourceUsageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSourceUsages extends ListRecords
{
    protected static string $resource = SourceUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
