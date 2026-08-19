<?php

namespace App\Filament\Admin\Resources\BookEvents\Pages;

use App\Filament\Admin\Resources\BookEvents\BookEventResource;
use Filament\Resources\Pages\ListRecords;

class ListBookEvents extends ListRecords
{
    protected static string $resource = BookEventResource::class;
}
