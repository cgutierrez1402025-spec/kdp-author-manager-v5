<?php

namespace App\Filament\Admin\Resources\Checklists\Pages;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChecklist extends CreateRecord
{
    protected static string $resource = ChecklistResource::class;
}
