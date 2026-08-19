# Matriz de tablas y acciones por tipo de informe Amazon KDP

Fecha de revisión: 19 de agosto de 2026.

## Alcance oficial

La [documentación general de KDP Reports](https://kdp.amazon.com/en_US/help/topic/GVTTXHKHVPAPBEDQ) indica que se pueden descargar los informes de Panel, Pedidos, KENP leídas, Preventas, Regalías de meses anteriores, Estimador de regalías y Pagos. Promociones y Mes en curso aparecen en KDP Reports, pero no son descargables. Amazon también mantiene temporalmente el [informe antiguo de Ventas y regalías](https://kdp.amazon.com/en_US/help/topic/G201488550), compuesto por varias hojas.

Esta matriz diferencia los datos oficiales o finalizados de las estimaciones y evita contabilizar dos veces una operación que aparezca en más de un informe.

## Acciones comunes para informes con libros

Siempre que una fila incluya título, autor, ASIN/ISBN, formato o tienda, la importación debe ejecutarse en una transacción:

1. `import_sessions`: insertar una sesión por carga múltiple.
2. `import_batches`: insertar un lote por archivo, con hash, tipo y periodo detectados.
3. `kdp_report_rows`: insertar la fila normalizada y el JSON original; omitirla si ya existe la misma huella.
4. `kdp_catalog_items`: hacer `upsert` de la identidad comercial y actualizar primera/última aparición y mercados.
5. `works`: reutilizar la obra por publicación inequívoca o identidad título/autor; en otro caso insertar obra `catalog_review`.
6. `platforms` y `marketplaces`: reutilizar Amazon KDP y hacer `upsert` de la tienda.
7. `publications`: hacer `upsert` por ASIN/marketplace y relacionarla con la obra.
8. `kdp_metadata`: hacer `upsert` de título y autor acreditados.
9. Actualizar las claves de `kdp_report_rows` y `kdp_catalog_items` hacia publicación y obra.

No se deben crear idiomas, manuscritos, géneros, categorías o promociones si el archivo no los acredita.

## Resumen de decisiones

| Informe descargable | Estado actual | Tablas que se insertan/actualizan | Acción específica necesaria |
|---|---|---|---|
| Panel | Soportado | Tablas comunes + `kdp_report_rows` | Separa pedidos, KENP y regalías estimadas; lo marca como instantánea |
| Pedidos | Soportado | Tablas comunes + `kdp_report_rows` | Insertar hechos `order`; no crear regalía |
| KENP leídas | Soportado | Tablas comunes + `kdp_report_rows` | Insertar hechos `kenp`; distinguir provisional/final |
| Preventas | Soportado | Tablas comunes + campos de preventa | Detección, `row_kind=preorder` y métricas propias |
| Regalías de meses anteriores | Soportado | Tablas comunes + `kdp_report_rows` | Tratar como fuente económica final; normalizar campos restantes |
| Estimador de regalías | Soportado | Tablas comunes + `kdp_royalty_estimates` | Mantiene estimaciones separadas de las regalías finales |
| Pagos | Soportado | `kdp_report_rows`, `kdp_payments`, `kdp_payment_allocations` | Conserva método, periodo, neto y fuente; no fuerza obra |
| Ventas y regalías antiguo | Soportado | Tablas comunes + `kdp_report_rows` | Procesa hojas con semántica separada y evita doble conteo |

## 1. Descarga del Panel

El Panel contiene libros con más ingresos, regalías estimadas, pedidos y KENP, además de formatos y tiendas. Es una instantánea y Amazon advierte que puede no coincidir con los informes especializados por sus distintos tiempos de actualización.

Acciones:

- insertar lote con `import_type=dashboard` y fecha/hora de corte;
- insertar cada métrica en `kdp_report_rows` con `row_kind=order`, `kenp` o `royalty_estimate`;
- ejecutar las acciones comunes para catálogo, obra, publicación y marketplace;
- añadir `observation_status=estimated` y `snapshot_at` a `kdp_report_rows`;
- no sumar estas filas con Pedidos, KENP o Regalías al obtener totales oficiales.

No se debe actualizar `royalty_entries`, `publication.price` ni `promotion_daily_results`.

## 2. Informe de Pedidos

El [informe de Pedidos](https://kdp.amazon.com/en_US/help/topic/GNT7B2H74F5NJZW3) incluye fecha, título, autor, ASIN, tienda y unidades pagadas/gratuitas. Para impresión representa pedidos enviados; para eBook, pedidos procesados.

Acciones:

- aplicar todas las acciones comunes;
- insertar `kdp_report_rows` con `row_kind=order`, fecha y unidades pagadas/gratuitas;
- conservar formato/distribución si aparecen en la descarga;
- identificar cada hecho mediante usuario, informe, fecha, ASIN, tienda, formato y dimensiones de la fila;
- usar estas filas para gráficos de demanda, nunca como regalía confirmada.

No es necesario crear `kdp_orders`: `kdp_report_rows` ya conserva el detalle y la procedencia. Una vista agregada puede ofrecer consumo más sencillo.

## 3. Informe KENP leídas

Contiene páginas normalizadas leídas por fecha, libro y marketplace. Amazon indica que el dato mensual puede variar hasta su cierre alrededor del día 15 siguiente.

Acciones:

- aplicar las acciones comunes;
- insertar `kdp_report_rows` con `row_kind=kenp`, `transaction_date` y `kenp_read`;
- añadir `observation_status=provisional|final` y fecha de captura;
- al reprocesar, sustituir la observación de igual clave y versión, sin acumularla;
- calcular regalías KENP finales sólo desde Regalías de meses anteriores, no desde páginas por una tarifa estimada.

No se debe crear una venta ni una fila de pago por una lectura KENP.

## 4. Informe de Preventas

El [informe de Preventas](https://kdp.amazon.com/en_US/help/topic/G201499460) ofrece fecha, tienda, unidades reservadas, cancelaciones y unidades netas. Tras publicarse el eBook, las ventas aparecen posteriormente como transacciones de preventa en regalías.

Acciones:

- ampliar el detector con `preorders` y alias españoles/ingleses;
- aplicar las acciones comunes si la descarga incluye identidad del título;
- añadir a `kdp_report_rows` `preorder_units`, `preorder_cancellations` y `net_preorder_units`;
- insertar con `row_kind=preorder`;
- no convertir preventas en pedidos o regalías definitivas;
- relacionar analíticamente la preventa con la venta posterior, sin borrar ninguno de los dos hechos.

No hace falta una tabla separada mientras se añadan métricas y estado a `kdp_report_rows`. Si se necesita gestionar fechas límite editoriales, esa planificación sí debe modelarse aparte de la importación económica.

## 5. Regalías de meses anteriores

El [informe de Regalías de meses anteriores](https://kdp.amazon.com/en_US/help/topic/G200641190) es la fuente oficial para transacciones e ingresos cerrados. Incluye título, autor, ASIN, tienda, moneda, formato, unidades, devoluciones, precio medio, tamaño medio de archivo, coste de entrega, tipo de regalía/transacción, KENP y total ganado.

Acciones:

- aplicar las acciones comunes;
- insertar `kdp_report_rows` con `row_kind=royalty` o `kenp`, conservando moneda y periodo;
- considerar `total_earnings` como importe final de ese concepto y periodo;
- añadir `average_file_size_mb`, actualmente sólo conservado en JSON;
- registrar bonos en un campo/tipo explícito cuando aparezcan;
- mantener precios y costes como observaciones medias del periodo, no como precio vigente de la publicación;
- construir vistas mensuales por publicación, tienda, moneda y tipo para informes contables.

No se debe copiar automáticamente a `royalty_entries` mientras esa tabla no conserve tienda, tipo, moneda y procedencia. Si se rediseña como resumen, debe reconstruirse desde filas fuente mediante `upsert` transaccional.

## 6. Estimador de regalías

El [Estimador de regalías](https://kdp.amazon.com/en_US/help/topic/G6BZ4M9PVJ8YSCW8) muestra importes estimados en una moneda seleccionada y estimaciones KENP; Amazon indica que no coincidirán necesariamente con las regalías ni con el pago final.

Acciones:

- aplicar las acciones comunes;
- crear `kdp_royalty_estimates`, o ampliar `kdp_report_rows`, con importe, moneda de presentación, tarifa KENP usada, periodo, fecha de captura y filtros;
- hacer `upsert` por publicación, tienda, periodo, moneda y fecha de captura;
- marcar siempre `observation_status=estimated`;
- usarlo en previsiones, nunca en contabilidad, conciliación o ingresos realizados.

Se recomienda tabla separada si se conservarán varias simulaciones de moneda o tarifa KENP; su semántica difiere de una transacción final.

## 7. Informe de Pagos

El [informe de Pagos](https://kdp.amazon.com/en_US/help/topic/G201436840) incluye número, tienda, estado, fecha, método, ingresos netos, tipo de cambio, importe, periodo de ventas, regalía acumulada, retención y fuente.

Acciones:

- insertar la fila original en `kdp_report_rows` con `row_kind=payment`;
- hacer `upsert` en `kdp_payments` por usuario, número de pago y moneda;
- hacer `upsert` en `kdp_payment_allocations` enlazando la fila fuente;
- añadir a `kdp_payments` `payment_method`, `net_earnings`, `sales_period_start`, `sales_period_end` y `source`;
- relacionar con publicación sólo cuando el informe contenga una identidad inequívoca;
- mantener `unallocated` para pagos agregados sin ASIN y conciliarlos con regalías por periodo/tienda/moneda mediante reglas auditables.

No se debe inventar una obra para una fila de pago agregada. `royalty_payments` puede mantenerse para planificación manual o reemplazarse en una migración posterior, pero no debe duplicar importes de `kdp_payments`.

## 8. Informe antiguo de Ventas y regalías

El XLSX antiguo contiene hojas de ventas combinadas, regalías por formato, pedidos y KENP. Amazon anuncia que seguirá disponible sólo durante un periodo limitado.

Acciones:

- mantener compatibilidad de lectura XLSX mientras siga disponible;
- asignar cada hoja a `row_kind` y evitar importar hojas de detalle cuando `Ventas combinadas` ya representa la misma regalía;
- aplicar las acciones comunes por cada identidad comercial;
- insertar regalías, pedidos y KENP como hechos diferentes;
- normalizar `ingresos`, `plan de pago`, métrica combinada y tamaño medio de archivo, hoy conservados sólo en JSON;
- marcar `source_generation=legacy` para distinguir esta fuente del sistema actual.

## Informes no descargables

Según la matriz oficial actual, Promociones y Mes en curso no ofrecen descarga. No deben aparecer como tipos de archivo seleccionables salvo que Amazon cambie esa capacidad o el usuario aporte un formato verificable. Los datos promocionales no deben inferirse desde una caída de precio o desde ventas coincidentes en fecha.

## Cambios de esquema implementados

### Ampliar `kdp_report_rows`

- `observation_status`: `estimated`, `provisional` o `final`;
- `snapshot_at`;
- `preorder_units`, `preorder_cancellations`, `net_preorder_units`;
- `income_amount`, `payment_plan`, `combined_units_or_kenp`;
- `average_file_size_mb`;
- `source_generation`: `current` o `legacy`.

### Ampliar `kdp_payments`

- `payment_method`;
- `net_earnings`;
- `sales_period_start` y `sales_period_end`;
- `source`.

### Nueva tabla `kdp_royalty_estimates`

Necesaria si se quieren conservar escenarios y capturas sucesivas sin mezclarlos con cifras cerradas. Debe relacionarse con usuario, publicación, marketplace e `import_batch`.

## Resultado de implementación

1. Se completaron columnas y alias de Regalías anteriores y Pagos.
2. Se incorporaron Preventas y sus pruebas.
3. KENP distingue observaciones provisionales y datos finales incluidos en regalías cerradas.
4. El Estimador se materializa en `kdp_royalty_estimates`, fuera de la contabilidad final.
5. El Panel se importa como instantánea no acumulable.
6. El XLSX antiguo queda marcado como `legacy` y filtra hojas solapadas.
7. El catálogo muestra obra vinculada y número de filas fuente; cada fila conserva lote, publicación y marketplace.

## Reglas de integridad

- Un archivo repetido se rechaza por SHA-256; un reprocesado sustituye sus derivados de forma atómica.
- La huella de fila debe incluir todas las dimensiones que cambien su significado.
- Ninguna estimación se suma con importes finales.
- Pedidos, preventas, regalías, KENP y pagos son hechos distintos.
- Los importes nunca se agregan entre monedas sin una conversión y fecha explícitas.
- Todo resumen debe poder reconstruirse desde las filas fuente.
- Una fila sin identidad de libro puede crear un pago, pero no una obra.
