<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminCrudController extends Controller
{
    public function dashboard()
    {
        $tables = collect($this->getTables())
            ->filter(fn (string $table) => $table !== 'migrations')
            ->sort()
            ->values();

        $sectionMap = [
            'dashboard' => ['label' => 'Tauler', 'icon' => '🏠', 'color' => 'blue'],
            'usuaris' => ['label' => 'Usuaris', 'icon' => '👥', 'color' => 'blue', 'table' => 'users'],
            'obres' => ['label' => 'Obres', 'icon' => '📖', 'color' => 'purple', 'table' => 'works'],
            'series' => ['label' => 'Series', 'icon' => '📚', 'color' => 'indigo', 'table' => 'series'],
            'idiomes' => ['label' => 'Idiomes obra', 'icon' => '🌐', 'color' => 'green', 'table' => 'work_language'],
            'edicions' => ['label' => 'Edicions', 'icon' => '📘', 'color' => 'orange', 'table' => 'editions'],
            'manuscrits' => ['label' => 'Manuscrits', 'icon' => '✍️', 'color' => 'yellow', 'table' => 'manuscript_versions'],
            'capitols' => ['label' => 'Capítols', 'icon' => '📑', 'color' => 'slate', 'table' => 'chapters'],
            'fonts' => ['label' => 'Fonts', 'icon' => '📎', 'color' => 'blue', 'table' => 'sources'],
            'ia' => ['label' => 'IA i Prompts', 'icon' => '🤖', 'color' => 'indigo', 'table' => 'ai_tools'],
            'ilustracions' => ['label' => 'Il·lustracions', 'icon' => '🖼️', 'color' => 'pink', 'table' => 'illustrations'],
            'plataformes' => ['label' => 'Plataformes', 'icon' => '🏪', 'color' => 'emerald', 'table' => 'platforms'],
            'mercats' => ['label' => 'Mercats', 'icon' => '🌍', 'color' => 'teal', 'table' => 'markets'],
            'publicacions' => ['label' => 'Publicacions', 'icon' => '📱', 'color' => 'cyan', 'table' => 'publications'],
            'metadades' => ['label' => 'Metadades KDP', 'icon' => '🏷️', 'color' => 'violet', 'table' => 'kdp_metadata'],
            'regalies' => ['label' => 'Regalies', 'icon' => '💰', 'color' => 'green', 'table' => 'royalties'],
            'pagaments' => ['label' => 'Pagaments', 'icon' => '💵', 'color' => 'lime', 'table' => 'payments'],
            'promocions' => ['label' => 'Promocions', 'icon' => '🎯', 'color' => 'rose', 'table' => 'promotions'],
            'premis' => ['label' => 'Premis', 'icon' => '🏆', 'color' => 'amber', 'table' => 'awards'],
            'events' => ['label' => 'Events', 'icon' => '🎪', 'color' => 'fuchsia', 'table' => 'events'],
            'distribucio' => ['label' => 'Distribució física', 'icon' => '📦', 'color' => 'orange', 'table' => 'distribution_points'],
            'stock' => ['label' => 'Stock', 'icon' => '📊', 'color' => 'sky', 'table' => 'stock_movements'],
            'tasques' => ['label' => 'Tasques', 'icon' => '✅', 'color' => 'emerald', 'table' => 'tasks'],
            'comentaris' => ['label' => 'Comentaris', 'icon' => '💬', 'color' => 'blue', 'table' => 'comments'],
            'etiquetes' => ['label' => 'Etiquetes', 'icon' => '🏷️', 'color' => 'purple', 'table' => 'tags'],
            'auditoria' => ['label' => 'Auditoria', 'icon' => '🔍', 'color' => 'slate', 'table' => 'audit_logs'],
            'importacions' => ['label' => 'Importacions', 'icon' => '📂', 'color' => 'stone', 'table' => 'import_batches'],
            'informes' => ['label' => 'Informes', 'icon' => '📊', 'color' => 'blue'],
        ];

        $dashboardData = [];
        foreach ($sectionMap as $key => $config) {
            $dashboardData[$key] = [
                'label' => $config['label'],
                'icon' => $config['icon'],
                'color' => $config['color'] ?? 'slate',
                'table' => $config['table'] ?? null,
            ];

            if (! empty($config['table']) && in_array($config['table'], $tables->toArray(), true)) {
                $table = $config['table'];
                $columns = $this->getColumns($table);
                $primaryKey = $this->getPrimaryKey($table);
                $rows = collect(DB::table($table)->limit(5)->get());
                $count = DB::table($table)->count();

                $formFields = [];
                $fieldCount = 0;
                foreach ($columns as $column) {
                    if ($fieldCount >= 5) {
                        break;
                    }
                    if (in_array($column->Field, ['id', 'created_at', 'updated_at', 'deleted_at'], true) || $column->Key === 'PRI') {
                        continue;
                    }

                    $type = Str::lower($column->Type);
                    $field = [
                        'name' => $column->Field,
                        'label' => ucwords(str_replace('_', ' ', $column->Field)),
                        'type' => 'text',
                    ];

                    if (Str::contains($type, ['int', 'bigint', 'smallint', 'tinyint', 'mediumint', 'decimal', 'float', 'double', 'real', 'numeric'])) {
                        $field['type'] = 'number';
                    } elseif (Str::contains($type, ['date', 'time', 'datetime', 'timestamp'])) {
                        $field['type'] = 'date';
                    } elseif (Str::contains($type, ['text', 'mediumtext', 'longtext'])) {
                        $field['type'] = 'textarea';
                    } elseif (Str::contains($type, ['tinyint(1)', 'boolean', 'bit'])) {
                        $field['type'] = 'select';
                        $field['options'] = [0 => 'No', 1 => 'Sí'];
                    }

                    $formFields[] = $field;
                    $fieldCount++;
                }

                $dashboardData[$key]['columns'] = $columns;
                $dashboardData[$key]['rows'] = $rows;
                $dashboardData[$key]['count'] = $count;
                $dashboardData[$key]['formFields'] = $formFields;
                $dashboardData[$key]['primaryKey'] = $primaryKey;
            }
        }

        $summary = [
            'Usuaris' => $dashboardData['usuaris']['count'] ?? 0,
            'Obres' => $dashboardData['obres']['count'] ?? 0,
            'Publicacions' => $dashboardData['publicacions']['count'] ?? 0,
            'Regalies registrades' => $dashboardData['regalies']['count'] ?? 0,
            'Promocions actives' => $dashboardData['promocions']['count'] ?? 0,
        ];

        return view('admin.dashboard', [
            'tablesData' => $dashboardData,
            'summary' => $summary,
        ]);
    }

    public function index(Request $request, string $table)
    {
        $this->ensureTableExists($table);

        $columns = $this->getColumns($table);
        $query = DB::table($table);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($builder) use ($columns, $search) {
                foreach ($columns as $column) {
                    if ($this->isSearchableColumn($column)) {
                        $builder->orWhere($column->Field, 'like', "%{$search}%");
                    }
                }
            });
        }

        foreach ($request->input('filter', []) as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        $rows = $query->paginate(15)->withQueryString();
        $primaryKey = $this->getPrimaryKey($table);
        $filters = $this->getFilterableColumns($columns);

        return view('admin.table-index', [
            'table' => $table,
            'columns' => $columns,
            'rows' => $rows,
            'primaryKey' => $primaryKey,
            'filters' => $filters,
            'search' => $request->input('q', ''),
        ]);
    }

    public function create(string $table)
    {
        $this->ensureTableExists($table);
        $columns = $this->getColumns($table);

        return view('admin.form', [
            'table' => $table,
            'columns' => $columns,
            'row' => null,
            'action' => route('admin.table.store', ['table' => $table]),
            'method' => 'post',
        ]);
    }

    public function store(Request $request, string $table)
    {
        $this->ensureTableExists($table);
        $columns = $this->getColumns($table);

        $data = $this->normalizeRequestData($request, $columns);

        try {
            $id = DB::table($table)->insertGetId($data);

            return redirect()->route('admin.table.edit', ['table' => $table, 'key' => $this->encodeKey(['id' => $id])])
                ->with('success', "Registro creado en {$table}.");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al crear registro: '.$e->getMessage())
                ->withInput();
        }
    }

    public function edit(string $table, string $key)
    {
        $this->ensureTableExists($table);
        $columns = $this->getColumns($table);
        $primary = $this->decodeKey($key);

        $row = DB::table($table)->where($primary)->first();
        abort_if(! $row, 404);

        return view('admin.form', [
            'table' => $table,
            'columns' => $columns,
            'row' => $row,
            'action' => route('admin.table.update', ['table' => $table, 'key' => $key]),
            'method' => 'put',
        ]);
    }

    public function update(Request $request, string $table, string $key)
    {
        $this->ensureTableExists($table);
        $columns = $this->getColumns($table);
        $primary = $this->decodeKey($key);

        $data = $this->normalizeRequestData($request, $columns);

        try {
            DB::table($table)->where($primary)->update($data);

            return redirect()->route('admin.table.edit', ['table' => $table, 'key' => $key])
                ->with('success', "Registro actualizado en {$table}.");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al actualizar registro: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy(string $table, string $key)
    {
        $this->ensureTableExists($table);
        $primary = $this->decodeKey($key);

        try {
            DB::table($table)->where($primary)->delete();

            return redirect()->route('admin.table.index', ['table' => $table])
                ->with('success', "Registro eliminado de {$table}.");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al eliminar registro: '.$e->getMessage());
        }
    }

    protected function ensureTableExists(string $table): void
    {
        abort_if(! Schema::hasTable($table), 404, "Tabla {$table} no encontrada.");
    }

    protected function getTables(): array
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
                ->map(fn ($row) => $row->name)
                ->all();
        }

        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->all();
    }

    protected function getColumns(string $table): array
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA table_info('{$table}')"))
                ->map(fn ($column) => (object) [
                    'Field' => $column->name,
                    'Type' => $column->type,
                    'Null' => $column->notnull === 0 ? 'YES' : 'NO',
                    'Key' => $column->pk ? 'PRI' : '',
                    'Column_name' => $column->name,
                ])
                ->all();
        }

        return DB::select("SHOW FULL COLUMNS FROM `{$table}`");
    }

    protected function getPrimaryKey(string $table): array
    {
        if (DB::getDriverName() === 'sqlite') {
            $columns = collect(DB::select("PRAGMA table_info('{$table}')"));
            $keys = $columns->filter(fn ($column) => $column->pk > 0)->pluck('name')->all();

            return ! empty($keys) ? $keys : ['id'];
        }

        $keys = DB::select("SHOW Keys FROM `{$table}` WHERE Key_name = 'PRIMARY'");
        if (empty($keys)) {
            return ['id'];
        }

        return array_map(fn ($row) => $row->Column_name, $keys);
    }

    protected function normalizeRequestData(Request $request, array $columns): array
    {
        $data = [];
        foreach ($columns as $column) {
            if (in_array($column->Field, ['id', 'created_at', 'updated_at', 'deleted_at'], true) || $column->Key === 'PRI') {
                continue;
            }

            $value = $request->input($column->Field);

            if ($value === '' || $value === null) {
                if ($column->Null === 'YES') {
                    $data[$column->Field] = null;
                }

                continue;
            }

            if ($this->isBooleanColumn($column)) {
                $data[$column->Field] = $request->has($column->Field) ? 1 : 0;
            } elseif ($this->isNumericColumn($column)) {
                $data[$column->Field] = is_numeric($value) ? $value : (empty($value) ? null : $value);
            } elseif ($this->isDateColumn($column)) {
                $data[$column->Field] = ! empty($value) ? $value : null;
            } else {
                $data[$column->Field] = (string) $value;
            }
        }

        return $data;
    }

    protected function isBooleanColumn($column): bool
    {
        $type = Str::lower($column->Type);

        return Str::contains($type, ['tinyint(1)', 'boolean', 'bit']);
    }

    protected function isNumericColumn($column): bool
    {
        $type = Str::lower($column->Type);

        return Str::contains($type, ['int', 'bigint', 'smallint', 'tinyint', 'mediumint', 'decimal', 'float', 'double', 'real', 'numeric']);
    }

    protected function isDateColumn($column): bool
    {
        $type = Str::lower($column->Type);

        return Str::contains($type, ['date', 'time', 'datetime', 'timestamp']);
    }

    protected function isSearchableColumn($column): bool
    {
        $type = Str::lower($column->Type);

        return Str::contains($type, ['char', 'text', 'varchar']);
    }

    protected function getFilterableColumns(array $columns): array
    {
        return array_values(array_filter($columns, fn ($column) => $this->isSearchableColumn($column) && ! in_array($column->Field, ['created_at', 'updated_at', 'deleted_at'], true)));
    }

    protected function encodeKey(array $primaryKey): string
    {
        $encoded = base64_encode(json_encode($primaryKey));

        return rtrim(strtr($encoded, '+/', '-_'), '=');
    }

    protected function decodeKey(string $encoded): array
    {
        $decoded = base64_decode(strtr($encoded, '-_', '+/'));

        return json_decode($decoded, true) ?: [];
    }
}
