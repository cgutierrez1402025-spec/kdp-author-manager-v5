<?php

namespace App\Filament\Admin\Resources\Narrators\Pages;

use App\Filament\Admin\Resources\Narrators\NarratorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNarrators extends ListRecords
{
    protected static string $resource = NarratorResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
