# Importación de informes Amazon KDP

## Alcance

La integración soportada se basa en archivos descargados manualmente desde KDP Reports. Amazon no documenta una API pública de cuenta KDP para obtener ventas privadas o modificar publicaciones. No se solicitan credenciales, cookies ni sesiones de Amazon.

La interfaz admite hasta 20 archivos CSV/XLSX de 20 MB cada uno o ZIP de hasta 100 MB. Puede detectar automáticamente regalías anteriores, ventas y regalías, pedidos, KENP, pagos o histórico; el usuario todavía puede forzar un tipo común cuando conoce el contenido.

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
- hoja fuente y naturaleza: `royalty`, `order`, `kenp` o `payment`;
- título, autor, ASIN, formato y marketplace;
- moneda, tipo de transacción y tipo de regalía;
- unidades vendidas, devueltas, netas, pagadas y gratuitas;
- páginas KENP;
- precios medios, entrega/producción y regalía;
- datos de pagos, retención y tipo de cambio;
- objeto original y objeto normalizado completos en JSON.

## Reglas de no duplicación

- `import_batches.file_hash` evita cargar dos veces el mismo archivo.
- `kdp_report_rows(user_id, row_fingerprint)` evita repetir una fila equivalente incluso si aparece en hojas solapadas.
- La hoja fuente no forma parte de la equivalencia comercial cuando las métricas son las mismas.
- KENP y regalías sí se diferencian mediante `row_kind`.

## Monedas

El importe y la moneda originales se conservan. Los gráficos agrupan regalías por código ISO y no generan un total multimoneda. Para un consolidado será necesario añadir tipos de cambio con fuente y fecha, manteniendo siempre el importe original.

## Asociación con publicaciones

La asociación automática busca el ASIN entre las publicaciones del usuario y, cuando el informe permite inferirlo, el formato. Una fila puede ser válida aunque `publication_id` sea nulo. Esto sucede cuando el libro todavía no existe en el catálogo interno o el formato no coincide.

Los títulos no vinculados aparecen en **Publicaciones → Catálogo detectado KDP** como pendientes. No se crea automáticamente una obra editorial porque el informe no aporta idioma original ni versión de manuscrito; el registro observado conserva todos los datos disponibles hasta que el autor complete la revisión.

Procedimiento recomendado:

1. crear o corregir la publicación y su ASIN;
2. abrir Importaciones KDP;
3. usar **Reprocesar**;
4. comprobar el vínculo y los gráficos.

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

## Límites conocidos

- Amazon puede cambiar nombres y estructura de sus hojas; los alias deben mantenerse versionados.
- Los informes localizados en idiomas distintos de español e inglés pueden requerir nuevos alias.
- Las filas sin ASIN solo se admiten si son pagos con número de pago.
- La sesión aísla cada archivo, aunque actualmente se ejecuta en la misma petición. Para volúmenes superiores a los límites de interfaz se debe configurar una cola.
- Los gráficos representan los datos descargados, no información en tiempo real.
