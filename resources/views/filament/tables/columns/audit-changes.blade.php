@php $props = $getState(); $old = $props['old'] ?? []; $new = $props['attributes'] ?? []; @endphp
<div class="text-xs space-y-1">
    @foreach($new as $key => $value)
        @if(($old[$key] ?? null) !== $value)
            <div><span class="font-semibold text-gray-600">{{ $key }}:</span> <span class="text-red-500 line-through">{{ is_array($old[$key] ?? '') ? json_encode($old[$key]) : ($old[$key] ?? 'null') }}</span> → <span class="text-green-600">{{ is_array($value) ? json_encode($value) : $value }}</span></div>
        @endif
    @endforeach
    @if(empty($new)) <span class="text-gray-400">Sin cambios</span> @endif
</div>
