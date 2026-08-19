@php($tasks = $this->getTasksProperty())
<x-filament::section icon="heroicon-o-check-circle" heading="Próximas tareas" description="Trabajo pendiente ordenado por fecha y prioridad">
<div class="space-y-3">
    @if(empty($tasks))
        <div class="editorial-empty">
            <x-filament::icon icon="heroicon-o-check-badge" class="mb-2 h-8 w-8 text-success-500" />
            <p class="font-medium text-gray-700 dark:text-gray-200">Todo al día</p>
            <p class="editorial-muted">No hay tareas pendientes.</p>
        </div>
    @else
        @foreach($tasks as $task)
            <a href="/admin/tasks/{{ $task['id'] }}/edit" class="block rounded-xl border p-3 transition hover:bg-gray-50 dark:hover:bg-white/5 {{ $task['is_overdue'] ? 'border-danger-300 dark:border-danger-500/40' : 'border-gray-200 dark:border-white/10' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">
                            {{ $task['title'] }}
                        </h4>
                        @if($task['work_title'])
                            <p class="text-sm text-gray-500">{{ $task['work_title'] }}</p>
                        @endif
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs {{ $task['is_overdue'] ? 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300' : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300' }}">
                        @if($task['is_overdue'])
                            Vencida
                        @elseif($task['due_date'])
                            {{ \Carbon\Carbon::parse($task['due_date'])->format('d/m') }}
                        @endif
                    </span>
                </div>
            </a>
        @endforeach
    @endif
</div>
</x-filament::section>
