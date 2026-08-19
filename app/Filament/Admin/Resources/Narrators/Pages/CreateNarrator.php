<?php

namespace App\Filament\Admin\Resources\Narrators\Pages;

use App\Filament\Admin\Resources\Narrators\NarratorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNarrator extends CreateRecord
{
    protected static string $resource = NarratorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
