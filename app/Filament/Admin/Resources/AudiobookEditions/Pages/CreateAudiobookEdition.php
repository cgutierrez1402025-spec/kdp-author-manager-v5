<?php

namespace App\Filament\Admin\Resources\AudiobookEditions\Pages;

use App\Filament\Admin\Resources\AudiobookEditions\AudiobookEditionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAudiobookEdition extends CreateRecord
{
    protected static string $resource = AudiobookEditionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
