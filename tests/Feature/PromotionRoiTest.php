<?php

namespace Tests\Feature;

use App\Models\BookPromotion;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\PromotionCost;
use App\Models\PromotionDailyResult;
use App\Models\Publication;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use App\Services\PromotionAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionRoiTest extends TestCase
{
    use RefreshDatabase;

    public function test_roi_calculation_is_correct(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create(['user_id' => $user->id]);
        $workLanguage = WorkLanguage::create([
            'work_id' => $work->id,
            'language_code' => $work->original_language,
            'translation_status' => 'original',
        ]);
        $manuscript = ManuscriptVersion::create([
            'work_id' => $work->id,
            'work_language_id' => $workLanguage->id,
            'version_number' => '1',
            'status' => 'final',
            'created_by' => $user->id,
        ]);
        $platform = Platform::factory()->create();
        $marketplace = Marketplace::factory()->create([
            'platform_id' => $platform->id,
        ]);
        $publication = Publication::create([
            'work_id' => $work->id,
            'work_language_id' => $workLanguage->id,
            'manuscript_version_id' => $manuscript->id,
            'platform_id' => $platform->id,
            'marketplace_id' => $marketplace->id,
            'format' => 'ebook',
            'status' => 'published',
        ]);

        $promotion = BookPromotion::create([
            'publication_id' => $publication->id,
            'promotion_type' => 'free',
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
            'normal_price' => 4.99,
            'promotional_price' => 0,
            'status' => 'completed',
        ]);

        PromotionCost::create([
            'book_promotion_id' => $promotion->id,
            'cost_type' => 'marketing',
            'amount' => 50.00,
            'currency' => 'EUR',
            'date' => now()->toDateString(),
        ]);

        PromotionDailyResult::create([
            'book_promotion_id' => $promotion->id,
            'date' => now()->subDays(3)->toDateString(),
            'paid_units' => 50,
            'free_units_promo' => 100,
            'gross_royalties' => 75.00,
            'net_royalties' => 75.00,
            'currency' => 'EUR',
        ]);

        $service = new PromotionAnalyticsService;

        $costs = $service->calculateTotalCost($promotion->id);
        $revenue = $service->calculateTotalRevenue($promotion->id);
        $roi = $service->calculateROI($promotion->id);

        $this->assertEquals(50.00, $costs);
        $this->assertEquals(75.00, $revenue);
        $this->assertEquals(50.0, round($roi));
        $this->assertSame(50.0, $promotion->fresh()->calculateROI());
    }

    public function test_roi_is_zero_when_there_are_no_costs(): void
    {
        $promotion = BookPromotion::factory()->create();

        PromotionDailyResult::factory()->create([
            'book_promotion_id' => $promotion->id,
            'gross_royalties' => 25,
            'net_royalties' => 20,
        ]);

        $this->assertSame(0.0, app(PromotionAnalyticsService::class)->calculateROI($promotion->id));
        $this->assertSame(0.0, $promotion->calculateROI());
    }
}
