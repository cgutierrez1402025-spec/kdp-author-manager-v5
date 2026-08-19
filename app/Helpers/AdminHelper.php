<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AdminHelper
{
    /**
     * Obtener la configuración de una tabla
     */
    public static function getTableConfig(string $table): array
    {
        $config = config('admin-tables');

        if (isset($config[$table])) {
            return $config[$table];
        }

        // Fallback: generar automáticamente con Str::headline()
        return self::generateFallbackConfig($table);
    }

    /**
     * Obtener el label/nombre humanizado de una tabla
     */
    public static function getTableLabel(string $table): string
    {
        return self::getTableConfig($table)['label'] ?? Str::headline($table);
    }

    /**
     * Obtener el ícono de una tabla
     */
    public static function getTableIcon(string $table): string
    {
        return self::getTableConfig($table)['icon'] ?? '📋';
    }

    /**
     * Obtener el módulo de una tabla
     */
    public static function getTableModule(string $table): string
    {
        return self::getTableConfig($table)['module'] ?? 'Otros';
    }

    /**
     * Obtener la descripción de una tabla
     */
    public static function getTableDescription(string $table): string
    {
        return self::getTableConfig($table)['description'] ?? '';
    }

    /**
     * Obtener la prioridad de una tabla
     */
    public static function getTablePriority(string $table): int
    {
        return self::getTableConfig($table)['priority'] ?? 999;
    }

    /**
     * Generar configuración fallback para tablas no registradas
     */
    private static function generateFallbackConfig(string $table): array
    {
        return [
            'label' => Str::headline($table),
            'icon' => '📋',
            'module' => 'Otros',
            'description' => 'Gestiona registros de '.Str::headline(Str::plural($table)),
            'priority' => 999,
        ];
    }

    /**
     * Obtener todas las tablas agrupadas por módulo
     */
    public static function getTablesByModule(): array
    {
        $config = config('admin-tables');
        $grouped = [];

        foreach ($config as $table => $data) {
            $module = $data['module'] ?? 'Otros';
            if (! isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][$table] = $data;
        }

        return $grouped;
    }

    /**
     * Obtener todas las tablas ordenadas por prioridad
     */
    public static function getTablesSorted(): array
    {
        $config = config('admin-tables');
        $tables = [];

        foreach ($config as $table => $data) {
            if (! isset($tables[$table])) {
                $tables[$table] = $data;
            }
        }

        usort($tables, function ($a, $b) {
            return ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999);
        });

        return $tables;
    }

    /**
     * Traducir nombre de campo a nombre humanizado
     */
    public static function getFieldLabel(string $field): string
    {
        $fieldTranslations = [
            'id' => 'ID',
            'created_at' => 'Creado en',
            'updated_at' => 'Actualizado en',
            'deleted_at' => 'Eliminado en',
            'name' => 'Nombre',
            'title' => 'Título',
            'description' => 'Descripción',
            'email' => 'Correo',
            'password' => 'Contraseña',
            'active' => 'Activo',
            'status' => 'Estado',
            'published' => 'Publicado',
            'price' => 'Precio',
            'quantity' => 'Cantidad',
            'total' => 'Total',
            'amount' => 'Monto',
            'date' => 'Fecha',
            'start_date' => 'Fecha de inicio',
            'end_date' => 'Fecha final',
            'author' => 'Autor',
            'isbn' => 'ISBN',
            'pages' => 'Páginas',
            'language' => 'Idioma',
            'category' => 'Categoría',
            'slug' => 'Identificador URL',
            'code' => 'Código',
            'type' => 'Tipo',
            'format' => 'Formato',
            'version' => 'Versión',
            'order' => 'Orden',
            'sort' => 'Ordenamiento',
            'enabled' => 'Habilitado',
            'visible' => 'Visible',
            'featured' => 'Destacado',
            'is_active' => 'Está activo',
            'is_deleted' => 'Está eliminado',
            'user_id' => 'Usuario',
            'author_id' => 'Autor',
            'category_id' => 'Categoría',
            'parent_id' => 'Elemento padre',
        ];

        return $fieldTranslations[$field] ?? Str::headline($field);
    }
}
