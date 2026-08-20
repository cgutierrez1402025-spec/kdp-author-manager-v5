<?php

namespace App\Filament\Admin\Resources\KdpPayments\Pages;

use App\Filament\Admin\Resources\KdpPayments\KdpPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKdpPayment extends CreateRecord
{
    protected static string $resource = KdpPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= auth()->id();

        return $data;
    }
}
