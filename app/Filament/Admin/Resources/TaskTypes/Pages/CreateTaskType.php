<?php

namespace App\Filament\Admin\Resources\TaskTypes\Pages;

use App\Filament\Admin\Resources\TaskTypes\TaskTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaskType extends CreateRecord
{
    protected static string $resource = TaskTypeResource::class;
}
