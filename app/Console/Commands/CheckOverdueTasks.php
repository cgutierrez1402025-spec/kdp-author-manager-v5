<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\OverdueTaskNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';

    protected $description = 'Send notifications for overdue tasks';

    public function handle(): int
    {
        $overdueTasks = Task::overdue()
            ->where('status', '!=', 'completed')
            ->with(['assignedTo', 'work'])
            ->get();

        if ($overdueTasks->isEmpty()) {
            $this->info('No overdue tasks found.');

            return self::SUCCESS;
        }

        foreach ($overdueTasks as $task) {
            if ($task->assignedTo) {
                Notification::route('mail', $task->assignedTo->email)
                    ->notify(new OverdueTaskNotification($task));
            }
        }

        $this->info("Sent notifications for {$overdueTasks->count()} overdue tasks.");

        return self::SUCCESS;
    }
}
