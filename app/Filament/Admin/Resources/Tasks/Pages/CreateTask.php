<?php

namespace App\Filament\Admin\Resources\Tasks\Pages;

use App\Filament\Admin\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function afterFill(): void
    {
        $workId = request()->integer('work_id');

        if ($workId && TaskResource::getEloquentQuery()->whereKey($workId)->exists()) {
            $this->form->fill([...$this->form->getState(), 'work_id' => $workId]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
