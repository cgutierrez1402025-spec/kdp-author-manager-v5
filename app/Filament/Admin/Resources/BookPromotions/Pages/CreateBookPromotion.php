<?php

namespace App\Filament\Admin\Resources\BookPromotions\Pages;

use App\Filament\Admin\Resources\BookPromotions\BookPromotionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookPromotion extends CreateRecord
{
    protected static string $resource = BookPromotionResource::class;
}
