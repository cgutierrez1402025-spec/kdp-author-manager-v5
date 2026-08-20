<?php

namespace App\Filament\Admin\Resources\Sources\Pages;

use App\Filament\Admin\Resources\Sources\SourceResource;
use App\Filament\Admin\Resources\Works\WorkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSource extends CreateRecord
{
    protected static string $resource = SourceResource::class;

    protected function afterFill(): void
    {
        $workId = request()->integer('work_id');

        if ($workId > 0 && WorkResource::getEloquentQuery()->whereKey($workId)->exists()) {
            $this->form->fill([...$this->form->getRawState(), 'work_id' => $workId]);
        }
    }
}
