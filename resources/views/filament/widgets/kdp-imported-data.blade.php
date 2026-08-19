@php
    $data = $this->getDashboardData();
    $maxTitleUnits = max(array_column($data['top_titles'], 'units') ?: [0]);
    $maxKenp = max(array_column($data['kenp_titles'], 'pages') ?: [0]);
    $maxMarketplace = max(array_column($data['marketplaces'], 'units') ?: [0]);
    $maxDaily = max(array_column($data['daily_units'], 'units') ?: [0]);
@endphp

<x-filament::section
    icon="heroicon-o-chart-bar-square"
    heading="Rendimiento importado desde Amazon KDP"
    description="Visualización directa de los archivos CSV/XLSX cargados. Las regalías se mantienen separadas por moneda."
>
    @if(! $data['has_data'])
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
            <p class="font-medium text-gray-700 dark:text-gray-200">Aún no hay datos KDP importados.</p>
            <a class="mt-3 inline-block text-sm font-semibold text-primary-600 hover:underline" href="/admin/importaciones-kdp/create">Cargar un informe KDP →</a>
        </div>
    @else
        @if($data['latest_batch'])
            <div class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm dark:bg-white/5">
                <span>
                    Última carga: <strong>{{ $data['latest_batch']->report_period?->format('m/Y') ?? 'sin periodo' }}</strong>
                    · {{ $data['latest_batch']->imported_rows }} filas válidas
                    · {{ $data['latest_batch']->error_rows }} errores
                </span>
                <a class="font-semibold text-primary-600 hover:underline" href="/admin/importaciones-kdp">Ver importaciones →</a>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Unidades netas</p>
                <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['total_units']) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Páginas KENP</p>
                <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['total_kenp']) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">ASIN distintos</p>
                <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['titles']) }}</p>
            </div>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-2">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">Unidades por día</h3>
                <div class="mt-4 flex h-40 items-end gap-2" role="img" aria-label="Unidades netas por día">
                    @forelse($data['daily_units'] as $point)
                        <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-medium">{{ $point['units'] }}</span>
                            <div class="w-full rounded-t bg-primary-500" style="height: {{ $maxDaily > 0 ? max(4, $point['units'] / $maxDaily * 110) : 0 }}px"></div>
                            <span class="text-[10px] text-gray-500">{{ $point['date'] }}</span>
                        </div>
                    @empty
                        <p class="self-center text-sm text-gray-500">El informe no contiene fechas diarias normalizadas.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">Regalías por moneda</h3>
                <p class="mt-1 text-xs text-gray-500">No se suman monedas diferentes sin un tipo de cambio.</p>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @forelse($data['revenue_by_currency'] as $revenue)
                        <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-500/10">
                            <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $revenue['currency'] }}</p>
                            <p class="text-lg font-bold text-emerald-900 dark:text-emerald-100">{{ number_format($revenue['total'], 2) }}</p>
                        </div>
                    @empty
                        <p class="col-span-full text-sm text-gray-500">No hay regalías monetarias en los informes cargados.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">Títulos por unidades netas</h3>
                <div class="mt-4 space-y-3">
                    @foreach($data['top_titles'] as $title)
                        <div>
                            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 text-xs"><span class="editorial-title" title="{{ $title['title'] }}">{{ $title['title'] }}</span><strong class="editorial-metric">{{ $title['units'] }}</strong></div>
                            <div class="mt-1 h-2 overflow-hidden rounded bg-gray-100 dark:bg-gray-800"><div class="h-full rounded bg-violet-500" style="width: {{ $maxTitleUnits > 0 ? $title['units'] / $maxTitleUnits * 100 : 0 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">Lectura Kindle Unlimited por título</h3>
                <div class="mt-4 space-y-3">
                    @forelse($data['kenp_titles'] as $title)
                        <div>
                            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 text-xs"><span class="editorial-title" title="{{ $title['title'] }}">{{ $title['title'] }}</span><strong class="editorial-metric">{{ number_format($title['pages']) }}</strong></div>
                            <div class="mt-1 h-2 overflow-hidden rounded bg-gray-100 dark:bg-gray-800"><div class="h-full rounded bg-sky-500" style="width: {{ $maxKenp > 0 ? $title['pages'] / $maxKenp * 100 : 0 }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No hay páginas KENP en los informes cargados.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 xl:col-span-2">
                <h3 class="font-semibold text-gray-950 dark:text-white">Unidades por marketplace</h3>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach($data['marketplaces'] as $marketplace)
                        <div>
                            <div class="flex justify-between text-xs"><span>{{ $marketplace['marketplace'] }}</span><strong>{{ $marketplace['units'] }}</strong></div>
                            <div class="mt-1 h-2 overflow-hidden rounded bg-gray-100 dark:bg-gray-800"><div class="h-full rounded bg-amber-500" style="width: {{ $maxMarketplace > 0 ? $marketplace['units'] / $maxMarketplace * 100 : 0 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-filament::section>
