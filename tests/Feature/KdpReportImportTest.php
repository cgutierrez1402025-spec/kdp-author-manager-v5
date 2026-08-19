<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\KdpCatalogItem;
use App\Models\KdpReportRow;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Publication;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use App\Services\Kdp\KdpReportImportService;
use App\Services\Kdp\XlsxTableReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class KdpReportImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_a_kdp_csv_and_links_the_publication(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $publication = $this->publication($user, 'B012345678', 'ebook');
        $this->actingAs($user);

        $csv = implode("\n", [
            'Title,Author,ASIN,Marketplace,Currency,Format,Units Sold,Units Refunded,Net Units Sold,KENP Read,Total Earnings',
            'Mi libro,Autora,B012345678,Amazon.es,EUR,eBook,12,2,10,1500,"25,50"',
        ]);
        Storage::disk('local')->put('private/kdp-imports/report.csv', $csv);

        $batch = ImportBatch::create([
            'user_id' => $user->id,
            'import_type' => 'prior_royalties',
            'report_period' => '2026-07-01',
            'source_system' => 'amazon_kdp',
            'original_file_path' => 'private/kdp-imports/report.csv',
            'original_file_name' => 'report.csv',
            'file_hash' => hash('sha256', $csv),
            'detected_format' => 'csv',
            'status' => 'pending',
            'processed_by_ai' => false,
        ]);

        app(KdpReportImportService::class)->import($batch);

        $row = KdpReportRow::firstOrFail();
        $this->assertSame($publication->id, $row->publication_id);
        $this->assertSame(10, $row->net_units_sold);
        $this->assertSame('25.5000', $row->total_earnings);
        $this->assertSame('EUR', $row->currency);
        $this->assertDatabaseHas('kdp_catalog_items', [
            'user_id' => $user->id,
            'asin' => 'B012345678',
            'publication_id' => $publication->id,
            'review_status' => 'linked',
        ]);
        $this->assertSame('completed', $batch->refresh()->status);
        $this->assertSame(1, $batch->imported_rows);
    }

    public function test_reprocessing_the_batch_is_idempotent(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $csv = "Payment Number,Marketplace,Currency,Payment Status,Payment Date,Payment Amount\nP-123,Amazon.es,EUR,Paid,2026-07-29,100.00";
        Storage::disk('local')->put('private/kdp-imports/payment.csv', $csv);

        $batch = ImportBatch::create([
            'user_id' => $user->id,
            'import_type' => 'payments',
            'report_period' => '2026-05-01',
            'source_system' => 'amazon_kdp',
            'original_file_path' => 'private/kdp-imports/payment.csv',
            'original_file_name' => 'payment.csv',
            'file_hash' => hash('sha256', $csv),
            'detected_format' => 'csv',
            'status' => 'pending',
            'processed_by_ai' => false,
        ]);

        $service = app(KdpReportImportService::class);
        $service->import($batch);
        $service->import($batch);

        $this->assertDatabaseCount('kdp_report_rows', 1);
        $this->assertSame(1, $batch->refresh()->skipped_rows);
    }

    public function test_reads_a_real_xlsx_workbook(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'kdp-xlsx-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Title', 'ASIN', 'Total Earnings']));
        $writer->addRow(Row::fromValues(['Libro XLSX', 'B012345678', 12.5]));
        $writer->close();

        $tables = app(XlsxTableReader::class)->read($path);

        $this->assertSame('Title', $tables['Sheet1'][0][0]);
        $this->assertSame('Libro XLSX', $tables['Sheet1'][1][0]);
        $this->assertSame('12.5', $tables['Sheet1'][1][2]);
    }

    public function test_preserves_an_unknown_title_in_the_detected_catalog(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $csv = "Title,Author,ASIN,Marketplace,Currency,Format,Net Units Sold,Total Earnings\nObra desconocida,Autora Nueva,B099999999,Amazon.es,EUR,eBook,3,4.20";
        Storage::disk('local')->put('private/kdp-imports/unknown.csv', $csv);
        $batch = ImportBatch::create([
            'user_id' => $user->id, 'import_type' => 'prior_royalties', 'report_period' => '2026-07-01',
            'source_system' => 'amazon_kdp', 'original_file_path' => 'private/kdp-imports/unknown.csv',
            'original_file_name' => 'unknown.csv', 'file_hash' => hash('sha256', $csv),
            'detected_format' => 'csv', 'status' => 'pending', 'processed_by_ai' => false,
        ]);

        app(KdpReportImportService::class)->import($batch);

        $item = KdpCatalogItem::firstOrFail();
        $this->assertSame('Obra desconocida', $item->title);
        $this->assertSame('pending', $item->review_status);
        $this->assertNull($item->work_id);
        $this->assertSame($item->id, KdpReportRow::firstOrFail()->kdp_catalog_item_id);
    }

    private function publication(User $user, string $asin, string $format): Publication
    {
        $work = Work::factory()->create(['user_id' => $user->id]);
        $language = WorkLanguage::create([
            'work_id' => $work->id,
            'language_code' => $work->original_language,
            'translation_status' => 'original',
        ]);
        $version = ManuscriptVersion::create([
            'work_id' => $work->id,
            'work_language_id' => $language->id,
            'version_number' => '1',
            'status' => 'final',
            'created_by' => $user->id,
        ]);
        $platform = Platform::factory()->create(['name' => 'Amazon KDP']);
        $marketplace = Marketplace::factory()->create(['platform_id' => $platform->id]);

        return Publication::create([
            'work_id' => $work->id,
            'work_language_id' => $language->id,
            'manuscript_version_id' => $version->id,
            'platform_id' => $platform->id,
            'marketplace_id' => $marketplace->id,
            'format' => $format,
            'asin' => $asin,
            'status' => 'published',
        ]);
    }
}
