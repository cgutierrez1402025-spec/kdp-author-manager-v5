<?php

namespace App\Filament\Admin\Resources\BookPromotions\Pages;

use App\Filament\Admin\Resources\BookPromotions\BookPromotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookPromotions extends ListRecords
{
    protected static string $resource = BookPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
