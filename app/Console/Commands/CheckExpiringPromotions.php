<?php

namespace App\Console\Commands;

use App\Models\KdpSelectPeriod;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckExpiringPromotions extends Command
{
    protected $signature = 'promotions:check-expiring {--days=7 : Days before expiration to check}';

    protected $description = 'Send notifications for promotions expiring soon';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $expiringSoon = KdpSelectPeriod::where('status', 'active')
            ->whereDate('end_date', now()->addDays($days)->toDateString())
            ->with('publication.work')
            ->get();

        if ($expiringSoon->isEmpty()) {
            $this->info('No promotions expiring in the next '.$days.' days.');

            return self::SUCCESS;
        }

        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'Admin'))->get();

        foreach ($expiringSoon as $period) {
            foreach ($admins as $admin) {
                Mail::raw(
                    "KDP Select period for '{$period->publication->work->title_public}' expires on {$period->end_date->format('Y-m-d')}.\n\nRemaining free days: {$period->free_promo_days_remaining}",
                    function ($message) use ($admin, $period) {
                        $message->to($admin->email)
                            ->subject('KDP Select Period Expiring Soon - '.$period->publication->work->title_public);
                    }
                );
            }

            $this->line("  Sent notification for: {$period->publication->work->title_public}");
        }

        $this->info("Notifications sent for {$expiringSoon->count()} expiring periods.");

        return self::SUCCESS;
    }
}
