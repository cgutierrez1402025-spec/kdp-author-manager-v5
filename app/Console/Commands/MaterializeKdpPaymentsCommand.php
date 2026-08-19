<?php

namespace App\Console\Commands;

use App\Services\Kdp\KdpReportImportService;
use Illuminate\Console\Command;

class MaterializeKdpPaymentsCommand extends Command
{
    protected $signature = 'kdp:materialize-payments {--user= : Limita el proceso a un usuario}';

    protected $description = 'Reconstruye la tabla de pagos KDP desde las filas importadas existentes';

    public function handle(KdpReportImportService $service): int
    {
        $count = $service->materializeExistingPayments($this->option('user') ? (int) $this->option('user') : null);
        $this->info("{$count} filas de pago materializadas o actualizadas.");

        return self::SUCCESS;
    }
}
