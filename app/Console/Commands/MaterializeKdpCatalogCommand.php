<?php

namespace App\Console\Commands;

use App\Services\Kdp\KdpCatalogMaterializer;
use Illuminate\Console\Command;

class MaterializeKdpCatalogCommand extends Command
{
    protected $signature = 'kdp:materialize-catalog {--user= : Limita el proceso a un usuario}';

    protected $description = 'Crea o vincula obras, publicaciones y metadatos desde el catálogo KDP detectado';

    public function handle(KdpCatalogMaterializer $materializer): int
    {
        $count = $materializer->materializeAll($this->option('user') ? (int) $this->option('user') : null);
        $this->info("{$count} elementos del catálogo materializados.");

        return self::SUCCESS;
    }
}
