<?php

namespace App\Filament\Admin\Resources\Checklists\Pages;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use App\Services\EditorialIntegrityService;
use Filament\Resources\Pages\EditRecord;

class EditChecklist extends EditRecord
{
    protected static string $resource = ChecklistResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(EditorialIntegrityService::class)->validateChecklist($data, auth()->user());
    }
}
