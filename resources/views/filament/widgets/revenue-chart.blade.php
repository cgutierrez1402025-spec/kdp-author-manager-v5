@php($chartData = $this->getChartData())
@php($maxRevenue = max($chartData['data'] ?: [0]))
<x-filament::section id="revenue" icon="heroicon-o-chart-bar" heading="Ingresos" description="Regalías acumuladas durante los últimos seis meses">
    <div class="space-y-3" role="img" aria-label="Ingresos mensuales de los últimos seis meses">
        @foreach($chartData['labels'] as $index => $label)
            @php($amount = $chartData['data'][$index] ?? 0)
            <div class="grid grid-cols-[5rem_1fr_5rem] items-center gap-2 text-xs">
                <span class="text-gray-500">{{ $label }}</span>
                <div class="h-3 overflow-hidden rounded bg-gray-100">
                    <div class="h-full rounded bg-amber-400" style="width: {{ $maxRevenue > 0 ? max(2, ($amount / $maxRevenue) * 100) : 0 }}%"></div>
                </div>
                <span class="text-right font-medium text-gray-700 dark:text-gray-200">{{ number_format($amount, 2) }} €</span>
            </div>
        @endforeach
    </div>
</x-filament::section>
