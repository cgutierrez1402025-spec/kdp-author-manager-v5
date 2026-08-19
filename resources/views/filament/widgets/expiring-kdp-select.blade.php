@php($expiring_periods = $this->getExpiringPeriods())
<x-filament::section icon="heroicon-o-clock" heading="Vencimientos KDP Select" description="Periodos que finalizan durante los próximos treinta días">
    @if(empty($expiring_periods))
        <div class="editorial-empty"><p class="editorial-muted">No hay vencimientos próximos.</p></div>
    @else
        <div class="space-y-2">
            @foreach($expiring_periods as $period)
                <div class="text-sm">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $period['work_title'] }}</span>
                    <div class="text-xs text-gray-500">
                        Vence: {{ $period['end_date'] }} ({{ $period['remaining_days'] }} días)
                        - {{ $period['free_days_remaining'] }} días gratis restantes
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
