# Evaluación de informes Amazon KDP y modelo de datos

**Fecha:** 19 de agosto de 2026  
**Alcance:** informes descargables de KDP, esquema de KDP Author Manager v5 y XLSX real importado.

## 1. Conclusión ejecutiva

Los informes KDP son una fuente de hechos comerciales, no un catálogo editorial completo. Permiten descubrir títulos que no existen en la aplicación, pero no aportan suficiente información para construir automáticamente una `work`, su idioma, versión de manuscrito y edición sin inventar datos.

La solución adoptada es:

1. conservar cada archivo y fila en el subsistema de importación;
2. normalizar las métricas en `kdp_report_rows`;
3. crear o actualizar automáticamente `kdp_catalog_items` por una identidad estable;
4. vincularlo a `publications/works` solo cuando el ASIN y formato producen una coincidencia fiable;
5. mantener los títulos nuevos como `pending`, visibles en **Catálogo detectado KDP**, hasta su revisión editorial.

Esto satisface la captura automática de obras ausentes sin introducir idiomas, manuscritos o géneros falsos. No se recomienda insertar directamente una obra canónica incompleta.

También se concluye que una única tabla ancha no debe convertirse en el modelo financiero definitivo. `kdp_report_rows` es una buena zona normalizada/auditable, pero pagos, promociones y preventas requieren tablas de hechos especializadas antes de alimentar contabilidad o automatizaciones.

## 2. Fuentes evaluadas

Amazon describe los informes disponibles y su descarga en [KDP Reports](https://kdp.amazon.com/en_US/help/topic/GVTTXHKHVPAPBEDQ/). La disponibilidad relevante es:

| Informe | Descargable | Naturaleza |
|---|---:|---|
| Pedidos | Sí | pedidos procesados por fecha, título, ASIN y marketplace |
| KENP Read | Sí | páginas leídas, estimadas hasta el cierre mensual |
| Royalties Estimator | Sí | regalías estimadas y convertidas a moneda elegida |
| Prior Month's Royalties | Sí | transacciones y regalías mensuales consolidadas |
| Payments | Sí | transferencias, retenciones y cambio aplicado |
| Pre-orders | Sí | pedidos, cancelaciones y unidades netas de preventa |
| Sales and Royalties (antiguo) | Sí mientras siga disponible | libro Excel con hojas solapadas de ventas, formatos, pedidos y KENP |
| Promotions | No según el resumen actual | estado y rendimiento de Kindle Countdown Deals visible en pantalla |
| Month-to-Date | No según el resumen actual | unidades y KENP del mes actual/anterior |

## 3. Campos por informe y destino recomendado

### 3.1 Sales and Royalties

El informe contiene hojas de ventas combinadas, regalías de ebook, tapa blanda y tapa dura, pedidos y KENP. Amazon define fecha de regalía, título, autor, ASIN/ISBN, marketplace, tipo de regalía, tipo de transacción, unidades, devoluciones, unidades netas, precios medios, costes y regalía. La hoja de pedidos añade unidades pagadas/gratuitas y la de KENP añade páginas leídas. Fuente: [Sales and Royalties Report](https://kdp.amazon.com/en_US/help/topic/G201488550).

Destino:

- catálogo observado → `kdp_catalog_items`;
- hechos de ventas/KENP → `kdp_report_rows`;
- publicación existente → vínculo por ASIN/formato;
- agregados históricos → no actualizar automáticamente hasta conciliar.

Riesgo principal: las hojas `Ventas combinadas` y `Regalías de eBooks/impresos` representan las mismas transacciones con distinto detalle. Sumarlas duplica unidades y regalías. El parser elige `Ventas combinadas` como fuente canónica y conserva KENP aparte.

### 3.2 Orders

Incluye fecha, título, autor, ASIN, marketplace, unidades pagadas y gratuitas. Un pedido no equivale necesariamente a una regalía procesada, especialmente en impresión. Debe tener `row_kind=order` y no sumarse con las unidades netas de regalías para el mismo indicador.

Destino futuro recomendado: tabla `kdp_orders` con clave de identidad, fecha local, ASIN, marketplace, pagadas, gratuitas, estado estimado y lote.

### 3.3 KENP Read

Incluye fecha, título, autor, ASIN, marketplace y páginas KENP. Los datos pueden cambiar y se finalizan aproximadamente a mitad del mes siguiente. Fuentes: [KDP Reports](https://kdp.amazon.com/en_US/help/topic/GVTTXHKHVPAPBEDQ/) y [Kindle Unlimited reporting](https://kdp.amazon.com/en_US/help/topic/G201541130).

Destino futuro recomendado: `kdp_kenp_reads`, separada de ventas, con fecha, ASIN, marketplace, páginas y estado estimado/final.

### 3.4 Royalties Estimator

Incluye título, autor, ASIN, marketplace, moneda, tipo de regalía/transacción, unidades vendidas/devueltas/netas y estimación. Amazon advierte que no coincide necesariamente con el pago real por cambios de KENP y tipos de cambio. Fuente: [Royalties Estimator](https://kdp.amazon.com/en_US/help/topic/G6BZ4M9PVJ8YSCW8).

No debe sobrescribir regalías consolidadas. Requiere `value_status=estimated`, moneda de presentación y, si existe, parámetros de conversión/KENP.

### 3.5 Prior Months' Royalties

Contiene título, autor, ASIN, marketplace, moneda, formato, KENP, unidades, devoluciones, precios/costes medios, tipo de transacción y ganancias definitivas. Es la mejor fuente para consolidar `royalty_entries`, pero el esquema actual de esa tabla es demasiado agregado: su clave publicación/año/mes pierde marketplace, moneda y tipo.

Destino futuro recomendado: `kdp_royalty_transactions` detallada y una vista/agregado mensual derivado. No se debe escribir directamente en `royalty_entries` hasta cambiar su clave o regenerarla desde hechos.

### 3.6 Payment Report

Incluye número, marketplace, estado, fecha, método, periodo, neto, regalía devengada, retención, tipo de cambio, importe pagado y fuente. Fuente: [Payment Report](https://kdp.amazon.com/en_US/help/topic/G201436840).

La tabla existente `royalty_payments` no tiene número de pago, método, moneda de destino, tipo de cambio ni fuente y exige plataforma. Se recomienda `kdp_payments` con clave `(user_id, payment_number, source)` y vínculo opcional al agregado contable.

### 3.7 Pre-order Report

Incluye fecha de pedido, marketplace, unidades, cancelaciones y neto. Fuente: [Pre-order Report](https://kdp.amazon.com/en_US/help/topic/G201499460).

No debe sumarse a ventas procesadas antes de publicación: después de la entrega aparecerá en regalías como transacción de preventa. Requiere `kdp_preorders` o un hecho con estado `preorder`, fecha y clave que permita evitar el doble conteo posterior.

### 3.8 Month-to-Date

Incluye título, ASIN, unidades vendidas/devueltas, KENP, unidades gratuitas de promoción y por igualación de precio. Fuente: [Month-to-Date Report](https://kdp.amazon.com/en_US/help/topic/G200641130).

Es información provisional y agregada. Puede alimentar seguimiento, pero nunca reemplazar el cierre mensual. Debe incluir periodo, fecha de captura y `value_status=estimated`.

### 3.9 Promotions

La vista contiene marketplace, ASIN, título, inicio, fin y estado de Kindle Countdown Deals. Fuente: [Promotions Report](https://kdp.amazon.com/en_US/help/topic/G201315170). La documentación actual no la marca como descargable.

Si el usuario aporta un archivo compatible en el futuro, puede mapearse a `book_promotions` solo tras vincular publicación. Como faltan objetivo, precios y otros campos internos, se recomienda una observación `kdp_promotion_observations` antes de crear una campaña canónica.

## 4. Resultado del análisis del XLSX real

El archivo importado contenía:

- definiciones y resumen;
- ventas combinadas;
- regalías de ebook e impresión (solapadas con ventas combinadas);
- pedidos procesados y pedidos de ebook (solapados entre sí);
- KENP.

La primera importación examinó 145 filas: 45 entraron, 63 se identificaron como equivalentes y 37 fallaron por la cabecera localizada `ASIN/ISBN`. Además, `70%` se interpretó erróneamente como formato.

Después de corregir alias, clasificación y selección de hojas, el reprocesado produjo:

- 39 filas canónicas;
- 37 filas de regalías y 2 de KENP;
- 62 unidades netas;
- 164 páginas KENP;
- 0 duplicados y 0 errores;
- importes separados por moneda, incluidos 6,55 EUR y 1,64 USD.

## 5. Duplicados

### Fuentes de duplicación

1. Mismo hecho repetido en hojas combinada y específica de formato.
2. Mismo archivo cargado otra vez.
3. Archivo nuevo con un periodo solapado.
4. Pedidos que después aparecen como venta/regalía procesada.
5. Preventa que vuelve a aparecer tras la entrega.
6. ASIN escrito en varias publicaciones internas por marketplace.
7. Título/autor con diferencias de acentos, abreviaturas o traducción.

### Controles

- hash del archivo;
- huella de fila normalizada por usuario/tipo/periodo/contenido;
- `row_kind` y fuente canónica por informe;
- identidad de catálogo por ASIN/ISBN/título/autor/formato;
- claves únicas específicas en futuras tablas de hechos;
- conciliación, no suma ciega, entre estimado y definitivo.

No debe usarse únicamente título como clave: traducciones, reediciones y títulos iguales producen falsos positivos. ASIN es el identificador preferido; ISBN complementa impresión.

## 6. Atributos vacíos

Los nulos son esperables cuando un informe no contiene una dimensión. No son un problema en una zona de staging, pero sí lo son en entidades canónicas que presuponen integridad editorial.

| Entidad | Vacíos aceptables | Vacíos problemáticos |
|---|---|---|
| `kdp_report_rows` | métricas ajenas al tipo de fila | identidad ausente sin motivo |
| `kdp_catalog_items` | ISBN, formato o autor si KDP no los entrega | título e identidad simultáneamente ausentes |
| `works` | notas, género o fechas | idioma/estado inventados o título ambiguo |
| `publications` | marketplace si es global | obra, idioma, manuscrito y plataforma ficticios |
| `royalty_payments` | fechas futuras | número/estado/moneda perdidos por falta de columnas |

Por ello se prefiere staging y promoción controlada frente a rellenar cadenas vacías, `N/A` o valores por defecto engañosos.

## 7. Tablas nuevas: decisión

### Implementada ahora

`kdp_catalog_items` es necesaria porque el catálogo observado en informes no equivale todavía al catálogo editorial. Guarda usuario, identidad, ASIN, ISBN, título, autor, formato, marketplaces, vínculos, estado de revisión y primeras/últimas apariciones.

### Existente y válida como staging

`kdp_report_rows` conserva el hecho importado, su tipo, métricas y JSON. Es útil para auditoría y gráficos, aunque sea dispersa.

### Recomendadas antes de automatización contable completa

1. `kdp_royalty_transactions` — ventas/regalías definitivas y estimadas.
2. `kdp_orders` — pedidos pagados/gratuitos.
3. `kdp_kenp_reads` — páginas por fecha/ASIN/mercado.
4. `kdp_payments` — pagos y conversión bancaria.
5. `kdp_preorders` — preventas y cancelaciones.
6. `kdp_report_snapshots` — captura de agregados provisionales.
7. `exchange_rates` — conversión trazable a moneda base.

Crear todas de inmediato sin muestras reales de cada descarga aumentaría el riesgo de diseñar columnas incorrectas. La recomendación es obtener un archivo anonimizado de cada tipo, crear contratos de parser y migrar desde `kdp_report_rows` una tabla por iteración.

## 8. Mapeo a tablas existentes

- `works/publications`: solo vincular automáticamente; crear la obra final mediante revisión.
- `royalty_entries`: generar agregados únicamente desde regalías definitivas después de ampliar granularidad y moneda.
- `royalty_payments`: no cargar hasta ampliar número/método/FX/fuente.
- `book_promotions`: crear después de vincular ASIN y completar datos internos.
- `promotion_daily_results`: puede recibir resultados diarios derivados cuando campaña y moneda estén identificadas.
- `kdp_select_periods`: los informes de ventas no prueban fechas de afiliación; no inferir.
- `kdp_metadata`: los informes no contienen descripción, palabras clave, categorías o declaración IA; no rellenar.

## 9. Plan recomendado

1. Usar ya `kdp_catalog_items` y el panel actual para descubrir títulos.
2. Añadir flujo de revisión: vincular a obra existente o crear una obra mediante un asistente que solicite idioma y edición.
3. Conseguir ejemplos anonimizados de Prior Royalties, Payments, Orders, KENP y Pre-orders.
4. Implementar primero `kdp_royalty_transactions` y `kdp_payments`.
5. Crear agregados mensuales reproducibles y tipos de cambio.
6. Añadir preventas y promociones sin doble conteo.
7. Mantener pruebas por idioma, versión del informe y claves de idempotencia.

La decisión final es crear staging de catálogo ahora y posponer la creación automática de obras canónicas hasta disponer de los campos editoriales que KDP no proporciona. Esto protege la calidad del catálogo y conserva toda la información disponible para una revisión posterior.

