<?php

namespace App\Filament\Admin\Resources\AiTasks\Pages;

use App\Filament\Admin\Resources\AiTasks\AiTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiTasks extends ListRecords
{
    protected static string $resource = AiTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
