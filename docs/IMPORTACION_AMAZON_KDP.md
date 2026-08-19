# Importación de informes Amazon KDP

## Alcance

La integración soportada se basa en archivos descargados manualmente desde KDP Reports. Amazon no documenta una API pública de cuenta KDP para obtener ventas privadas o modificar publicaciones. No se solicitan credenciales, cookies ni sesiones de Amazon.

La interfaz admite CSV y XLSX de hasta 20 MB y permite clasificar el archivo como regalías anteriores, ventas y regalías, pedidos, KENP, pagos o histórico.

## Flujo interno

1. Filament guarda el archivo en `storage/app/private/kdp-imports`.
2. Se calcula SHA-256. Un archivo idéntico ya registrado se rechaza.
3. Se crea un registro en `import_batches` con usuario, periodo, tipo y ruta privada.
4. El lector extrae todas las hojas del XLSX o detecta el delimitador del CSV.
5. Se localiza la cabecera dentro de las primeras 25 filas.
6. Las columnas en español o inglés se convierten al esquema canónico.
7. Las filas de definiciones y resumen se ignoran.
8. En Sales & Royalties se usa `Ventas combinadas` como fuente canónica de regalías y se ignoran hojas duplicadas por formato. `KENP leídas` se conserva como métrica independiente.
9. Cada fila obtiene una huella estable, de modo que una reimportación no duplica datos.
10. Se intenta vincular con una publicación del autor por ASIN y formato.
11. Se crea o actualiza el título observado en `kdp_catalog_items`, incluso cuando no existe una obra interna.
12. El lote termina como `completed` o `failed` y conserva contadores y errores.

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
- La carga se procesa de forma síncrona; archivos grandes deberían migrarse a un trabajo en cola.
- Los gráficos representan los datos descargados, no información en tiempo real.
