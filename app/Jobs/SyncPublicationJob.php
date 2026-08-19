<?php

namespace App\Jobs;

use App\Models\Publication;
use App\Services\Kdp\PublicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPublicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [30, 120, 300, 900];

    public int $timeout = 120;

    public function __construct(public Publication $publication) {}

    public function handle(PublicationService $service): void
    {
        $this->publication->update(['status' => 'processing']);

        try {
            if (! $service->syncPublication($this->publication)) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 900);
            }
        } catch (\Exception $exception) {
            Log::warning('Fallo sync', [
                'id' => $this->publication->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->publication->update([
            'status' => 'failed',
            'sync_metadata' => array_merge(
                $this->publication->sync_metadata ?? [],
                ['last_error' => $exception->getMessage()],
            ),
        ]);
    }
}
