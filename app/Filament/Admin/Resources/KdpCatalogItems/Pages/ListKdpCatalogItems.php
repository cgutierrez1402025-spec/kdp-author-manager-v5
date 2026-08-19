<?php

namespace App\Filament\Admin\Resources\KdpCatalogItems\Pages;

use App\Filament\Admin\Resources\KdpCatalogItems\KdpCatalogItemResource;
use Filament\Resources\Pages\ListRecords;

class ListKdpCatalogItems extends ListRecords
{
    protected static string $resource = KdpCatalogItemResource::class;
}
