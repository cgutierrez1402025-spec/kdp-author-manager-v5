<?php

namespace App\Jobs;

use App\Exports\WorksProgressReport;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportWorksProgressReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $fileName = 'works-progress-report-'.now()->format('Y-m-d-H-i-s').'.xlsx';

        Excel::store(new WorksProgressReport, "exports/$fileName", 'public');

        if ($this->userId) {
            User::find($this->userId)?->notify(
                new ExportReadyNotification($fileName)
            );
        }
    }
}
