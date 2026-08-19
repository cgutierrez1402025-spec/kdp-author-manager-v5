@php($events = $this->getEventsProperty())
<x-filament::section icon="heroicon-o-calendar-days" heading="Próximos eventos" description="Agenda de los próximos treinta días">
<div class="space-y-3">
    @if(empty($events))
        <div class="editorial-empty"><p class="editorial-muted">No hay eventos próximos en los próximos 30 días.</p></div>
    @else
        @foreach($events as $event)
            <div class="rounded-xl border border-gray-200 p-3 dark:border-white/10">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</h4>
                        <p class="text-sm text-gray-500">{{ $event['location_name'] }}, {{ $event['city'] }}</p>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ $event['event_date'] ? \Carbon\Carbon::parse($event['event_date'])->format('d/m/Y') : 'N/A' }}
                    </span>
                </div>
                @if($event['total_copies_sold'])
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $event['total_copies_sold'] }} copias vendidas • {{ number_format($event['total_income'], 2) }} €
                    </p>
                @endif
            </div>
        @endforeach
    @endif
</div>
</x-filament::section>
