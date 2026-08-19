@foreach($versions as $version)
    @php
        $dotClass = match ($version['status']) {
            'published' => 'bg-success-500',
            'final' => 'bg-info-500',
            'review' => 'bg-warning-500',
            default => 'bg-gray-400',
        };
    @endphp
    <div role="treeitem" aria-level="{{ $version['level'] + 1 }}" class="relative flex items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-white/10" style="margin-left: {{ min($version['level'], 6) * 20 }}px">
        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
        <span class="font-semibold text-gray-900 dark:text-white">v{{ $version['version_number'] }}</span>
        @if($version['is_published'])
            <span class="rounded-full bg-success-50 px-2 py-0.5 text-xs text-success-700 dark:bg-success-500/10 dark:text-success-300">Publicada</span>
        @elseif($version['is_final'])
            <span class="rounded-full bg-info-50 px-2 py-0.5 text-xs text-info-700 dark:bg-info-500/10 dark:text-info-300">Final</span>
        @else
            <span class="text-xs capitalize text-gray-500">{{ $version['status'] }}</span>
        @endif
        <span class="ml-auto text-xs text-gray-500">{{ $version['created_at']->format('d/m/Y') }}</span>
    </div>
    @if(!empty($version['children']))
        @include('filament.widgets.version-tree-recursive', ['versions' => $version['children']])
    @endif
@endforeach
