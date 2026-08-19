@php($works = $this->getWorks())
<x-filament::section icon="heroicon-o-trophy" heading="Obras con más ingresos" description="Todas las obras con ingresos, ordenadas de mayor a menor">
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
        <div class="mt-4 flex justify-end border-t border-gray-100 pt-3 dark:border-white/5">
            <a class="text-sm font-semibold text-primary-600 hover:underline dark:text-primary-400" href="{{ \App\Filament\Admin\Resources\Works\WorkResource::getUrl('index') }}">Ver todas las obras →</a>
        </div>
    @endif
</x-filament::section>
