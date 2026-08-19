<?php

namespace App\Filament\Admin\Resources\BookEvents\Pages;

use App\Filament\Admin\Resources\BookEvents\BookEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookEvent extends CreateRecord
{
    protected static string $resource = BookEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
