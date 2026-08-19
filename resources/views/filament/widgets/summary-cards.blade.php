@php
    $stats = $this->getStats();
    $cards = [
        'total_works' => ['label' => 'Obras', 'icon' => 'heroicon-o-book-open', 'url' => '/admin/works', 'color' => 'text-violet-600 bg-violet-50 dark:bg-violet-500/10 dark:text-violet-300'],
        'monthly_revenue' => ['label' => 'Ingresos del mes', 'icon' => 'heroicon-o-banknotes', 'url' => '#revenue', 'color' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-300'],
        'active_publications' => ['label' => 'Publicaciones activas', 'icon' => 'heroicon-o-globe-alt', 'url' => '/admin/publications?tableFilters[status][value]=published', 'color' => 'text-blue-600 bg-blue-50 dark:bg-blue-500/10 dark:text-blue-300'],
        'active_promotions' => ['label' => 'Promociones activas', 'icon' => 'heroicon-o-megaphone', 'url' => '/admin/book-promotions', 'color' => 'text-amber-600 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-300'],
    ];
@endphp
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores principales">
    @foreach($cards as $key => $card)
        <a href="{{ $card['url'] }}" class="editorial-card group flex min-h-32 items-start justify-between p-5 transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md dark:hover:border-primary-500/50">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $key === 'monthly_revenue' ? number_format($stats[$key], 2).' €' : number_format($stats[$key]) }}
                </p>
                @if($key === 'monthly_revenue' && $stats['revenue_change'] !== null)
                    <p class="mt-1 text-xs font-medium {{ $stats['revenue_change'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $stats['revenue_change'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($stats['revenue_change']), 1) }}% frente al mes anterior
                    </p>
                @else
                    <p class="mt-1 text-xs text-gray-400">Abrir detalle →</p>
                @endif
            </div>
            <span class="rounded-xl p-3 {{ $card['color'] }}">
                <x-filament::icon :icon="$card['icon']" class="h-6 w-6" />
            </span>
        </a>
    @endforeach
</div>
