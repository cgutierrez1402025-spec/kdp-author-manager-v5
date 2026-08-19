<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\KdpCatalogItem;
use App\Models\KdpReportRow;
use App\Models\Marketplace;
use App\Models\User;
use App\Services\Kdp\KdpCatalogPromotionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class KdpCatalogPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_pending_catalog_item_can_create_a_work_without_fake_manuscript(): void
    {
        $author = User::where('email', 'author@example.com')->firstOrFail();
        $this->actingAs($author);
        $item = KdpCatalogItem::create([
            'user_id' => $author->id, 'identity_key' => hash('sha256', 'new-catalog-work'), 'asin' => 'B0NEWBOOK1',
            'title' => 'Obra importada', 'author' => 'Autora KDP', 'format' => 'ebook', 'marketplaces' => ['Amazon.es'],
            'review_status' => 'pending', 'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);
        $row = KdpReportRow::create([
            'user_id' => $author->id, 'import_batch_id' => ImportBatch::firstOrFail()->id,
            'kdp_catalog_item_id' => $item->id, 'row_fingerprint' => hash('sha256', 'new-catalog-row'),
            'report_type' => 'prior_royalties', 'report_period' => '2026-07-01', 'title' => $item->title,
            'author' => $item->author, 'asin' => $item->asin, 'format' => 'ebook', 'row_kind' => 'royalty',
            'raw_data' => [], 'normalized_data' => [],
        ]);
        $marketplace = Marketplace::where('code', 'amazon.es')->firstOrFail();

        $work = app(KdpCatalogPromotionService::class)->createWork($item, [
            'title' => $item->title, 'author_name' => $item->author, 'language_code' => 'es',
            'work_type' => 'novel', 'marketplace_id' => $marketplace->id, 'format' => 'ebook',
        ], $author);

        $this->assertSame('catalog_review', $work->status);
        $this->assertDatabaseHas('work_languages', ['work_id' => $work->id, 'language_code' => 'es']);
        $this->assertDatabaseHas('publications', ['work_id' => $work->id, 'asin' => 'B0NEWBOOK1', 'manuscript_version_id' => null, 'status' => 'catalog_review']);
        $this->assertSame($work->id, $item->fresh()->work_id);
        $this->assertNotNull($row->fresh()->publication_id);
    }

    public function test_author_cannot_promote_another_authors_catalog_item(): void
    {
        $author = User::where('email', 'author@example.com')->firstOrFail();
        $other = User::factory()->create();
        $this->actingAs($other);
        $item = KdpCatalogItem::where('user_id', $author->id)->firstOrFail();

        $this->expectException(HttpException::class);
        app(KdpCatalogPromotionService::class)->createWork($item, [], $other);
    }
}
