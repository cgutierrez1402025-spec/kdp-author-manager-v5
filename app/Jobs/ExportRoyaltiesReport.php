<?php

namespace App\Jobs;

use App\Exports\RoyaltiesReport;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportRoyaltiesReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $publicationId;

    protected ?int $platformId;

    protected ?string $startDate;

    protected ?string $endDate;

    protected ?int $userId;

    public function __construct(?int $publicationId, ?int $platformId, ?string $startDate, ?string $endDate, ?int $userId = null)
    {
        $this->publicationId = $publicationId;
        $this->platformId = $platformId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $fileName = 'royalties-report-'.now()->format('Y-m-d-H-i-s').'.xlsx';

        Excel::store(
            new RoyaltiesReport(
                $this->publicationId,
                $this->platformId,
                $this->startDate,
                $this->endDate
            ),
            "exports/$fileName",
            'public'
        );

        if ($this->userId) {
            User::find($this->userId)?->notify(
                new ExportReadyNotification($fileName)
            );
        }
    }
}
