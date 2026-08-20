<?php

namespace App\Filament\Admin\Resources\BookEvents\Pages;

use App\Filament\Admin\Resources\BookEvents\BookEventResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListBookEvents extends ListRecords
{
    protected static string $resource = BookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo evento')];
    }
}
