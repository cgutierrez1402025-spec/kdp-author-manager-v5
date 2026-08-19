<x-filament::section icon="heroicon-o-share" heading="Historial de versiones" description="Origen y derivaciones del manuscrito seleccionado">
    @if(empty($versions))
        <div class="editorial-empty">
            <p class="editorial-muted">No hay versiones relacionadas.</p>
        </div>
    @else
        <div class="space-y-2" role="tree" aria-label="Árbol de versiones del manuscrito">
            @include('filament.widgets.version-tree-recursive', ['versions' => $versions])
        </div>
    @endif
</x-filament::section>
