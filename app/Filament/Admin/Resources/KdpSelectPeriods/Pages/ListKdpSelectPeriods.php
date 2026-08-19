<?php

namespace App\Filament\Admin\Resources\KdpSelectPeriods\Pages;

use App\Filament\Admin\Resources\KdpSelectPeriods\KdpSelectPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKdpSelectPeriods extends ListRecords
{
    protected static string $resource = KdpSelectPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
