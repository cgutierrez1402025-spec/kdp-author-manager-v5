# Resultado de materialización automática del catálogo KDP

## Registros creados durante una importación

| Información disponible | Tabla afectada |
|---|---|
| Operación y archivo | `import_sessions`, `import_batches` |
| Fila original y normalizada | `kdp_report_rows` |
| Identidad comercial detectada | `kdp_catalog_items` |
| Título/autor | `works` |
| ASIN, ISBN, formato y mercado | `publications` |
| Título y autor comercial | `kdp_metadata` |
| Marketplace nuevo | `platforms`, `marketplaces` |
| Pago | `kdp_payments`, `kdp_payment_allocations` |
| Fila inválida | `import_errors` |

## Datos provisionales

La obra creada automáticamente tiene `status = catalog_review` y una `kdp_identity_key` única por usuario, título y autor normalizados. La publicación usa el mismo estado. Los campos no presentes en KDP quedan nulos:

- idioma original y `work_language_id`;
- manuscrito;
- género, público, serie y descripción;
- fechas editoriales no incluidas;
- precio oficial cuando el informe sólo contiene promedios.

Esto permite que la obra aparezca inmediatamente en la tabla de obras sin falsificar información.

## Duplicados

- Título y autor iguales se materializan en una sola obra.
- ASIN y marketplace identifican la publicación cuando existen.
- Cada formato o mercado puede producir una publicación distinta de la misma obra.
- Una publicación existente vinculada de forma inequívoca tiene prioridad.
- Repetir la importación o ejecutar el backfill no crea duplicados.

## Tablas deliberadamente no rellenadas

`work_languages` y `manuscript_versions` no reciben filas ficticias. `royalty_entries` tampoco se rellena porque las regalías importadas ya residen en `kdp_report_rows`; escribirlas en ambas produciría dobles conteos. Los históricos de precio y métricas de mercado sólo se actualizan cuando la fuente contiene una observación inequívoca compatible con su semántica.

## Reconstrucción

```bash
php artisan kdp:materialize-catalog
php artisan kdp:materialize-payments
```

Ambos comandos son idempotentes y permiten aplicar el modelo a informes anteriores.

## Comprobación sobre la base local

El 19 de agosto de 2026 se aplicó la reconstrucción sobre los 73 elementos del catálogo ya importados:

- los 73 quedaron vinculados y ninguna fila quedó pendiente;
- se crearon 30 obras provisionales y 92 publicaciones provisionales;
- las 447 filas KDP quedaron relacionadas con una publicación;
- no se detectaron identidades de obra ni combinaciones ASIN/marketplace duplicadas;
- una segunda ejecución mantuvo exactamente 50 obras, 112 publicaciones y 112 metadatos KDP.

Antes del proceso se guardó la copia de seguridad
`/private/tmp/kdp-author-manager-v5-before-auto-materialization.sqlite`.
