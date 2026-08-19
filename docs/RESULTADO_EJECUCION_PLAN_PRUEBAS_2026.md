# Resultado de ejecución del plan de pruebas y correcciones

Fecha: 19 de agosto de 2026.

## Resultado general

- 91 pruebas superadas y 622 aserciones correctas.
- PHPStan sin errores.
- Pint sin desviaciones.
- Build Vite de producción correcto.
- 24 migraciones aplicadas.
- Base de datos, claves foráneas y almacenamiento correctos.
- Cero sesiones KDP fallidas, lotes fallidos y trabajos de cola fallidos.
- Interfaz disponible en `http://127.0.0.1:8020/admin/login`.
- Login, redirección del panel, CSS y JavaScript responden correctamente.

## Auditoría de datos KDP

La base conserva 324 filas canónicas, 50 obras y 112 publicaciones. No se encontraron:

- catálogos sin obra o publicación;
- filas de libro sin catálogo o publicación;
- lotes de tipo desconocido;
- identidades de obra duplicadas;
- combinaciones ASIN/marketplace duplicadas;
- huellas de fila duplicadas;
- pagos duplicados;
- claves foráneas rotas.

## Idempotencia

Se creó una copia aislada en `/private/tmp/kdp-v5-idempotency.sqlite`. Dos reprocesados consecutivos del informe de Pedidos mantuvieron exactamente:

- 324 filas KDP;
- 50 obras;
- 112 publicaciones.

La base real conservó antes y después la huella SHA-256 `6e1dea8d74827d5ae1bfc498c23f45cf9f984f1860d27903111a9a01ae7d1bce`.

## Errores encontrados y corregidos

### Estado obsoleto de una sesión

Una sesión figuraba como fallida aunque su lote estaba completado. `KdpBulkImportService::summarize()` ahora reconstruye los contadores desde los lotes, considerando también duplicados y archivos sin lote. El reprocesado individual y el comando masivo vuelven a sincronizar la sesión. Se añadió una prueba de regresión.

### Protección de comandos destructivos

Durante la validación de instalación limpia se detectó que una ejecución incorrectamente aislada de `migrate:fresh` podía afectar la base local. Los datos se restauraron íntegramente desde `/private/tmp/kdp-author-manager-v5-before-report-semantics.sqlite`, se reaplicó la migración y se reprocesaron los informes.

La aplicación bloquea ahora los comandos destructivos fuera de `testing`. Sólo se habilitan expresamente con:

```bash
ALLOW_DESTRUCTIVE_DB_COMMANDS=true php artisan migrate:fresh --seed
```

La protección se comprobó sobre una copia temporal: el comando fue rechazado y la base real permaneció intacta.

## Comandos de validación

```bash
php artisan test --compact --do-not-cache-result
composer analyse
vendor/bin/pint --test app database tests
npm run build
php artisan app:health
```

## Estado final

La aplicación queda operativa, con la base restaurada y consistente. La única habilitación destructiva debe reservarse para bases temporales o prescindibles cuya ruta se haya verificado previamente.
