<?php

namespace App\Filament\Admin\Resources\TaskTypes\Pages;

use App\Filament\Admin\Resources\TaskTypes\TaskTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditTaskType extends EditRecord
{
    protected static string $resource = TaskTypeResource::class;
}
