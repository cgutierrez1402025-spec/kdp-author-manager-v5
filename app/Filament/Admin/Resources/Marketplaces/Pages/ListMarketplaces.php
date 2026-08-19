<?php

namespace App\Filament\Admin\Resources\Marketplaces\Pages;

use App\Filament\Admin\Resources\Marketplaces\MarketplaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketplaces extends ListRecords
{
    protected static string $resource = MarketplaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
