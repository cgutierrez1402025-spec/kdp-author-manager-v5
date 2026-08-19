<?php

namespace Tests\Feature;

use App\Models\KdpReportRow;
use App\Models\User;
use App\Services\Kdp\KdpBulkImportService;
use App\Services\Kdp\KdpReportTypeDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use ZipArchive;

class KdpBulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_report_type_and_period_from_headers_and_filename(): void
    {
        Storage::fake('local');
        $path = 'private/kdp-imports/payments-2026-07.csv';
        Storage::disk('local')->put($path, "Payment Number,Payment Date,Payment Amount,Payment Status\nP-1,2026-07-30,10.00,Paid");

        $result = app(KdpReportTypeDetector::class)->detect(Storage::disk('local')->path($path));

        $this->assertSame('payments', $result['type']);
        $this->assertSame('2026-07-01', $result['period']);
        $this->assertSame(100.0, $result['confidence']);
        $this->assertSame('2026-08-01', app(KdpReportTypeDetector::class)->periodFromFilename('Payments_08-2026.csv'));
    }

    public function test_each_file_keeps_its_own_month_when_multiple_months_are_uploaded(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $contents = "Payment Number,Payment Date,Payment Amount,Payment Status\n%s,2026-07-30,10.00,Paid";
        Storage::disk('local')->put('private/kdp-imports/payments-2026-06.csv', sprintf($contents, 'P-JUNE'));
        Storage::disk('local')->put('private/kdp-imports/payments-2026-07.csv', sprintf($contents, 'P-JULY'));

        $session = app(KdpBulkImportService::class)->import([
            'private/kdp-imports/payments-2026-06.csv',
            'private/kdp-imports/payments-2026-07.csv',
        ], $user->id, '2025-01-01');

        $this->assertEqualsCanonicalizing(['2026-06-01', '2026-07-01'], $session->batches->pluck('report_period')->map->toDateString()->all());
        $this->assertEqualsCanonicalizing(['2026-06-01', '2026-07-01'], $session->batches->pluck('detected_report_period')->map->toDateString()->all());
    }

    public function test_imports_multiple_different_reports_in_one_session(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        Storage::disk('local')->put('private/kdp-imports/payments-2026-07.csv', "Payment Number,Marketplace,Currency,Payment Status,Payment Date,Payment Amount\nP-123,Amazon.es,EUR,Paid,2026-07-29,100.00");
        Storage::disk('local')->put('private/kdp-imports/royalties-2026-07.csv', "Title,Author,ASIN,Marketplace,Currency,Format,Net Units Sold,Units Refunded,Total Earnings\nMi libro,Autora,B012345678,Amazon.es,EUR,eBook,10,0,25.50");

        $session = app(KdpBulkImportService::class)->import([
            'private/kdp-imports/payments-2026-07.csv',
            'private/kdp-imports/royalties-2026-07.csv',
        ], $user->id);

        $this->assertSame('completed', $session->status, $session->batches->map->only(['original_file_name', 'import_type', 'status', 'notes'])->toJson());
        $this->assertSame(2, $session->completed_files);
        $this->assertSame(2, $session->imported_rows);
        $this->assertCount(2, $session->batches);
        $this->assertEqualsCanonicalizing(['payments', 'prior_royalties'], $session->batches->pluck('import_type')->all());
        $this->assertDatabaseCount('kdp_report_rows', 2);
    }

    public function test_duplicate_file_does_not_cancel_other_files(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $content = "Payment Number,Marketplace,Currency,Payment Status,Payment Date,Payment Amount\nP-123,Amazon.es,EUR,Paid,2026-07-29,100.00";
        Storage::disk('local')->put('private/kdp-imports/one.csv', $content);
        Storage::disk('local')->put('private/kdp-imports/copy.csv', $content);

        $session = app(KdpBulkImportService::class)->import(['private/kdp-imports/one.csv', 'private/kdp-imports/copy.csv'], $user->id, '2026-07-01');

        $this->assertSame(1, $session->completed_files);
        $this->assertSame(1, $session->duplicate_files);
        $this->assertSame(1, KdpReportRow::count());
    }

    public function test_imports_supported_reports_from_a_zip_archive(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $zipPath = Storage::disk('local')->path('private/kdp-imports/reports.zip');
        Storage::disk('local')->makeDirectory('private/kdp-imports');
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('folder/payments-2026-07.csv', "Payment Number,Payment Date,Payment Amount,Payment Status\nP-ZIP,2026-07-30,10.00,Paid");
        $zip->addFromString('../ignored.php', '<?php echo "unsafe";');
        $zip->close();

        $session = app(KdpBulkImportService::class)->import(['private/kdp-imports/reports.zip'], $user->id);

        $this->assertSame('completed', $session->status);
        $this->assertSame(1, $session->total_files);
        $this->assertSame('payments', $session->batches()->firstOrFail()->import_type);
        $this->assertDatabaseCount('kdp_report_rows', 1);
    }

    public function test_unrecognised_file_is_isolated_for_review(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        Storage::disk('local')->put('private/kdp-imports/unknown.csv', "Foo,Bar\nOne,Two");

        $session = app(KdpBulkImportService::class)->import(['private/kdp-imports/unknown.csv'], $user->id, '2026-07-01');

        $this->assertSame('failed', $session->status);
        $this->assertSame(1, $session->failed_files);
        $this->assertSame('needs_review', $session->batches()->firstOrFail()->status);
    }

    public function test_author_cannot_import_into_another_users_session(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($other);
        Storage::disk('local')->put('private/kdp-imports/payment.csv', "Payment Number,Payment Date,Payment Amount\nP-1,2026-07-30,10.00");

        $this->expectException(HttpException::class);
        app(KdpBulkImportService::class)->import(['private/kdp-imports/payment.csv'], $owner->id);
    }
}
