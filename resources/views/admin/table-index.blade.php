@props(['table', 'columns', 'rows', 'primaryKey', 'filters', 'search'])

<x-admin-layout>
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">{{ str_replace('_', ' ', $table) }}</h1>
                <p class="text-slate-600 mt-1">Lista de registros con búsqueda global y filtros.</p>
            </div>
            <a href="{{ route('admin.table.create', ['table' => $table]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuevo registro
            </a>
        </div>
    </div>

    <form method="get" action="{{ route('admin.table.index', ['table' => $table]) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="col-span-1">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Buscar</label>
            <input type="search" name="q" value="{{ old('q', $search) }}" class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none transition-all duration-200" placeholder="Buscar texto...">
        </div>
        @foreach($filters as $filter)
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ str_replace('_', ' ', $filter->Field) }}</label>
                <input type="text" name="filter[{{ $filter->Field }}]" value="{{ request()->input('filter.' . $filter->Field) }}" class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none transition-all duration-200">
            </div>
        @endforeach
        <div class="flex items-end">
            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 w-full">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filtrar
            </button>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    @foreach($columns as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $column->Field }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($rows as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        @foreach($columns as $column)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $row->{$column->Field} }}</td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @php
                                $primaryValues = [];
                                foreach ($primaryKey as $pk) {
                                    $primaryValues[$pk] = $row->{$pk};
                                }
                                $encodedKey = rtrim(strtr(base64_encode(json_encode($primaryValues)), '+/', '-_'), '=');
                            @endphp
                            <a href="{{ route('admin.table.edit', ['table' => $table, 'key' => $encodedKey]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md transition-colors">Editar</a>
                            <form action="{{ route('admin.table.destroy', ['table' => $table, 'key' => $encodedKey]) }}" method="post" class="inline-block" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-md transition-colors">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="px-6 py-4 text-center text-sm text-slate-500">No se encontraron registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $rows->links() }}
    </div>
</x-admin-layout>