<?php

namespace App\Filament\Admin\Resources\EventBooks\Pages;

use App\Filament\Admin\Resources\EventBooks\EventBookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventBook extends CreateRecord
{
    protected static string $resource = EventBookResource::class;
}
