<?php

namespace App\Filament\Admin\Resources\AudiobookEditions\Pages;

use App\Filament\Admin\Resources\AudiobookEditions\AudiobookEditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAudiobookEdition extends EditRecord
{
    protected static string $resource = AudiobookEditionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
