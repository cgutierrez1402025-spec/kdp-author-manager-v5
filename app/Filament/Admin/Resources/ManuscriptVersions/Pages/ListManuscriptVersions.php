<?php

namespace App\Filament\Admin\Resources\ManuscriptVersions\Pages;

use App\Filament\Admin\Resources\ManuscriptVersions\ManuscriptVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManuscriptVersions extends ListRecords
{
    protected static string $resource = ManuscriptVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
