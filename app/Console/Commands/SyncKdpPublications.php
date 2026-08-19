<?php

namespace App\Console\Commands;

use App\Models\Publication;
use App\Services\KdpApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncKdpPublications extends Command
{
    protected $signature = 'kdp:sync-publications {--days=1 : Number of days to look back for sold books}';

    protected $description = 'Sync publication data from KDP/Amazon API';

    public function handle(KdpApiService $service): int
    {
        $this->info('Starting KDP publication sync...');

        $publications = Publication::whereNotNull('asin')
            ->where('status', 'published')
            ->get();

        $synced = 0;
        $errors = 0;

        foreach ($publications as $publication) {
            $result = $service->syncPublication($publication);

            if ($result['success']) {
                $synced++;
                $this->line("  Synced: {$publication->asin}");
            } else {
                $errors++;
                Log::error("Failed to sync publication {$publication->id}", $result);
                $this->error("  Failed: {$publication->asin} - {$result['error']}");
            }
        }

        $this->info("Sync complete. {$synced} synced, {$errors} errors.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
