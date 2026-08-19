<?php

namespace App\Filament\Admin\Resources\BookEvents\Pages;

use App\Filament\Admin\Resources\BookEvents\BookEventResource;
use Filament\Resources\Pages\EditRecord;

class EditBookEvent extends EditRecord
{
    protected static string $resource = BookEventResource::class;
}
