<?php

use App\Models\Marketplace;
use App\Models\Platform;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $platforms = [
            ['name' => 'Amazon KDP', 'description' => 'Amazon Kindle Direct Publishing'],
            ['name' => 'Smashwords', 'description' => 'Smashwords Publishing Platform'],
            ['name' => 'Google Play Books', 'description' => 'Google Play Books Publisher'],
            ['name' => 'Apple Books', 'description' => 'Apple Books for Authors'],
            ['name' => 'Kobo Writing Life', 'description' => 'Kobo Writing Life Platform'],
            ['name' => 'Draft2Digital', 'description' => 'Draft2Digital Publishing'],
        ];

        foreach ($platforms as $platform) {
            Platform::updateOrCreate(
                ['name' => $platform['name']],
                $platform
            );
        }

        $kdp = Platform::where('name', 'Amazon KDP')->first();

        $marketplaces = [
            ['platform_id' => $kdp->id, 'code' => 'amazon_com', 'name' => 'Amazon.com', 'currency' => 'USD'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_es', 'name' => 'Amazon.es', 'currency' => 'EUR'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_co_uk', 'name' => 'Amazon.co.uk', 'currency' => 'GBP'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_de', 'name' => 'Amazon.de', 'currency' => 'EUR'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_fr', 'name' => 'Amazon.fr', 'currency' => 'EUR'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_it', 'name' => 'Amazon.it', 'currency' => 'EUR'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_jp', 'name' => 'Amazon.co.jp', 'currency' => 'JPY'],
            ['platform_id' => $kdp->id, 'code' => 'amazon_br', 'name' => 'Amazon.com.br', 'currency' => 'BRL'],
        ];

        foreach ($marketplaces as $marketplace) {
            Marketplace::updateOrCreate(
                ['code' => $marketplace['code']],
                $marketplace
            );
        }
    }

    public function down(): void
    {
        Marketplace::whereIn('code', [
            'amazon_com', 'amazon_es', 'amazon_co_uk', 'amazon_de',
            'amazon_fr', 'amazon_it', 'amazon_jp', 'amazon_br',
        ])->delete();

        Platform::whereIn('name', [
            'Amazon KDP', 'Smashwords', 'Google Play Books',
            'Apple Books', 'Kobo Writing Life', 'Draft2Digital',
        ])->delete();
    }
};
