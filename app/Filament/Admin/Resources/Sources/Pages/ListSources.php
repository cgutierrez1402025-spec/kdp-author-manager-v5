<?php

namespace App\Filament\Admin\Resources\Sources\Pages;

use App\Filament\Admin\Resources\Sources\SourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSources extends ListRecords
{
    protected static string $resource = SourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
