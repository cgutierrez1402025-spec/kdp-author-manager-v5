<?php

namespace App\Filament\Admin\Resources\Checklists\Pages;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use Filament\Resources\Pages\EditRecord;

class EditChecklist extends EditRecord
{
    protected static string $resource = ChecklistResource::class;
}
