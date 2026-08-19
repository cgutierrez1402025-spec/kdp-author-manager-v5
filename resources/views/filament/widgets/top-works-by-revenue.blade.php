@php($works = $this->getWorks())
<x-filament::section icon="heroicon-o-trophy" heading="Obras con más ingresos" description="Las cinco obras con mayor rendimiento acumulado">
    @if(empty($works))
        <div class="editorial-empty"><p class="editorial-muted">Aún no hay regalías para comparar.</p></div>
    @else
        <ol class="space-y-2">
            @foreach($works as $work)
                <li class="flex justify-between text-sm">
                    <span class="text-gray-900 dark:text-white">{{ $work['title'] }}</span>
                    <span class="font-medium text-amber-600">{{ number_format($work['revenue'], 2) }} €</span>
                </li>
            @endforeach
        </ol>
    @endif
</x-filament::section>
