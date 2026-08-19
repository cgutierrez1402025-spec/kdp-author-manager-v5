<?php

namespace App\Filament\Admin\Resources\Checklists\Pages;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use Filament\Resources\Pages\ListRecords;

class ListChecklists extends ListRecords
{
    protected static string $resource = ChecklistResource::class;
}
