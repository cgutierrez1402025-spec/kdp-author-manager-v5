# Arquitectura y modelo de datos

## Plataforma

- PHP 8.2+, Laravel 12;
- Filament 3 y Livewire 3 para administración;
- SQLite en local y compatibilidad con MySQL mediante configuración;
- Vite y Tailwind para recursos web;
- colas Laravel para trabajos existentes de exportación y sincronización.

## Capas

- `app/Filament/Admin`: navegación, formularios, tablas, páginas y widgets.
- `app/Models`: entidades Eloquent, relaciones y conversiones.
- `app/Services`: reglas editoriales, analítica, importación KDP e IA.
- `app/Jobs`: procesos diferidos y exportaciones.
- `app/Policies` y `ScopesAuthorOwnedRecords`: autorización y aislamiento.
- `database/migrations`: definición autoritativa del esquema.
- `database/seeders`: datos demostrativos coherentes.

## Dominios principales

### Catálogo

`users → works → work_languages → manuscript_versions → chapters`. Las ediciones y versiones permiten conservar evolución editorial sin sobrescribir el manuscrito publicado.

### Publicación

`works → publications → kdp_metadata / kdp_select_periods`. Una publicación pertenece a plataforma y opcionalmente marketplace, formato y versión de manuscrito.

### Finanzas y marketing

`royalty_entries`, `royalty_payments`, `payment_thresholds`, `book_promotions`, `promotion_costs` y `promotion_daily_results` sustentan los widgets históricos y ROI.

### Importación KDP

`users → import_batches → kdp_report_rows` constituye la fuente detallada nueva. `kdp_catalog_items` mantiene la dimensión de títulos/ediciones observados, incluidos los que aún no existen como obra. `import_errors`, `import_rows` e `import_mappings` se mantienen para trazabilidad genérica y flujos heredados.

`kdp_report_rows` conserva dimensiones, métricas, JSON original, JSON normalizado, huella y vínculos opcionales. `row_kind` evita sumar dos conceptos distintos:

- `royalty`: ventas/regalías;
- `order`: pedidos pagados o gratuitos;
- `kenp`: lectura Kindle Unlimited;
- `payment`: transferencias.

### Operación editorial

Tareas, checklists, comentarios, eventos, fuentes, ilustraciones, premios, distribución física y contenido A+ cubren el resto del ciclo del autor.

## Paneles y fuentes

- Los widgets históricos usan entidades operativas (`royalty_entries`, promociones, tareas y eventos).
- `KdpImportedDataWidget` usa exclusivamente `kdp_report_rows`.
- No se mezclan automáticamente ambas fuentes para evitar doble contabilización.
- Los administradores pueden ver el conjunto autorizado; un autor se filtra siempre por `user_id`.

## Idempotencia y auditoría

El archivo se identifica con SHA-256 y cada fila con otra huella SHA-256 construida a partir de usuario, tipo, periodo y contenido normalizado. El archivo original permanece privado. El reprocesado reconstruye datos derivados desde ese original.

## Decisiones de diseño pendientes

- tipos de cambio y moneda base por usuario;
- conciliación explícita entre filas KDP y agregados `royalty_entries`;
- mapeo asistido de ASIN no vinculados;
- procesamiento en cola para archivos grandes;
- señales de decisión persistentes y asistente IA multiproveedor.
