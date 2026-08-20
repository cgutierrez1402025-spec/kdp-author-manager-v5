<?php

namespace App\Services\Kdp;

use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Models\KdpCatalogItem;
use App\Models\KdpPayment;
use App\Models\KdpPaymentAllocation;
use App\Models\KdpReportRow;
use App\Models\KdpRoyaltyEstimate;
use App\Models\Publication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class KdpReportImportService
{
    private const ALIASES = [
        'title' => ['title', 'titulo'],
        'author' => ['author', 'autor', 'nombre del autor'],
        'asin' => ['asin', 'asin/isbn'],
        'isbn' => ['isbn'],
        'format' => ['format', 'formato'],
        'marketplace' => ['marketplace', 'mercado', 'tienda'],
        'currency' => ['currency', 'moneda'],
        'transaction_type' => ['transaction type', 'tipo de transaccion'],
        'royalty_type' => ['royalty type', 'tipo de regalia'],
        'units_sold' => ['units sold', 'unidades vendidas'],
        'units_refunded' => ['units refunded', 'unidades reembolsadas', 'unidades devueltas', 'devoluciones'],
        'net_units_sold' => ['net units sold', 'unidades netas vendidas', 'unidades netas'],
        'paid_units' => ['paid units', 'unidades pagadas'],
        'free_units' => ['free units', 'unidades gratuitas'],
        'preorder_units' => ['pre-order units', 'preorder units', 'unidades de preventas', 'unidades de preventa'],
        'preorder_cancellations' => ['pre-order cancellations', 'preorder cancellations', 'cancelaciones de preventas', 'cancelaciones de preventa'],
        'net_preorder_units' => ['pre-order units net of cancellations', 'net pre-order units', 'unidades de preventas excluidas las cancelaciones'],
        'kenp_read' => ['kenp read', 'kenp pages read', 'paginas kenp leidas'],
        'combined_units_or_kenp' => ['net units sold or kenp read', 'unidades netas vendidas o kenp leidas**', 'unidades netas vendidas o kenp leidas'],
        'average_list_price' => ['average list price without tax', 'precio medio de lista sin impuestos'],
        'average_offer_price' => ['average offer price without tax', 'precio medio de oferta sin impuestos'],
        'average_delivery_cost' => ['average delivery cost', 'coste medio de entrega', 'gasto medio de envio', 'gasto medio de entrega/produccion', 'gasto de produccion medio'],
        'average_file_size_mb' => ['average file size', 'average file size (mb)', 'tamano medio del archivo (mb)'],
        'total_earnings' => ['total earnings', 'regalias', 'royalty', 'ganancias totales', 'ingresos totales'],
        'income_amount' => ['income', 'income amount', 'ingresos', 'estimated royalties', 'estimated royalty', 'regalias estimadas'],
        'payment_plan' => ['payment plan', 'plan de pago'],
        'payment_number' => ['payment number', 'numero de pago'],
        'payment_status' => ['payment status', 'estado del pago'],
        'payment_date' => ['payment date', 'fecha de pago'],
        'payment_method' => ['payment method', 'metodo de pago'],
        'net_earnings' => ['net earnings', 'ingresos netos'],
        'sales_period' => ['sales period', 'periodo de ventas'],
        'payment_source' => ['source', 'fuente'],
        'accrued_royalty' => ['accrued royalty', 'regalia acumulada', 'regalias acumuladas'],
        'tax_withholding' => ['tax withholding', 'retencion fiscal', 'retencion de impuestos'],
        'fx_rate' => ['fx rate', 'tipo de cambio'],
        'payment_amount' => ['payment amount', 'importe del pago'],
        'transaction_date' => ['royalty date', 'fecha de las regalias', 'order date', 'fecha del pedido', 'date', 'fecha'],
        'kenp_rate' => ['kenp rate', 'tarifa kenp'],
    ];

    public function import(ImportBatch $batch): ImportBatch
    {
        abort_unless($batch->user_id === auth()->id() || auth()->user()?->canViewAllAuthorData(), 403);

        $batch->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $tables = $this->readTables(Storage::disk('local')->path($batch->original_file_path));
            $counters = ['total_rows' => 0, 'imported_rows' => 0, 'skipped_rows' => 0, 'error_rows' => 0];

            DB::transaction(function () use ($batch, $tables, &$counters): void {
                $hasCombinedRoyalties = collect(array_keys($tables))
                    ->contains(fn (string $sheet): bool => $this->normalizeHeader($sheet) === 'ventas combinadas');

                foreach ($tables as $sheet => $rows) {
                    if (! $this->shouldImportSheet($sheet, $hasCombinedRoyalties, $batch->import_type)) {
                        continue;
                    }
                    $this->importTable($batch, $sheet, $rows, $counters);
                }
            });

            $batch->update($counters + ['status' => 'completed', 'finished_at' => now()]);
            User::query()->pluck('id')->each(fn ($userId) => Cache::forget('dashboard:user:'.$userId.':summary'));
        } catch (Throwable $exception) {
            $batch->update(['status' => 'failed', 'finished_at' => now(), 'notes' => $exception->getMessage()]);
            throw $exception;
        }

        return $batch->refresh();
    }

    public function reprocess(ImportBatch $batch): ImportBatch
    {
        abort_unless($batch->user_id === auth()->id() || auth()->user()?->canViewAllAuthorData(), 403);

        $catalogItemIds = $batch->reportRows()->whereNotNull('kdp_catalog_item_id')->pluck('kdp_catalog_item_id')->unique();
        $reportRowIds = $batch->reportRows()->pluck('id');
        $paymentIds = KdpPaymentAllocation::whereIn('kdp_report_row_id', $reportRowIds)->pluck('kdp_payment_id')->unique();

        try {
            return DB::transaction(function () use ($batch, $catalogItemIds, $paymentIds): ImportBatch {
                $lockedBatch = ImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
                $path = Storage::disk('local')->path($lockedBatch->original_file_path);
                if (! is_file($path) || hash_file('sha256', $path) !== $lockedBatch->file_hash) {
                    throw new RuntimeException('El archivo original no existe o no coincide con su huella SHA-256.');
                }

                $detection = app(KdpReportTypeDetector::class)->detect($path);
                $lockedBatch->update([
                    'detected_import_type' => $detection['type'],
                    'detection_confidence' => $detection['confidence'],
                    'import_type' => $lockedBatch->import_type === 'unknown' && $detection['type']
                        ? $detection['type'] : $lockedBatch->import_type,
                ]);

                $lockedBatch->reportRows()->delete();
                $lockedBatch->errors()->delete();
                $lockedBatch->update([
                    'status' => 'pending', 'total_rows' => 0, 'imported_rows' => 0,
                    'skipped_rows' => 0, 'error_rows' => 0, 'started_at' => null, 'finished_at' => null,
                ]);

                $result = $this->import($lockedBatch->refresh());
                $this->cleanDerivedRecords($catalogItemIds->all(), $paymentIds->all());

                return $result;
            });
        } catch (Throwable $exception) {
            $batch->refresh()->update(['notes' => 'Reprocesado fallido; se conservaron los datos anteriores. '.$exception->getMessage()]);
            throw $exception;
        }
    }

    /** @param array<int, int> $catalogItemIds
     * @param  array<int, int>  $paymentIds
     */
    private function cleanDerivedRecords(array $catalogItemIds, array $paymentIds): void
    {
        KdpCatalogItem::whereKey($catalogItemIds)->each(function (KdpCatalogItem $item): void {
            if (! $item->reportRows()->exists()) {
                if ($item->review_status === 'pending' && ! $item->work_id && ! $item->publication_id) {
                    $item->delete();
                }

                return;
            }

            $item->update([
                'marketplaces' => $item->reportRows()->whereNotNull('marketplace')->pluck('marketplace')->unique()->sort()->values()->all(),
                'first_seen_at' => $item->reportRows()->min('created_at'),
                'last_seen_at' => $item->reportRows()->max('created_at'),
            ]);
        });

        KdpPayment::whereKey($paymentIds)->doesntHave('allocations')->delete();
    }

    /** @return array<string, array<int, array<int, string|null>>> */
    private function readTables(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'xlsx') {
            return app(XlsxTableReader::class)->read($path);
        }
        if (! in_array($extension, ['csv', 'txt'], true)) {
            throw new RuntimeException('Formato no admitido. Utiliza CSV o XLSX.');
        }

        $firstLine = (string) fgets(fopen($path, 'rb'));
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn (string $candidate) => substr_count($firstLine, $candidate))->first();
        $file = new \SplFileObject($path, 'rb');
        $file->setCsvControl($delimiter);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $rows = [];
        foreach ($file as $row) {
            if (is_array($row) && $row !== [null]) {
                $rows[] = array_map(fn ($value) => is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1252') : $value, $row);
            }
        }

        return ['CSV' => $rows];
    }

    /** @param array<int, array<int, string|null>> $rows
     * @param  array<string, int>  $counters
     */
    private function importTable(ImportBatch $batch, string $sheet, array $rows, array &$counters): void
    {
        $headerIndex = $this->headerIndex($rows);
        if ($headerIndex === null) {
            return;
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $rows[$headerIndex]);

        foreach (array_slice($rows, $headerIndex + 1, null, true) as $index => $values) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $counters['total_rows']++;
            $raw = [];
            foreach ($headers as $column => $header) {
                if ($header !== '') {
                    $raw[$header] = $values[$column] ?? null;
                }
            }

            try {
                $normalized = $this->normalizeRow($raw, $sheet, $batch->import_type);
                if (in_array($batch->import_type, ['dashboard', 'royalties_estimator', 'kenp'], true)) {
                    $normalized['snapshot_at'] = $batch->created_at;
                }
                $fingerprint = hash('sha256', implode('|', [
                    $batch->user_id,
                    $batch->import_type,
                    optional($batch->report_period)->format('Y-m'),
                    json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
                ]));

                if (KdpReportRow::where('user_id', $batch->user_id)->where('row_fingerprint', $fingerprint)->exists()) {
                    $counters['skipped_rows']++;

                    continue;
                }

                $publicationId = $this->publicationId($batch, $normalized);
                $catalogItem = $this->catalogItem($batch, $normalized, $publicationId);

                $reportRow = KdpReportRow::create($normalized + [
                    'user_id' => $batch->user_id,
                    'import_batch_id' => $batch->id,
                    'publication_id' => $publicationId,
                    'kdp_catalog_item_id' => $catalogItem?->id,
                    'row_fingerprint' => $fingerprint,
                    'report_type' => $batch->import_type,
                    'source_sheet' => $sheet,
                    'report_period' => $batch->report_period,
                    'raw_data' => ['sheet' => $sheet, 'row' => $index + 1, 'values' => $raw],
                    'normalized_data' => $normalized,
                ]);
                if ($catalogItem && $reportRow->row_kind !== 'payment') {
                    app(KdpCatalogMaterializer::class)->materialize($catalogItem);
                    $reportRow->refresh();
                }
                $this->materializePayment($batch, $reportRow);
                $this->materializeEstimate($batch, $reportRow);
                $counters['imported_rows']++;
            } catch (Throwable $exception) {
                ImportError::create([
                    'import_batch_id' => $batch->id,
                    'severity' => 'error',
                    'error_type' => 'invalid_row',
                    'message' => $exception->getMessage(),
                    'row_number' => $index + 1,
                    'suggested_solution' => 'Revise la fila en el archivo original y vuelva a importarlo.',
                    'resolved' => false,
                ]);
                $counters['error_rows']++;
            }
        }
    }

    public function materializePayment(ImportBatch $batch, KdpReportRow $row): void
    {
        if ($row->row_kind !== 'payment' || ! $row->payment_number) {
            return;
        }

        $payment = KdpPayment::updateOrCreate(
            ['user_id' => $batch->user_id, 'payment_number' => $row->payment_number, 'currency' => $row->currency],
            [
                'latest_import_batch_id' => $batch->id, 'marketplace' => $row->marketplace,
                'status' => $row->payment_status, 'payment_date' => $row->payment_date,
                'payment_method' => $row->payment_method, 'net_earnings' => $row->net_earnings,
                'sales_period_start' => $this->salesPeriod($row->sales_period)[0],
                'sales_period_end' => $this->salesPeriod($row->sales_period)[1],
                'source' => $row->payment_source,
                'accrued_royalty' => $row->accrued_royalty, 'tax_withholding' => $row->tax_withholding,
                'fx_rate' => $row->fx_rate, 'payment_amount' => $row->payment_amount,
                'raw_data' => $row->raw_data,
            ],
        );

        KdpPaymentAllocation::updateOrCreate(
            ['kdp_report_row_id' => $row->id],
            [
                'kdp_payment_id' => $payment->id, 'publication_id' => $row->publication_id,
                'allocated_amount' => $row->publication_id ? $row->payment_amount : null,
                'currency' => $row->currency, 'allocation_method' => 'source_row',
                'status' => $row->publication_id ? 'allocated' : 'unallocated',
                'confidence' => $row->publication_id ? 100 : null,
            ],
        );
    }

    public function materializeEstimate(ImportBatch $batch, KdpReportRow $row): void
    {
        if ($row->row_kind !== 'royalty_estimate') {
            return;
        }

        KdpRoyaltyEstimate::updateOrCreate(['kdp_report_row_id' => $row->id], [
            'user_id' => $batch->user_id, 'publication_id' => $row->publication_id,
            'marketplace_id' => $row->publication?->marketplace_id, 'import_batch_id' => $batch->id,
            'period' => $row->report_period, 'snapshot_at' => $row->snapshot_at,
            'estimated_amount' => $row->income_amount ?? $row->total_earnings,
            'currency' => $row->currency, 'kenp_rate' => $row->kenp_rate,
            'filters' => ['report_type' => $batch->import_type],
        ]);
    }

    public function materializeExistingPayments(?int $userId = null): int
    {
        $count = 0;
        KdpReportRow::query()->with('importBatch')->where('row_kind', 'payment')->whereNotNull('payment_number')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->chunkById(200, function ($rows) use (&$count): void {
                foreach ($rows as $row) {
                    $this->materializePayment($row->importBatch, $row);
                    $count++;
                }
            });

        return $count;
    }

    /** @param array<int, array<int, string|null>> $rows */
    private function headerIndex(array $rows): ?int
    {
        $known = collect(self::ALIASES)->flatten()->map(fn ($header) => $this->normalizeHeader($header));
        $bestIndex = null;
        $bestScore = 0;

        foreach (array_slice($rows, 0, 25, true) as $index => $row) {
            $score = collect($row)->map(fn ($value) => $this->normalizeHeader((string) $value))->intersect($known)->count();
            if ($score > $bestScore) {
                $bestIndex = $index;
                $bestScore = $score;
            }
        }

        return $bestScore >= 2 ? $bestIndex : null;
    }

    /** @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function normalizeRow(array $raw, string $sheet, string $reportType): array
    {
        $row = [];
        foreach (self::ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $key = $this->normalizeHeader($alias);
                if (array_key_exists($key, $raw)) {
                    $row[$field] = is_string($raw[$key]) ? trim($raw[$key]) : $raw[$key];
                    break;
                }
            }
        }

        if (empty($row['asin']) && empty($row['payment_number'])) {
            throw new RuntimeException('La fila no contiene ASIN ni número de pago.');
        }

        foreach (['units_sold', 'units_refunded', 'net_units_sold', 'paid_units', 'free_units', 'preorder_units', 'preorder_cancellations', 'net_preorder_units', 'kenp_read', 'combined_units_or_kenp'] as $field) {
            $row[$field] = $this->number($row[$field] ?? null, true);
        }
        foreach (['average_list_price', 'average_offer_price', 'average_delivery_cost', 'average_file_size_mb', 'total_earnings', 'income_amount', 'net_earnings', 'accrued_royalty', 'tax_withholding', 'fx_rate', 'payment_amount', 'kenp_rate'] as $field) {
            $row[$field] = $this->number($row[$field] ?? null);
        }

        $row['asin'] = isset($row['asin']) ? strtoupper($row['asin']) : null;
        $row['currency'] = isset($row['currency']) ? strtoupper(substr($row['currency'], 0, 3)) : null;
        $row['row_kind'] = $this->rowKind($row, $sheet, $reportType);
        $row['observation_status'] = match ($reportType) {
            'dashboard', 'royalties_estimator' => 'estimated',
            'kenp' => 'provisional',
            default => 'final',
        };
        $row['source_generation'] = in_array($reportType, ['sales_royalties', 'historical'], true) ? 'legacy' : 'current';
        $row['snapshot_at'] = null;
        $row['format'] = $this->format($row['format'] ?? null, $row['transaction_type'] ?? null, $sheet);
        $row['payment_date'] = $this->date($row['payment_date'] ?? null);
        $row['transaction_date'] = $this->date($row['transaction_date'] ?? null);

        return $row;
    }

    /** @param array<string, mixed> $row */
    private function publicationId(ImportBatch $batch, array $row): ?int
    {
        if (empty($row['asin'])) {
            return null;
        }

        return Publication::query()
            ->where('asin', $row['asin'])
            ->when($row['format'] ?? null, fn ($query, $format) => $query->where('format', $format))
            ->whereHas('work', fn ($query) => $query->where('user_id', $batch->user_id))
            ->value('id');
    }

    /** @param array<string, mixed> $row */
    private function catalogItem(ImportBatch $batch, array $row, ?int $publicationId): ?KdpCatalogItem
    {
        if (empty($row['title']) && empty($row['asin']) && empty($row['isbn'])) {
            return null;
        }

        $identity = hash('sha256', implode('|', [
            strtoupper((string) ($row['asin'] ?? '')),
            preg_replace('/[^0-9X]/i', '', (string) ($row['isbn'] ?? '')),
            $this->normalizeHeader((string) ($row['title'] ?? '')),
            $this->normalizeHeader((string) ($row['author'] ?? '')),
            (string) ($row['format'] ?? ''),
        ]));

        $item = KdpCatalogItem::firstOrNew([
            'user_id' => $batch->user_id,
            'identity_key' => $identity,
        ]);
        $marketplaces = collect($item->marketplaces ?? [])
            ->push($row['marketplace'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
        $publication = $publicationId ? Publication::with('work')->find($publicationId) : null;

        $item->fill([
            'work_id' => $publication?->work_id,
            'publication_id' => $publicationId,
            'asin' => $row['asin'] ?? $item->asin,
            'isbn' => $row['isbn'] ?? $item->isbn,
            'title' => $row['title'] ?? $item->title ?? 'Título no informado',
            'author' => $row['author'] ?? $item->author,
            'format' => $row['format'] ?? $item->format,
            'marketplaces' => $marketplaces,
            'review_status' => $publicationId ? 'linked' : 'pending',
            'first_seen_at' => $item->first_seen_at ?? now(),
            'last_seen_at' => now(),
        ])->save();

        if ($publication && ! $publication->isbn && ! empty($row['isbn'])) {
            $publication->update(['isbn' => $row['isbn']]);
        }

        return $item;
    }

    private function normalizeHeader(string $header): string
    {
        return (string) Str::of($header)->lower()->ascii()->replaceMatches('/\s+/', ' ')->trim();
    }

    private function number(mixed $value, bool $integer = false): int|float|null
    {
        if ($value === null || trim((string) $value) === '' || strtoupper(trim((string) $value)) === 'N/A') {
            return null;
        }

        $value = preg_replace('/[^0-9,\.\-]/', '', (string) $value);
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = strrpos($value, ',') > strrpos($value, '.')
                ? str_replace(['.', ','], ['', '.'], $value)
                : str_replace(',', '', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        if (! is_numeric($value)) {
            throw new RuntimeException("Valor numérico no válido: {$value}");
        }

        return $integer ? (int) round((float) $value) : (float) $value;
    }

    private function format(?string $value, ?string $transactionType, string $sheet): ?string
    {
        $value = $this->normalizeHeader(implode(' ', array_filter([$value, $transactionType, $sheet])));

        return match (true) {
            str_contains($value, 'paperback'), str_contains($value, 'tapa blanda') => 'paperback',
            str_contains($value, 'hardcover'), str_contains($value, 'tapa dura') => 'hardcover',
            str_contains($value, 'audiobook'), str_contains($value, 'audio book'), str_contains($value, 'audiolibro') => 'audiobook',
            str_contains($value, 'ebook'), str_contains($value, 'kindle') => 'ebook',
            str_contains($value, 'ventas combinadas'), str_contains($value, 'pedidos procesados') => 'ebook',
            default => null,
        };
    }

    /** @param array<string, mixed> $row */
    private function rowKind(array $row, string $sheet, string $reportType): string
    {
        $sheet = $this->normalizeHeader($sheet);

        return match (true) {
            isset($row['payment_number']) => 'payment',
            $reportType === 'preorders' || isset($row['preorder_units']) || isset($row['preorder_cancellations']) => 'preorder',
            str_contains($sheet, 'kenp') => 'kenp',
            $reportType === 'royalties_estimator' => 'royalty_estimate',
            $reportType === 'dashboard' && isset($row['kenp_read']) => 'kenp',
            $reportType === 'dashboard' && (isset($row['paid_units']) || isset($row['free_units'])) => 'order',
            $reportType === 'dashboard' => 'royalty_estimate',
            isset($row['paid_units']) || isset($row['free_units']) => 'order',
            default => 'royalty',
        };
    }

    private function shouldImportSheet(string $sheet, bool $hasCombinedRoyalties, string $reportType): bool
    {
        $sheet = $this->normalizeHeader($sheet);

        if (str_contains($sheet, 'definiciones') || $sheet === 'resumen') {
            return false;
        }

        if ($hasCombinedRoyalties && str_starts_with($sheet, 'regalias de')) {
            return false;
        }

        if ($sheet !== 'csv') {
            if ($reportType === 'orders') {
                return str_contains($sheet, 'pedido') || str_contains($sheet, 'order');
            }
            if ($reportType === 'kenp') {
                return str_contains($sheet, 'kenp');
            }
            if ($reportType === 'preorders') {
                return str_contains($sheet, 'preventa') || str_contains($sheet, 'pre-order') || str_contains($sheet, 'preorder');
            }
        }

        if ($reportType === 'sales_royalties' && str_contains($sheet, 'pedidos')) {
            return false;
        }

        return true;
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (Throwable) {
            throw new RuntimeException("Fecha no válida: {$value}");
        }
    }

    /** @return array{0: ?string, 1: ?string} */
    private function salesPeriod(?string $value): array
    {
        if (! $value) {
            return [null, null];
        }

        try {
            $start = Carbon::parse($value)->startOfMonth();

            return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
        } catch (Throwable) {
            return [null, null];
        }
    }
}
