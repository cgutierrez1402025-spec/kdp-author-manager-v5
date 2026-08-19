<?php

namespace App\Services\Kdp;

use App\Models\ImportBatch;
use App\Models\ImportSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class KdpBulkImportService
{
    public function __construct(private KdpReportTypeDetector $detector, private KdpReportImportService $importer) {}

    /** @param array<int, string> $paths */
    public function import(array $paths, int $userId, ?string $fallbackPeriod = null, string $requestedType = 'auto', ?string $notes = null): ImportSession
    {
        abort_unless($userId === auth()->id() || auth()->user()?->canViewAllAuthorData(), 403);
        $paths = $this->expandArchives($paths);
        if ($paths === [] || count($paths) > 20) {
            abort(422, 'Selecciona entre 1 y 20 informes.');
        }

        $session = ImportSession::create(['user_id' => $userId, 'status' => 'processing', 'total_files' => count($paths), 'started_at' => now(), 'notes' => $notes]);

        foreach (array_values($paths) as $order => $path) {
            $absolutePath = Storage::disk('local')->path($path);
            $hash = is_file($absolutePath) ? hash_file('sha256', $absolutePath) : null;
            if (! $hash || ImportBatch::where('file_hash', $hash)->exists()) {
                $session->increment($hash ? 'duplicate_files' : 'failed_files');

                continue;
            }

            try {
                $detection = $this->detector->detect($absolutePath);
                $type = $requestedType === 'auto' ? $detection['type'] : $requestedType;
                $period = $detection['period'] ?? $fallbackPeriod;
                $batch = ImportBatch::create([
                    'import_session_id' => $session->id, 'user_id' => $userId, 'import_type' => $type ?? 'unknown',
                    'detected_import_type' => $detection['type'], 'detection_confidence' => $detection['confidence'],
                    'report_period' => $period ? Carbon::parse($period)->startOfMonth() : now()->startOfMonth(),
                    'detected_report_period' => $detection['period'], 'source_system' => 'amazon_kdp',
                    'original_file_path' => $path, 'original_file_name' => basename($path), 'file_hash' => $hash,
                    'detected_format' => strtolower(pathinfo($path, PATHINFO_EXTENSION)), 'processing_order' => $order + 1,
                    'status' => $type ? 'pending' : 'needs_review', 'processed_by_ai' => false,
                ]);

                if (! $type) {
                    $session->increment('failed_files');

                    continue;
                }

                $this->importer->import($batch);
                $session->increment('completed_files');
            } catch (Throwable $exception) {
                report($exception);
                $session->increment('failed_files');
            }
        }

        $this->summarize($session);

        return $session->refresh();
    }

    /** @param array<int, string> $paths
     * @return array<int, string>
     */
    private function expandArchives(array $paths): array
    {
        $expanded = [];
        foreach ($paths as $path) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'zip') {
                $expanded[] = $path;

                continue;
            }

            $zip = new ZipArchive;
            if ($zip->open(Storage::disk('local')->path($path)) !== true) {
                abort(422, 'No se puede abrir uno de los ZIP.');
            }
            $directory = 'private/kdp-imports/extracted/'.Str::uuid();
            $totalSize = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $name = (string) ($stat['name'] ?? '');
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (! in_array($extension, ['csv', 'txt', 'xlsx'], true)) {
                    continue;
                }
                $size = (int) ($stat['size'] ?? 0);
                $totalSize += $size;
                if ($size > 20 * 1024 * 1024 || $totalSize > 100 * 1024 * 1024) {
                    $zip->close();
                    abort(422, 'El ZIP supera los límites seguros de extracción.');
                }
                $contents = $zip->getFromIndex($index);
                if ($contents === false) {
                    continue;
                }
                $destination = $directory.'/'.$index.'-'.basename($name);
                Storage::disk('local')->put($destination, $contents);
                $expanded[] = $destination;
            }
            $zip->close();
        }

        return $expanded;
    }

    public function summarize(ImportSession $session): void
    {
        $session->refresh();
        $totals = $session->batches()->selectRaw('COALESCE(SUM(imported_rows),0) imported, COALESCE(SUM(skipped_rows),0) skipped, COALESCE(SUM(error_rows),0) errors')->first();
        $failed = $session->failed_files;
        $completed = $session->completed_files;
        $status = $failed === 0 ? 'completed' : ($completed > 0 || $session->duplicate_files > 0 ? 'partial' : 'failed');
        $session->update(['status' => $status, 'imported_rows' => $totals->imported, 'skipped_rows' => $totals->skipped, 'error_rows' => $totals->errors, 'finished_at' => now()]);
    }

    public function reprocessSession(ImportSession $session): ImportSession
    {
        abort_unless($session->user_id === auth()->id() || auth()->user()?->canViewAllAuthorData(), 403);
        $session->update(['status' => 'processing', 'completed_files' => 0, 'failed_files' => 0, 'imported_rows' => 0, 'skipped_rows' => 0, 'error_rows' => 0, 'started_at' => now(), 'finished_at' => null]);

        foreach ($session->batches as $batch) {
            try {
                $this->importer->reprocess($batch);
                $session->increment('completed_files');
            } catch (Throwable) {
                $session->increment('failed_files');
            }
        }
        $this->summarize($session);

        return $session->refresh();
    }
}
