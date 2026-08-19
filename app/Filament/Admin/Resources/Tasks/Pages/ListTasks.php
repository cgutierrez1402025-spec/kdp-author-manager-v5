<?php

namespace App\Filament\Admin\Resources\Tasks\Pages;

use App\Filament\Admin\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;
}
