@php($works = $this->getWorks())
<x-filament::section icon="heroicon-o-trophy" heading="Obras con más ingresos" description="Las cinco obras con mayor rendimiento acumulado">
    @if(empty($works))
        <div class="editorial-empty"><p class="editorial-muted">Aún no hay regalías para comparar.</p></div>
    @else
        <ol class="space-y-2">
            @foreach($works as $work)
                <li class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4 text-sm">
                    <span class="editorial-title">{{ $work['title'] }}</span>
                    <span class="editorial-metric font-medium text-amber-600">{{ number_format($work['revenue'], 2) }} €</span>
                </li>
            @endforeach
        </ol>
    @endif
</x-filament::section>
