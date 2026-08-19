<?php

namespace Tests\Feature;

use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Publication;
use App\Models\RoyaltyEntry;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use App\Services\RoyaltyImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoyaltyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_royalty_entry_without_duplicates(): void
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

        $row = [
            'publication_id' => $publication->id,
            'year' => 2025,
            'month' => 12,
            'paid_units' => 100,
            'free_units' => 25,
            'kenp_pages' => 5000,
            'royalty_ebook' => 70.00,
            'royalty_paperback' => 0,
            'royalty_hardcover' => 0,
            'royalty_kenp' => 35.00,
            'total_royalty' => 105.00,
            'currency' => 'USD',
        ];

        $service = app(RoyaltyImportService::class);
        $this->assertSame(1, $service->import([$row]));
        $this->assertSame(1, $service->import([$row]));

        $this->assertDatabaseHas('royalty_entries', [
            'publication_id' => $publication->id,
            'year' => 2025,
            'month' => 12,
        ]);

        $duplicate = RoyaltyEntry::where('publication_id', $publication->id)
            ->where('year', 2025)
            ->where('month', 12)
            ->count();

        $this->assertEquals(1, $duplicate, 'Should not allow duplicate entries for same publication/year/month');
    }

    public function test_import_is_atomic_when_a_row_is_invalid(): void
    {
        $this->expectException(ValidationException::class);

        try {
            app(RoyaltyImportService::class)->import([
                [
                    'publication_id' => 999999,
                    'year' => 2025,
                    'month' => 1,
                    'paid_units' => 1,
                    'free_units' => 0,
                    'kenp_pages' => 0,
                    'royalty_ebook' => 1,
                    'royalty_paperback' => 0,
                    'royalty_hardcover' => 0,
                    'royalty_kenp' => 0,
                    'total_royalty' => 1,
                    'currency' => 'EUR',
                ],
            ]);
        } finally {
            $this->assertDatabaseCount('royalty_entries', 0);
        }
    }
}
