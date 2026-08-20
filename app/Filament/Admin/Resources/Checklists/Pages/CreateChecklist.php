<?php

namespace App\Filament\Admin\Resources\Checklists\Pages;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Filament\Admin\Resources\Works\WorkResource;
use App\Services\EditorialIntegrityService;
use Filament\Resources\Pages\CreateRecord;

class CreateChecklist extends CreateRecord
{
    protected static string $resource = ChecklistResource::class;

    protected function afterFill(): void
    {
        $workId = request()->integer('work_id');
        $publicationId = request()->integer('publication_id');

        if ($workId <= 0 || ! WorkResource::getEloquentQuery()->whereKey($workId)->exists()) {
            return;
        }

        $state = [...$this->form->getRawState(), 'work_id' => $workId];

        if ($publicationId > 0 && PublicationResource::getEloquentQuery()
            ->whereKey($publicationId)
            ->where('work_id', $workId)
            ->exists()) {
            $state['publication_id'] = $publicationId;
        }

        $this->form->fill($state);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(EditorialIntegrityService::class)->validateChecklist($data, auth()->user());
    }
}
