<?php

namespace App\Filament\Admin\Resources\Tasks\Pages;

use App\Filament\Admin\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;
}
