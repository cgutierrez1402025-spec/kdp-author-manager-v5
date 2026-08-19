<div class="space-y-4">
    @if(isset($result['success']) && $result['success'])
        <div class="p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Vista Previa del HTML Modificado:</h3>
            <div class="text-sm text-gray-700 max-h-96 overflow-y-auto border border-gray-200 rounded p-2">
                {!! $result['html'] ?? '' !!}
            </div>
        </div>

        <div class="text-xs text-gray-500">
            <span class="font-medium">Ilustración:</span> {{ $anchor->illustration->title ?? 'N/A' }}
        </div>

        <div class="text-xs text-gray-500">
            <span class="font-medium">Tipo de Anclaje:</span> {{ $anchor->anchor_type ?? 'N/A' }}
        </div>

        <div class="text-xs text-gray-500 break-all">
            <span class="font-medium">Etiqueta IMG generada:</span>
            <code class="text-xs">{{ $result['image_tag'] ?? '' }}</code>
        </div>
    @elseif(isset($result['error']))
        <div class="p-4 bg-red-50 rounded-lg">
            <p class="text-sm text-red-600">Error: {{ $result['error'] }}</p>
        </div>
    @endif
</div>