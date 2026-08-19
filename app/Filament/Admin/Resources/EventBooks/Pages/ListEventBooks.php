<?php

namespace App\Filament\Admin\Resources\EventBooks\Pages;

use App\Filament\Admin\Resources\EventBooks\EventBookResource;
use Filament\Resources\Pages\ListRecords;

class ListEventBooks extends ListRecords
{
    protected static string $resource = EventBookResource::class;
}
