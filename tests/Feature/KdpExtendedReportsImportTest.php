<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\KdpPayment;
use App\Models\KdpReportRow;
use App\Models\User;
use App\Services\Kdp\KdpReportImportService;
use App\Services\Kdp\KdpReportTypeDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KdpExtendedReportsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_and_imports_a_spanish_preorder_report(): void
    {
        [$user, $path] = $this->file('preventas-2026-08.csv', implode("\n", [
            'Título,Autor,ASIN,Tienda,Fecha del pedido,Unidades de preventas,Cancelaciones de preventas,"Unidades de preventas excluidas las cancelaciones"',
            'Mi preventa,Autora,B0PREORDER1,Amazon.es,2026-08-10,8,2,6',
        ]));
        $detection = app(KdpReportTypeDetector::class)->detect(Storage::disk('local')->path($path));
        $this->assertSame('preorders', $detection['type']);

        app(KdpReportImportService::class)->import($this->batch($user, $path, 'preorders'));

        $row = KdpReportRow::firstOrFail();
        $this->assertSame('preorder', $row->row_kind);
        $this->assertSame(8, $row->preorder_units);
        $this->assertSame(2, $row->preorder_cancellations);
        $this->assertSame(6, $row->net_preorder_units);
        $this->assertNotNull($row->publication_id);
    }

    public function test_imports_all_supported_payment_fields(): void
    {
        [$user, $path] = $this->file('payments-2026-07.csv', implode("\n", [
            'Payment Number,Marketplace,Currency,Payment Status,Payment Date,Payment Method,Net Earnings,Sales Period,Source,Accrued Royalty,Tax Withholding,FX Rate,Payment Amount',
            'P-COMPLETE,Amazon.com,USD,Paid,2026-09-29,EFT,95.00,July 2026,eBook sales,100.00,5.00,0.85,80.75',
        ]));

        app(KdpReportImportService::class)->import($this->batch($user, $path, 'payments'));

        $this->assertDatabaseHas('kdp_payments', [
            'payment_number' => 'P-COMPLETE', 'payment_method' => 'EFT', 'net_earnings' => 95,
            'sales_period_start' => '2026-07-01 00:00:00', 'sales_period_end' => '2026-07-31 00:00:00', 'source' => 'eBook sales',
        ]);
    }

    public function test_spanish_payment_report_promotes_generic_fecha_to_payment_date(): void
    {
        [$user, $path] = $this->file('pagos-2026-07.csv', implode("\n", [
            'Periodo de ventas - Fecha de inicio,Periodo de ventas - Fecha final,Tienda,Número de pago,Fecha,Método de pago,Moneda,Importe del pago',
            '2026-05-01,2026-05-31,Amazon.es,100000015517121,2026-07-29,EFT,EUR,7.18',
        ]));

        app(KdpReportImportService::class)->import($this->batch($user, $path, 'payments'));

        $row = KdpReportRow::firstOrFail();
        $payment = KdpPayment::firstOrFail();

        $this->assertSame('2026-07-29', $row->payment_date?->toDateString());
        $this->assertSame('2026-07-29', $payment->payment_date?->toDateString());
    }

    public function test_royalty_estimate_is_kept_out_of_final_royalties(): void
    {
        [$user, $path] = $this->file('estimator-2026-08.csv', "Title,Author,ASIN,Marketplace,Currency,Estimated Royalties,KENP Rate\nEstimated Book,Author,B0ESTIMATE1,Amazon.com,USD,42.50,0.0045");

        app(KdpReportImportService::class)->import($this->batch($user, $path, 'royalties_estimator'));

        $row = KdpReportRow::firstOrFail();
        $this->assertSame('royalty_estimate', $row->row_kind);
        $this->assertSame('estimated', $row->observation_status);
        $this->assertDatabaseHas('kdp_royalty_estimates', ['kdp_report_row_id' => $row->id, 'estimated_amount' => 42.5]);
        $this->assertDatabaseCount('royalty_entries', 0);
    }

    public function test_legacy_fields_are_normalized_instead_of_only_kept_in_json(): void
    {
        [$user, $path] = $this->file('legacy-2026-07.csv', "Título,Autor,ASIN,Tienda,Moneda,Ingresos,Plan de pago,Tamaño medio del archivo (MB),Unidades netas vendidas o KENP leídas**\nLibro,Autora,B0LEGACY01,Amazon.es,EUR,19.25,70%,2.75,140");

        app(KdpReportImportService::class)->import($this->batch($user, $path, 'sales_royalties'));

        $row = KdpReportRow::firstOrFail();
        $this->assertSame('19.2500', $row->income_amount);
        $this->assertSame('70%', $row->payment_plan);
        $this->assertSame('2.7500', $row->average_file_size_mb);
        $this->assertSame(140, $row->combined_units_or_kenp);
        $this->assertSame('legacy', $row->source_generation);
    }

    /** @return array{User, string} */
    private function file(string $name, string $contents): array
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $path = 'private/kdp-imports/'.$name;
        Storage::disk('local')->put($path, $contents);

        return [$user, $path];
    }

    private function batch(User $user, string $path, string $type): ImportBatch
    {
        $contents = Storage::disk('local')->get($path);

        return ImportBatch::create([
            'user_id' => $user->id, 'import_type' => $type, 'report_period' => '2026-08-01',
            'source_system' => 'amazon_kdp', 'original_file_path' => $path,
            'original_file_name' => basename($path), 'file_hash' => hash('sha256', $contents),
            'detected_format' => 'csv', 'status' => 'pending', 'processed_by_ai' => false,
        ]);
    }
}
