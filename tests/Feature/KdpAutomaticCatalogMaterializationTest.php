<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\KdpCatalogItem;
use App\Models\KdpReportRow;
use App\Models\User;
use App\Models\Work;
use App\Services\Kdp\KdpCatalogMaterializer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KdpAutomaticCatalogMaterializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_formats_with_same_title_and_author_share_one_work(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'author@example.com')->firstOrFail();
        $batch = ImportBatch::firstOrFail();

        foreach ([['B0AUTOEB01', 'ebook'], ['B0AUTOPB01', 'paperback']] as $index => [$asin, $format]) {
            $item = KdpCatalogItem::create([
                'user_id' => $user->id, 'identity_key' => hash('sha256', $asin), 'asin' => $asin, 'title' => 'Una misma obra',
                'author' => 'Autora Común', 'format' => $format, 'marketplaces' => ['Amazon.es'], 'review_status' => 'pending',
                'first_seen_at' => now(), 'last_seen_at' => now(),
            ]);
            KdpReportRow::create([
                'user_id' => $user->id, 'import_batch_id' => $batch->id, 'kdp_catalog_item_id' => $item->id,
                'row_fingerprint' => hash('sha256', 'auto-row-'.$index), 'report_type' => 'prior_royalties',
                'report_period' => '2026-07-01', 'title' => $item->title, 'author' => $item->author, 'asin' => $asin,
                'format' => $format, 'marketplace' => 'Amazon.es', 'currency' => 'EUR', 'row_kind' => 'royalty',
                'raw_data' => [], 'normalized_data' => [],
            ]);
            app(KdpCatalogMaterializer::class)->materialize($item);
        }

        $works = Work::where('title_public', 'Una misma obra')->get();
        $this->assertCount(1, $works);
        $this->assertCount(2, $works->first()->publications);
        $this->assertEqualsCanonicalizing(['ebook', 'paperback'], $works->first()->publications->pluck('format')->all());
    }

    public function test_materializing_same_catalog_twice_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $item = KdpCatalogItem::firstOrFail();
        $service = app(KdpCatalogMaterializer::class);
        $work = $service->materialize($item);
        $publicationCount = $work->publications()->count();

        $service->materialize($item);

        $this->assertSame($publicationCount, $work->publications()->count());
    }

    public function test_items_without_a_reported_title_do_not_collapse_into_one_work(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'author@example.com')->firstOrFail();
        $service = app(KdpCatalogMaterializer::class);

        foreach (['B0UNTITLED1', 'B0UNTITLED2'] as $asin) {
            $item = KdpCatalogItem::create([
                'user_id' => $user->id, 'identity_key' => hash('sha256', $asin), 'asin' => $asin,
                'title' => 'Título no informado', 'author' => null, 'format' => 'ebook',
                'marketplaces' => [], 'review_status' => 'pending', 'first_seen_at' => now(), 'last_seen_at' => now(),
            ]);

            $service->materialize($item);
        }

        $this->assertDatabaseHas('works', ['title_public' => 'Edición KDP B0UNTITLED1']);
        $this->assertDatabaseHas('works', ['title_public' => 'Edición KDP B0UNTITLED2']);
        $this->assertSame(2, Work::where('status', 'catalog_review')->count());
    }
}
