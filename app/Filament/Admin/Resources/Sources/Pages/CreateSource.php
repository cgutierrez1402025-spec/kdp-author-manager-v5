<?php

namespace App\Filament\Admin\Resources\Sources\Pages;

use App\Filament\Admin\Resources\Sources\SourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSource extends CreateRecord
{
    protected static string $resource = SourceResource::class;

    protected function afterFill(): void
    {
        $workId = request()->integer('work_id');

        if ($workId && SourceResource::getEloquentQuery()->whereKey($workId)->exists()) {
            $this->form->fill([...$this->form->getState(), 'work_id' => $workId]);
        }
    }
}
