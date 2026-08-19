<?php

namespace App\Filament\Admin\Resources\Works\Pages;

use App\Filament\Admin\Resources\Works\WorkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWork extends CreateRecord
{
    protected static string $resource = WorkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['title'] ??= $data['title_public'];

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->workLanguages()->firstOrCreate(
            ['language_code' => $this->record->original_language],
            ['translation_status' => 'original'],
        );
    }
}
