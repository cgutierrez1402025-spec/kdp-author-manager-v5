<?php

namespace App\Jobs;

use App\Exports\InventoryReport;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportInventoryReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $fileName = 'inventory-report-'.now()->format('Y-m-d-H-i-s').'.xlsx';

        Excel::store(new InventoryReport, "exports/$fileName", 'public');

        if ($this->userId) {
            User::find($this->userId)?->notify(
                new ExportReadyNotification($fileName)
            );
        }
    }
}
