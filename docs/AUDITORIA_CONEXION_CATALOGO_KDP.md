# Auditoría de conexión entre el catálogo detectado KDP y el modelo editorial

Fecha: 19 de agosto de 2026.

## Conclusión

`kdp_catalog_items` no sustituye a `works` ni a `publications`. Es una tabla puente de identidad y revisión: representa cada producto detectado en los informes de Amazon, agrupa sus apariciones y lo conecta con la obra editorial y con una publicación representativa. Las filas económicas permanecen en `kdp_report_rows` y apuntan a la publicación exacta correspondiente al mercado.

La conexión existente es íntegra en la base analizada: los 73 elementos del catálogo tienen obra y publicación, y las 324 filas canónicas importadas tienen catálogo, obra y publicación. No hay claves rotas ni discrepancias entre la obra del catálogo y la obra de sus publicaciones.

No obstante, la importación todavía desaprovecha algunas columnas presentes en los archivos reales. Se conservan en `raw_data`, por lo que no se han perdido, pero no pueden consultarse eficientemente ni alimentar indicadores hasta que se normalicen.

## Papel de cada tabla

| Tabla | Grano o significado | Relación principal |
|---|---|---|
| `import_sessions` | Una operación de carga múltiple | Contiene lotes |
| `import_batches` | Un archivo importado | Contiene filas e incidencias |
| `kdp_report_rows` | Una fila económica original y normalizada | Pertenece a lote, catálogo y publicación |
| `kdp_catalog_items` | Una identidad comercial detectada | Pertenece a usuario, obra y publicación representativa |
| `works` | La obra intelectual, común a formatos y mercados | Tiene muchas publicaciones |
| `publications` | Una edición/formato en un marketplace | Pertenece a una obra |
| `kdp_metadata` | Metadatos comerciales de una publicación | Relación uno a uno con publicación |
| `marketplaces` | Tienda o territorio de Amazon KDP | Referenciado por publicaciones |
| `kdp_payments` | Un pago identificado por Amazon | Se enlaza a filas mediante asignaciones |
| `kdp_payment_allocations` | Evidencia de atribución de un pago | Une pago, fila y, si consta, publicación |

La ruta efectiva es `archivo → import_batch → kdp_report_row → kdp_catalog_item → work`. Para la edición concreta es `kdp_report_row → publication → work / marketplace / kdp_metadata`.

Un elemento de catálogo puede aparecer en varios mercados. Por eso `kdp_catalog_items.publication_id` es sólo la publicación representativa, mientras que `kdp_report_rows.publication_id` conserva la publicación exacta de cada fila.

## Resultado medido

| Comprobación | Resultado |
|---|---:|
| Elementos en catálogo | 73 |
| Catálogos con obra y publicación | 73 |
| Catálogos en estado `linked` | 73 |
| Filas KDP canónicas | 324 |
| Filas con catálogo, publicación y obra | 324 |
| Filas KENP conectadas | 16 de 16 |
| Filas de pedidos conectadas | 93 de 93 |
| Filas de regalías conectadas | 215 de 215 |
| Publicaciones KDP sin metadatos | 0 |
| Referencias rotas o incoherentes | 0 |

Hay 30 obras provisionales para 73 productos y 92 combinaciones de producto/mercado. Esto es correcto: una obra puede tener varios formatos y cada formato puede comercializarse en varios marketplaces.

## Información que ya se propaga

| Campo KDP | Destino |
|---|---|
| título y autor | `works` y `kdp_metadata` |
| ASIN, ISBN y formato | `publications` |
| tienda y moneda | `marketplaces` y `publications` |
| unidades, devoluciones, KENP, precios medios, costes y regalías | `kdp_report_rows` |
| número, fecha, estado, retención, cambio e importe de pago | `kdp_payments` |
| valores originales no reconocidos | `kdp_report_rows.raw_data` |

Los campos editoriales que Amazon no entrega en estos informes —idioma, manuscrito, género, descripción, categorías o palabras clave— permanecen vacíos y señalados con `catalog_review`. No deben inventarse.

## Información adicional ya normalizada

Tras ejecutar el plan, las columnas que antes sólo estaban en JSON tienen columnas consultables:

| Columna encontrada | Apariciones | Destino aplicado |
|---|---:|---|
| `ingresos` | 99 | `income_amount` |
| `plan de pago` | 99 | `payment_plan` |
| `unidades netas vendidas o KENP leídas**` | 99 | `combined_units_or_kenp` |
| `tamaño medio del archivo (MB)` | 156 | `average_file_size_mb` |

Los cuatro lotes que estaban como `unknown` se volvieron a detectar como `prior_royalties`. Ya no quedan filas desconocidas. También se eliminaron 123 filas solapadas que un lote de Pedidos había tomado de hojas auxiliares de regalías y KENP.

## Tablas que no deben rellenarse directamente

- `work_languages` y `manuscript_versions`: los informes analizados no acreditan idioma editorial ni archivo fuente.
- `kdp_metadata` más allá de título y autor: los informes económicos no incluyen sinopsis, categorías, palabras clave o declaración de IA.
- `publication_market_observations`: está diseñada para ranking, reseñas y valoración, datos ausentes en estos informes.
- `promotion_daily_results`: sólo debe recibir datos cuando exista una promoción identificada.
- `publication.price`: el informe ofrece precios medios observados, no necesariamente el precio oficial vigente.

## Regalías: evitar una duplicación semántica

`royalty_entries` existe, pero su clave única es publicación/año/mes y carece de marketplace, tipo de regalía, moneda de origen y vínculo con filas fuente. Copiar allí cada fila KDP produciría agregaciones ambiguas y riesgo de doble conteo, porque `kdp_report_rows` ya es la fuente canónica de los gráficos.

La opción recomendada es mantener el detalle en `kdp_report_rows` y crear vistas o consultas agregadas. Si se necesita interoperar con `royalty_entries`, debe rediseñarse como resumen materializado, incorporando marketplace, moneda, tipo, procedencia y una tabla puente con las filas KDP. La reconstrucción tendrá que ser transaccional e idempotente.

## Mejoras propuestas

### Prioridad alta

1. Añadir a `kdp_report_rows` los campos `income_amount`, `payment_plan`, `combined_units_or_kenp` y `average_file_size_mb`.
2. Ampliar los alias españoles e ingleses y resolver la métrica combinada según hoja, formato y tipo de transacción.
3. Corregir la autodetección de los 130 registros clasificados como `unknown`.
4. Añadir pruebas con las cabeceras reales y verificar que ningún campo conocido quede únicamente en `raw_data`.

### Prioridad media

5. Incorporar una pantalla de trazabilidad desde obra y publicación hacia catálogo, filas y lotes de origen.
6. Mostrar en el catálogo el número de publicaciones y mercados asociados, no sólo la publicación representativa.
7. Crear un indicador de completitud que diferencie datos ausentes en Amazon de datos pendientes de revisión.
8. Auditar conflictos cuando un mismo ASIN llegue con títulos, autores o formatos incompatibles.

### Evolución opcional

9. Crear una vista mensual por publicación, marketplace, moneda y tipo de regalía.
10. Materializar ese resumen sólo si se conserva la procedencia y se evita el doble conteo.
11. Crear históricos de precio a partir de observaciones fechadas con `source = kdp_report`, sin sobrescribir el precio oficial.

## Criterios de aceptación

- Toda fila no destinada a pagos termina con catálogo y publicación o con un error explícito.
- Publicación, obra, catálogo y usuario son coherentes entre sí.
- Reimportar o reprocesar no cambia totales ni crea duplicados.
- Ningún campo conocido de un informe soportado queda sólo en JSON.
- Importes y métricas conservan moneda, mercado, periodo y significado.
- Todo agregado puede rastrearse hasta el lote y las filas fuente.
- La ausencia de datos editoriales nunca se resuelve con valores inventados.
