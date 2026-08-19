<?php

namespace App\Filament\Admin\Resources\Publications\Pages;

use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Filament\Admin\Resources\Works\WorkResource;
use App\Services\EditorialIntegrityService;
use Filament\Resources\Pages\CreateRecord;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function afterFill(): void
    {
        $workId = request()->integer('work_id');

        if ($workId > 0 && WorkResource::getEloquentQuery()->whereKey($workId)->exists()) {
            $this->form->fill([...$this->form->getRawState(), 'work_id' => $workId]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $kdpMetadata = $data['kdpMetadata'] ?? [];
        unset($data['kdpMetadata']);

        return app(EditorialIntegrityService::class)->validatePublication($data, auth()->user());
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        $metadata = array_filter(
            $data['kdpMetadata'] ?? [],
            static fn ($value): bool => $value !== null && $value !== '',
        );

        if ($metadata !== []) {
            $metadata['title'] ??= $this->record->work->title_public;
            $this->record->kdpMetadata()->create($metadata);
        }
    }
}
