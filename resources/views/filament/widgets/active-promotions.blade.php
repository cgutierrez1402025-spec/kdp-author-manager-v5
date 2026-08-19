@php($promotions = $this->getPromotionsProperty())
<x-filament::section icon="heroicon-o-megaphone" heading="Promociones activas" description="Coste, retorno y fechas de las campañas en curso">
<div class="grid gap-3 md:grid-cols-2">
    @if(empty($promotions))
        <div class="editorial-empty"><p class="editorial-muted">No hay promociones activas.</p></div>
    @else
        @foreach($promotions as $promotion)
            <div class="rounded-xl border border-gray-200 p-3 dark:border-white/10">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">
                            {{ $promotion['promotion_name'] ?? 'Untitled' }}
                        </h4>
                        <p class="text-sm text-gray-500">{{ $promotion['work_title'] ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-400">{{ $promotion['marketplace'] ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-semibold {{ $promotion['roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $promotion['roi'] }}% ROI
                        </span>
                        <p class="text-xs text-gray-500">
                            {{ number_format($promotion['total_revenue'], 2) }} € / {{ number_format($promotion['total_cost'], 2) }} €
                        </p>
                    </div>
                </div>
                @if($promotion['end_date'])
                    <p class="text-xs text-gray-400 mt-2">
                        Finaliza: {{ \Carbon\Carbon::parse($promotion['end_date'])->locale('es')->translatedFormat('d M Y') }}
                    </p>
                @endif
            </div>
        @endforeach
    @endif
</div>
</x-filament::section>
