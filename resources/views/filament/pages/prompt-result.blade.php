<div class="space-y-4">
    @if(isset($result['success']) && $result['success'])
        <div class="p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Resultado:</h3>
            <div class="text-sm text-gray-700 whitespace-pre-wrap max-h-96 overflow-y-auto">
                {!! $result['result'] ?? '' !!}
            </div>
        </div>

        @if(!empty($prompt->purpose))
            <div class="text-xs text-gray-500">
                <span class="font-medium">Propósito:</span> {{ $prompt->purpose }}
            </div>
        @endif
    @elseif(isset($result['error']))
        <div class="p-4 bg-red-50 rounded-lg">
            <p class="text-sm text-red-600">{{ $result['error'] }}</p>
        </div>
    @endif

    <div class="text-xs text-gray-500">
        <span class="font-medium">Herramienta:</span> {{ $prompt->aiTool->name ?? 'N/A' }}
    </div>
</div>