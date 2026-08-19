@props(['table', 'columns', 'row', 'action', 'method'])

<x-admin-layout>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    {{ $row ? 'Editar' : 'Crear' }} {{ str_replace('_', ' ', $table) }}
                </h1>
                <p class="text-slate-600 mt-1">
                    Completa el formulario {{ $row ? 'para actualizar' : 'para crear' }} un nuevo registro
                </p>
            </div>
            <a href="{{ route('admin.table.index', ['table' => $table]) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all duration-200 font-medium transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver a la lista
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ $action }}" method="post" class="space-y-6">
        @csrf
        @if($method === 'put')
            @method('put')
        @endif

        @php
            // Agrupar campos por categoría
            $fieldGroups = [
                'Información Básica' => ['id', 'name', 'title', 'slug', 'description'],
                'Contenido' => ['content', 'body', 'text', 'html', 'markdown'],
                'Fechas y Timestamps' => ['created_at', 'updated_at', 'deleted_at', 'date', 'published_at'],
                'Configuración' => ['active', 'status', 'enabled', 'published', 'is_active'],
                'Otros' => []
            ];
            
            // Organizar columnas por grupos
            $groupedColumns = [];
            foreach ($columns as $column) {
                $field = $column->Field;
                $placed = false;
                
                foreach ($fieldGroups as $group => $fields) {
                    if (in_array($field, $fields)) {
                        if (!isset($groupedColumns[$group])) {
                            $groupedColumns[$group] = [];
                        }
                        $groupedColumns[$group][] = $column;
                        $placed = true;
                        break;
                    }
                }
                
                if (!$placed) {
                    if (!isset($groupedColumns['Otros'])) {
                        $groupedColumns['Otros'] = [];
                    }
                    $groupedColumns['Otros'][] = $column;
                }
            }
        @endphp

        @foreach($groupedColumns as $groupName => $groupColumns)
            @if(!empty($groupColumns))
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                    <!-- Group Header -->
                    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100">
                        <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                            <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                            {{ $groupName }}
                        </h2>
                    </div>

                    <!-- Group Fields -->
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($groupColumns as $column)
                                @php
                                    $field = $column->Field;
                                    $value = old($field, $row->{$field} ?? '');
                                    $type = $column->Type;
                                    $isReadonly = in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at']);
                                    $fieldErrors = method_exists($errors, "get") ? $errors->get($field) : [];
                                    $hasError = !empty($fieldErrors);
                                    $isRequired = $column->Null === 'NO' && !$isReadonly;
                                    $fieldLabel = str_replace('_', ' ', ucfirst($field));
                                @endphp

                                <div class="form-group group">
                                    <!-- Label -->
                                    <label for="{{ $field }}" class="block text-sm font-semibold text-slate-700 mb-2.5 flex items-center gap-1">
                                        <span>{{ $fieldLabel }}</span>
                                        @if($isRequired)
                                            <span class="text-red-500 font-bold" title="Campo requerido">*</span>
                                        @elseif(!$isReadonly)
                                            <span class="text-slate-400 text-xs">(opcional)</span>
                                        @else
                                            <span class="text-slate-400 text-xs">(solo lectura)</span>
                                        @endif
                                    </label>

                                    <!-- Input Fields -->
                                    @if(in_array($field, ['created_at', 'updated_at', 'deleted_at']))
                                        <!-- Timestamp Display (readonly) -->
                                        <div class="relative group/readonly">
                                            <input type="text" 
                                                class="w-full px-4 py-2.5 rounded-lg bg-slate-100 border border-slate-300 text-slate-600 cursor-not-allowed font-medium" 
                                                value="{{ $value ? \Carbon\Carbon::parse($value)->format('d/m/Y H:i') : 'N/A' }}" 
                                                disabled>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a1 1 0 011-1h8a1 1 0 011 1v2a1 1 0 11-2 0V8H7v1a1 1 0 11-2 0zm0 0a1 1 0 011-1h8a1 1 0 011 1v2a1 1 0 11-2 0V8H7v1a1 1 0 11-2 0zM4 5a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 11-2 0V5H6v12a1 1 0 11-2 0V5z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>

                                    @elseif(\Illuminate\Support\Str::contains($type, ['longtext', 'json']))
                                        <!-- Textarea for Long Text and JSON -->
                                        <div class="relative">
                                            <textarea 
                                                name="{{ $field }}" 
                                                id="{{ $field }}" 
                                                rows="6"
                                                class="w-full px-4 py-3 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 font-mono text-sm placeholder-slate-400 focus:outline-none transition-all duration-200 {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                                {{ $isReadonly ? 'readonly' : '' }}
                                                placeholder="Ingresa el contenido aquí...">{{ $value }}</textarea>
                                            @if(\Illuminate\Support\Str::contains($type, 'json'))
                                                <div class="absolute top-2 right-2 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded opacity-75">
                                                    JSON
                                                </div>
                                            @endif
                                        </div>

                                    @elseif(\Illuminate\Support\Str::contains($type, 'text'))
                                        <!-- Textarea for Text -->
                                        <textarea 
                                            name="{{ $field }}" 
                                            id="{{ $field }}" 
                                            rows="4"
                                            class="w-full px-4 py-3 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 placeholder-slate-400 focus:outline-none transition-all duration-200 resize-vertical {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}
                                            placeholder="Ingresa el texto aquí...">{{ $value }}</textarea>

                                    @elseif(\Illuminate\Support\Str::contains($type, 'date') && !\Illuminate\Support\Str::contains($type, 'datetime'))
                                        <!-- Date Input -->
                                        <input 
                                            type="date" 
                                            name="{{ $field }}" 
                                            id="{{ $field }}" 
                                            value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '' }}"
                                            class="w-full px-4 py-2.5 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 focus:outline-none transition-all duration-200 {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}>

                                    @elseif(\Illuminate\Support\Str::contains($type, ['timestamp', 'datetime']))
                                        <!-- DateTime Input -->
                                        <input 
                                            type="datetime-local" 
                                            name="{{ $field }}" 
                                            id="{{ $field }}" 
                                            value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}"
                                            class="w-full px-4 py-2.5 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 focus:outline-none transition-all duration-200 {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}>

                                    @elseif(\Illuminate\Support\Str::contains($type, ['tinyint(1)', 'boolean', 'bit']))
                                        <!-- Toggle Switch for Boolean -->
                                        <div class="flex items-center gap-4">
                                            <label class="relative inline-flex cursor-pointer group/switch">
                                                <input 
                                                    type="hidden" 
                                                    name="{{ $field }}" 
                                                    value="0">
                                                <input 
                                                    type="checkbox" 
                                                    value="1" 
                                                    name="{{ $field }}" 
                                                    id="{{ $field }}"
                                                    class="sr-only peer" 
                                                    {{ $value ? 'checked' : '' }}
                                                    {{ $isReadonly ? 'disabled' : '' }}>
                                                <div class="w-12 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all after:duration-300 peer-checked:bg-blue-600 shadow-sm group-hover/switch:shadow-md transition-shadow {{ $isReadonly ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium {{ $value ? 'text-green-600' : 'text-slate-600' }}">
                                                    {{ $value ? '✓ Habilitado' : '✗ Deshabilitado' }}
                                                </span>
                                            </div>
                                        </div>

                                    @elseif(\Illuminate\Support\Str::contains($type, ['int', 'bigint', 'smallint', 'tinyint']))
                                        <!-- Number Input for Integers -->
                                        <input 
                                            type="number" 
                                            name="{{ $field }}" 
                                            id="{{ $field }}" 
                                            value="{{ $value }}"
                                            step="1"
                                            class="w-full px-4 py-2.5 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 placeholder-slate-400 focus:outline-none transition-all duration-200 {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}
                                            placeholder="Ingresa un número entero...">

                                    @elseif(\Illuminate\Support\Str::contains($type, ['decimal', 'float', 'double']))
                                        <!-- Number Input for Decimals -->
                                        <input 
                                            type="number" 
                                            name="{{ $field }}" 
                                            id="{{ $field }}" 
                                            value="{{ $value }}"
                                            step="0.01"
                                            class="w-full px-4 py-2.5 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 placeholder-slate-400 focus:outline-none transition-all duration-200 {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}
                                            placeholder="Ingresa un número decimal...">

                                    @else
                                        <!-- Default Text Input -->
                                        <input 
                                            type="text" 
                                            name="{{ $field }}" 
                                            id="{{ $field }}" 
                                            value="{{ $value }}"
                                            class="w-full px-4 py-2.5 rounded-lg border-2 {{ $hasError ? 'border-red-500 focus:border-red-600' : 'border-slate-300 focus:border-blue-500' }} bg-white text-slate-900 placeholder-slate-400 focus:outline-none transition-all duration-200 {{ $isReadonly ? 'bg-slate-100 text-slate-600 cursor-not-allowed opacity-75' : 'hover:border-slate-400' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}
                                            placeholder="Ingresa el valor aquí...">
                                    @endif

                                    <!-- Error Messages -->
                                    @if($hasError)
                                        <div class="mt-2.5 space-y-2 animate-slideDown">
                                            @foreach($fieldErrors as $error)
                                                <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200 backdrop-blur-sm">
                                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-sm font-medium text-red-700">{{ $error }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Help Text -->
                                    @if($isRequired)
                                        <p class="mt-1.5 text-xs font-medium text-blue-600">Campo requerido</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row gap-3 pt-8 border-t border-slate-200">
            <!-- Save Button -->
            <button type="submit" name="action" value="save" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Guardar</span>
            </button>

            <!-- Save and Return Button -->
            <button type="submit" name="action" value="save_and_return" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M7 13H3"/>
                </svg>
                <span>Guardar y volver</span>
            </button>

            <!-- Cancel Button -->
            <a href="{{ route('admin.table.index', ['table' => $table]) }}" class="flex-1 px-6 py-3 bg-slate-300 hover:bg-slate-400 text-slate-900 font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>Cancelar</span>
            </a>
        </div>
    </form>

    <!-- Add CSS Animations -->
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-light {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.3);
            }
            50% {
                box-shadow: 0 0 0 5px rgba(59, 130, 246, 0);
            }
        }

        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }

        /* Custom input number spinner styling */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Focus ring for accessibility */
        input:focus, textarea:focus, select:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        /* Readonly fields styling */
        input:disabled, textarea:disabled {
            opacity: 0.75;
        }

        /* Smooth transitions for all form elements */
        input, textarea, select {
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
    </style>
</x-admin-layout>
