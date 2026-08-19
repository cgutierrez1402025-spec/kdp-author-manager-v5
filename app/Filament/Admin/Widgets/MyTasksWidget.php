<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Task;
use Filament\Widgets\Widget;

class MyTasksWidget extends Widget
{
    protected static string $view = 'filament.widgets.my-tasks';

    protected static ?int $sort = 2;

    protected int $tasksLimit = 5;

    protected int|string|array $columnSpan = 1;

    public function getTasksProperty(): array
    {
        return Task::with('work')
            ->where(fn ($query) => $query
                ->where('assigned_to', auth()->id())
                ->orWhere('created_by', auth()->id()))
            ->where('status', '!=', 'completed')
            ->orderBy('due_date')
            ->orderBy('priority')
            ->limit($this->tasksLimit)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'work_title' => $task->work->title_public ?? null,
                'priority' => $task->priority,
                'due_date' => $task->due_date,
                'is_overdue' => $task->isOverdue(),
            ])
            ->values()
            ->all();
    }
}
