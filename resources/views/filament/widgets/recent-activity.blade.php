<x-filament::section icon="heroicon-o-clock" heading="Actividad reciente" description="Últimos cambios registrados en el catálogo">
    @php($activities = $this->getActivities())
    @if(empty($activities))
        <div class="editorial-empty"><p class="editorial-muted">No hay actividad reciente.</p></div>
    @else
        <div class="space-y-2">
            @foreach($activities as $activity)
                <div class="flex flex-wrap items-baseline gap-x-1 border-b border-gray-100 py-2 text-sm last:border-0 dark:border-white/5">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $activity['user'] }}</span>
                    <span class="text-gray-500"> {{ $activity['action'] }}</span>
                    <span class="font-medium text-gray-700"> {{ $activity['description'] }}</span>
                    <span class="text-xs text-gray-400">{{ $activity['created_at'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
