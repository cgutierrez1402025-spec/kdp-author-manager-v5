<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ApplicationHealthCommand extends Command
{
    protected $signature = 'app:health';

    protected $description = 'Comprueba base de datos, integridad referencial, almacenamiento y estado operativo';

    public function handle(): int
    {
        try {
            DB::select('select 1');
        } catch (Throwable $exception) {
            $this->error('Base de datos: ERROR — '.$exception->getMessage());

            return self::FAILURE;
        }

        $checks = [['Base de datos', 'OK']];
        $foreignKeyErrors = DB::getDriverName() === 'sqlite' ? count(DB::select('PRAGMA foreign_key_check')) : 0;
        $checks[] = ['Claves foráneas', $foreignKeyErrors === 0 ? 'OK' : "ERROR ({$foreignKeyErrors})"];
        $checks[] = ['Almacenamiento', is_writable(storage_path()) ? 'OK' : 'ERROR (sin escritura)'];
        $checks[] = ['Migraciones aplicadas', (string) (Schema::hasTable('migrations') ? DB::table('migrations')->count() : 0)];
        $checks[] = ['Sesiones KDP fallidas', (string) (Schema::hasTable('import_sessions') ? DB::table('import_sessions')->where('status', 'failed')->count() : 0)];
        $checks[] = ['Lotes KDP fallidos', (string) (Schema::hasTable('import_batches') ? DB::table('import_batches')->where('status', 'failed')->count() : 0)];
        $checks[] = ['Trabajos de cola fallidos', (string) (Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0)];
        $this->table(['Comprobación', 'Resultado'], $checks);

        return $foreignKeyErrors === 0 && is_writable(storage_path()) ? self::SUCCESS : self::FAILURE;
    }
}
