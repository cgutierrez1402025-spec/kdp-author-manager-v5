<?php

namespace App\Filament\Admin\Resources\BookEvents\Pages;

use App\Filament\Admin\Resources\BookEvents\BookEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookEvents extends ListRecords
{
    protected static string $resource = BookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo evento')];
    }
}
