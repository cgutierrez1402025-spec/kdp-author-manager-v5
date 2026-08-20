@php($chartData = $this->getChartData())
<x-filament::section id="revenue" icon="heroicon-o-chart-bar" heading="Ingresos" description="Regalías de los últimos seis periodos, sin mezclar monedas">
    @if(empty($chartData['series']))
        <p class="editorial-muted">No hay regalías registradas ni importadas para este usuario.</p>
    @else
        <div class="space-y-6" role="img" aria-label="Ingresos mensuales de los últimos seis periodos">
            @foreach($chartData['series'] as $currency => $amounts)
                @php($maxRevenue = max($amounts ?: [0]))
                <div>
                    <div class="mb-2 flex items-center justify-between text-xs">
                        <strong>{{ $currency }}</strong>
                        <span class="text-gray-500">{{ $chartData['source'] === 'kdp_report_rows' ? 'Informes KDP' : 'Regalías registradas' }}</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($chartData['labels'] as $index => $label)
                            @php($amount = $amounts[$index] ?? 0)
                            <div class="grid grid-cols-[5rem_1fr_6rem] items-center gap-2 text-xs">
                                <span class="text-gray-500">{{ $label }}</span>
                                <div class="h-3 overflow-hidden rounded bg-gray-100 dark:bg-gray-800">
                                    <div class="h-full rounded bg-amber-400" style="width: {{ $maxRevenue > 0 ? max(2, ($amount / $maxRevenue) * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-right font-medium text-gray-700 dark:text-gray-200">{{ number_format($amount, 2) }} {{ $currency }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
