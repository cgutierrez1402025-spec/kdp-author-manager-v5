@props(['tablesData', 'summary'])

@php
    $colorMap = [
        'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
        'purple' => 'border-purple-200 bg-purple-50 text-purple-700',
        'green' => 'border-green-200 bg-green-50 text-green-700',
        'yellow' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
        'orange' => 'border-orange-200 bg-orange-50 text-orange-700',
        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'slate' => 'border-slate-200 bg-slate-50 text-slate-700',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'cyan' => 'border-cyan-200 bg-cyan-50 text-cyan-700',
        'violet' => 'border-violet-200 bg-violet-50 text-violet-700',
        'lime' => 'border-lime-200 bg-lime-50 text-lime-700',
        'pink' => 'border-pink-200 bg-pink-50 text-pink-700',
        'teal' => 'border-teal-200 bg-teal-50 text-teal-700',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
        'fuchsia' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700',
        'stone' => 'border-stone-200 bg-stone-50 text-stone-700',
    ];
@endphp

<x-admin-layout>
    <div class="app-container">
        <div class="app-header">
            <div class="logo-area">
                <h1>📚 KDP Author Manager</h1>
                <p>Gestió completa · Totes les taules de la BD</p>
            </div>
            <div class="user-info">
                Carlos Gutiérrez
                <div class="avatar">CG</div>
            </div>
        </div>

        <nav class="main-nav">
            @foreach($tablesData as $key => $data)
                <button class="nav-btn {{ $loop->first ? 'active' : '' }}" data-section="{{ $key }}">
                    {{ $data['icon'] }} {{ $data['label'] }}
                </button>
            @endforeach
        </nav>

        <div class="main-content">
            @if(isset($summary))
                <div id="dashboard" class="section active-section">
                    <div class="split-panel">
                        <div class="info-panel">
                            <h3>📊 Resum d'entitats</h3>
                            <ul>
                                @foreach($summary as $label => $count)
                                    <li>{{ $label }}: {{ $count }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="info-panel">
                            <h3>⚡ Accions ràpides</h3>
                            <a href="{{ route('admin.table.create', ['table' => 'works']) }}" class="btn-primary" style="display:inline-block;margin-bottom:8px;">➕ Nova obra</a><br>
                            <a href="{{ route('admin.table.create', ['table' => 'publications']) }}" class="btn-secondary" style="display:inline-block;">📱 Nova publicació</a>
                        </div>
                    </div>
                    <div class="table-ref">✅ Taules cobertes: resum de totes les entitats</div>
                </div>
            @endif

            @foreach($tablesData as $key => $data)
                @if($key === 'informes')
                    <div id="informes" class="section">
                        <div class="section-title">📊 Informes globals</div>
                        <div class="cards-grid">
                            <div class="feature-card"><div class="card-icon">📈</div><div class="card-title">Vendes per obra</div><div>Resum regalies</div></div>
                            <div class="feature-card"><div class="card-icon">🌍</div><div class="card-title">Rendiment per mercat</div><div>US, ES, UK, DE</div></div>
                            <div class="feature-card"><div class="card-icon">🎯</div><div class="card-title">ROI promocions</div><div>Cost vs regalies generades</div></div>
                            <div class="feature-card"><div class="card-icon">🤖</div><div class="card-title">Ús IA per obra</div><div>Prompts i eines utilitzades</div></div>
                            <div class="feature-card"><div class="card-icon">📚</div><div class="card-title">Producció per gènere</div><div>Obres, paraules, publicacions</div></div>
                            <div class="feature-card"><div class="card-icon">🏆</div><div class="card-title">Premis i reconeixements</div><div>Presentacions i resultats</div></div>
                        </div>
                        <div class="table-ref">📌 Informes que agreguen dades de múltiples taules.</div>
                    </div>
                @elseif(isset($data['table'], $data['columns'], $data['rows']))
                    <div id="{{ $key }}" class="section">
                        <div class="section-title">{{ $data['icon'] }} {{ $data['label'] }}</div>
                        <div class="split-panel">
                            <div class="info-panel">
                                <h3>➕ Nou{{ $data['label'] }}</h3>
                                <form method="post" action="{{ route('admin.table.store', ['table' => $data['table']]) }}">
                                    @csrf
                                    @foreach($data['formFields'] as $field)
                                        <div class="form-group">
                                            <label>{{ $field['label'] }}</label>
                                            @if($field['type'] === 'select')
                                                <select name="{{ $field['name'] }}">
                                                    <option value="">--</option>
                                                    @foreach($field['options'] as $value => $option)
                                                        <option value="{{ $value }}">{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($field['type'] === 'textarea')
                                                <textarea name="{{ $field['name'] }}" rows="2"></textarea>
                                            @else
                                                <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" placeholder="{{ $field['label'] }}">
                                            @endif
                                        </div>
                                    @endforeach
                                    <button type="submit" class="btn-primary">Crear</button>
                                </form>
                            </div>
                            <div>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            @foreach($data['columns'] as $column)
                                                <th>{{ ucwords(str_replace('_', ' ', $column->Field)) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['rows'] as $row)
                                            <tr>
                                                @foreach($data['columns'] as $column)
                                                    <td>{{ $row->{$column->Field} }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        @if($data['rows']->isEmpty())
                                            <tr><td colspan="{{ count($data['columns']) }}">No hi ha registres</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="table-ref">📌 Taula: <code>{{ $data['table'] }}</code></div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="app-footer">✅ Totes les taules de la base de dades KDP en castellà tenen representació en esta interfície. · DAM 2025-2026</div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => section.classList.remove('active-section'));
            const activeSection = document.getElementById(sectionId);
            if (activeSection) activeSection.classList.add('active-section');
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-section') === sectionId) btn.classList.add('active');
            });
        }
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const sectionId = btn.getAttribute('data-section');
                if (sectionId) showSection(sectionId);
            });
        });
        showSection('dashboard');
    </script>
</x-admin-layout>
