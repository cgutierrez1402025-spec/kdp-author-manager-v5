<?php

namespace App\Filament\Admin\Resources\Narrators\Pages;

use App\Filament\Admin\Resources\Narrators\NarratorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNarrator extends EditRecord
{
    protected static string $resource = NarratorResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
