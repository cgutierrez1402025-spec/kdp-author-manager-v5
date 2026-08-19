<?php

namespace App\Filament\Admin\Resources\AiTools\Pages;

use App\Filament\Admin\Resources\AiTools\AiToolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiTools extends ListRecords
{
    protected static string $resource = AiToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
