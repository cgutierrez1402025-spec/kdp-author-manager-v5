<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use App\Models\ImportSession;
use App\Services\Kdp\KdpReportImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ReprocessKdpImportsCommand extends Command
{
    protected $signature = 'kdp:reprocess-imports {--user= : Limita a un usuario} {--type= : Limita al tipo anterior del lote}';

    protected $description = 'Reprocesa de forma atómica los informes KDP conservados y vuelve a detectar lotes desconocidos';

    public function handle(KdpReportImportService $service, KdpBulkImportService $bulkService): int
    {
        $processed = 0;
        $failed = 0;
        $sessionIds = [];

        ImportBatch::query()
            ->when($this->option('user'), fn ($query, $user) => $query->where('user_id', (int) $user))
            ->when($this->option('type'), fn ($query, $type) => $query->where('import_type', $type))
            ->whereIn('status', ['completed', 'failed'])
            ->eachById(function (ImportBatch $batch) use ($service, &$processed, &$failed, &$sessionIds): void {
                if ($batch->import_session_id) {
                    $sessionIds[] = $batch->import_session_id;
                }
                Auth::loginUsingId($batch->user_id);
                try {
                    $service->reprocess($batch);
                    $processed++;
                    $this->line("Lote #{$batch->id}: reprocesado como {$batch->fresh()->import_type}.");
                } catch (Throwable $exception) {
                    $failed++;
                    $this->warn("Lote #{$batch->id}: {$exception->getMessage()}");
                } finally {
                    Auth::logout();
                }
            });

        ImportSession::whereKey(array_unique($sessionIds))->each(
            fn (ImportSession $session) => $bulkService->summarize($session)
        );

        $this->info("Resultado: {$processed} reprocesados; {$failed} fallidos.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
