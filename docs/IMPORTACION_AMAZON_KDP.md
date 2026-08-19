# Importación de informes Amazon KDP

## Alcance

La integración soportada se basa en archivos descargados manualmente desde KDP Reports. Amazon no documenta una API pública de cuenta KDP para obtener ventas privadas o modificar publicaciones. No se solicitan credenciales, cookies ni sesiones de Amazon.

La interfaz admite hasta 20 archivos CSV/XLSX de 20 MB cada uno o ZIP de hasta 100 MB. Puede detectar automáticamente Panel, regalías anteriores, ventas y regalías, pedidos, KENP, preventas, estimador, pagos o histórico; el usuario todavía puede forzar un tipo común cuando conoce el contenido.

## Flujo interno

1. Filament guarda los archivos en `storage/app/private/kdp-imports`.
2. Si se recibe un ZIP, sólo extrae CSV, TXT y XLSX con nombres saneados, límite de 20 MB por entrada y 100 MB descomprimidos.
3. Se crea una `import_session` que agrega estado y contadores de toda la operación.
4. Para cada archivo se calcula SHA-256; un duplicado se omite sin cancelar los demás.
5. La cabecera, hojas y nombre del archivo permiten detectar tipo, confianza y periodo.
6. Se crea un `import_batch` independiente por archivo con usuario, periodo, detección y ruta privada.
7. El lector extrae todas las hojas del XLSX o detecta el delimitador del CSV y localiza la cabecera en las primeras 25 filas.
8. Las columnas en español o inglés se convierten al esquema canónico y las filas de definición/resumen se ignoran.
9. En Sales & Royalties se usa `Ventas combinadas` como fuente canónica; `KENP leídas` se conserva como métrica independiente.
10. Cada fila obtiene una huella estable, se vincula por ASIN/formato y actualiza `kdp_catalog_items`.
11. Cada archivo confirma o revierte su propia transacción. La sesión termina `completed`, `partial` o `failed`.

## Uso de la carga múltiple

1. Abre **Publicaciones → Importar informes KDP**.
2. Arrastra o selecciona hasta 20 informes; también puedes elegir un ZIP.
3. Mantén **Detectar automáticamente**. El periodo manual sólo actúa como respaldo cuando no aparece en el nombre.
4. Inicia la importación una sola vez.
5. Consulta **Publicaciones → Sesiones de importación** para ver archivos completados, duplicados, fallidos y filas nuevas.
6. Usa **Ver archivos** para revisar cada lote y reprocesar únicamente el necesario.

El mes se reconoce en nombres con patrones `AAAA-MM` y `MM-AAAA`, admitiendo guion, punto, espacio o guion bajo. Si todos los archivos seleccionados contienen el mismo mes, el formulario rellena **Mes del informe**. Si contienen meses diferentes, el campo común queda vacío y cada lote utiliza su propio periodo detectado.

## Campos normalizados

Se almacenan, cuando existen:

- fecha de transacción y periodo;
- hoja fuente y naturaleza: `royalty`, `royalty_estimate`, `order`, `preorder`, `kenp` o `payment`;
- estado de observación `estimated`, `provisional` o `final`, y fecha de captura;
- título, autor, ASIN, formato y marketplace;
- moneda, tipo de transacción y tipo de regalía;
- unidades vendidas, devueltas, netas, pagadas y gratuitas;
- páginas KENP;
- preventas, cancelaciones y preventas netas;
- precios medios, tamaño de archivo, entrega/producción, ingresos, plan de pago y regalía;
- datos de pagos, método, periodo de ventas, fuente, retención y tipo de cambio;
- objeto original y objeto normalizado completos en JSON.

## Reglas de no duplicación

- `import_batches.file_hash` evita cargar dos veces el mismo archivo.
- `kdp_report_rows(user_id, row_fingerprint)` evita repetir una fila equivalente incluso si aparece en hojas solapadas.
- La hoja fuente no forma parte de la equivalencia comercial cuando las métricas son las mismas.
- KENP y regalías sí se diferencian mediante `row_kind`.

## Monedas

El importe y la moneda originales se conservan. Los gráficos agrupan regalías por código ISO y no generan un total multimoneda. Para un consolidado será necesario añadir tipos de cambio con fuente y fecha, manteniendo siempre el importe original.

## Asociación con publicaciones

La asociación automática busca el ASIN entre las publicaciones del usuario y, cuando el informe permite inferirlo, el formato. Si no existe, una fila de libro crea una publicación provisional; una fila agregada de pago puede permanecer sin publicación.

Los títulos no vinculados se materializan automáticamente en `works`, `publications` y `kdp_metadata`, además de aparecer en **Publicaciones → Catálogo detectado KDP**. La obra y la publicación usan el estado `catalog_review`; idioma, manuscrito, género y otros datos ausentes quedan en `NULL` hasta la revisión.

Desde ese listado se puede usar **Crear obra y edición** para completar idioma, marketplace y formato, o **Vincular a obra**. La operación crea las relaciones necesarias de forma transaccional y nunca inventa un manuscrito. También puede marcarse un elemento como ignorado.

La identidad automática de la obra combina usuario, título normalizado y autor. Por ello, ebook, tapa blanda y tapa dura con títulos/autores iguales se agrupan bajo una obra, mientras cada ASIN/formato/mercado conserva su publicación. Si ya existe una coincidencia inequívoca, se reutiliza. El proceso completo es idempotente.

Para aplicar esta proyección a informes cargados antes de esta versión:

```bash
php artisan kdp:materialize-catalog
```

No se crean filas en `work_languages` ni `manuscript_versions` si el informe no contiene idioma o manuscrito. Tampoco se copian las regalías a `royalty_entries`, porque eso duplicaría la fuente: `kdp_report_rows` continúa siendo la tabla canónica para los informes importados.

Procedimiento recomendado:

1. crear o corregir la publicación y su ASIN;
2. abrir Importaciones KDP;
3. usar **Reprocesar**;
4. comprobar el vínculo y los gráficos.

### Reprocesado coherente

**Reprocesar** verifica la huella SHA-256 y bloquea el lote antes de sustituir datos. Dentro de una única transacción reconstruye filas, errores y proyecciones de pagos, recalcula el catálogo afectado y elimina únicamente elementos pendientes que hayan quedado huérfanos. Las obras, publicaciones y decisiones revisadas por el usuario no se eliminan. Si falta el archivo, ha cambiado o el parser falla, se revierte la transacción y se conserva íntegramente el resultado anterior.

En **Sesiones de importación**, **Reprocesar sesión** repite todos los lotes con una transacción independiente por archivo. Un fallo conserva ese archivo y no impide actualizar los demás; la sesión termina `partial` cuando corresponde.

Para reprocesar desde consola todos los archivos originales conservados y volver a detectar lotes antiguos:

```bash
php artisan kdp:reprocess-imports
php artisan kdp:reprocess-imports --user=2
php artisan kdp:reprocess-imports --type=orders
```

## Estados y contadores

Una sesión utiliza `processing`, `completed`, `partial` o `failed`. `partial` indica que al menos un archivo terminó y otro falló. Los archivos no reconocidos quedan como `needs_review` y no bloquean la sesión.

- `pending`: lote creado;
- `processing`: lectura activa;
- `completed`: proceso terminado, incluso si algunas filas presentan error;
- `failed`: el archivo no pudo procesarse.

Los contadores significan:

- importadas: filas canónicas nuevas;
- duplicadas: filas equivalentes ya existentes;
- errores: filas reconocidas que no pudieron normalizarse;
- total: filas de datos examinadas en hojas seleccionadas.

## Proyección de pagos

Las filas de pago se conservan en `kdp_report_rows` y se materializan en `kdp_payments`/`kdp_payment_allocations`. Un pago sin relación explícita con una publicación queda sin asignar. Para reconstruir esta proyección desde informes anteriores se utiliza `php artisan kdp:materialize-payments`.

## Límites conocidos

- Amazon puede cambiar nombres y estructura de sus hojas; los alias deben mantenerse versionados.
- Los informes localizados en idiomas distintos de español e inglés pueden requerir nuevos alias.
- Las filas sin ASIN solo se admiten si son pagos con número de pago.
- La sesión aísla cada archivo, aunque actualmente se ejecuta en la misma petición. Para volúmenes superiores a los límites de interfaz se debe configurar una cola.
- Los gráficos representan los datos descargados, no información en tiempo real.
