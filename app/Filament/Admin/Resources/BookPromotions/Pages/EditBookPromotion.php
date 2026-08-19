<?php

namespace App\Filament\Admin\Resources\BookPromotions\Pages;

use App\Filament\Admin\Resources\BookPromotions\BookPromotionResource;
use App\Filament\Admin\Resources\BookPromotions\Widgets\PromotionRoiDashboard;
use Filament\Resources\Pages\EditRecord;

class EditBookPromotion extends EditRecord
{
    protected static string $resource = BookPromotionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PromotionRoiDashboard::make([
                'record' => $this->record,
            ]),
        ];
    }
}
