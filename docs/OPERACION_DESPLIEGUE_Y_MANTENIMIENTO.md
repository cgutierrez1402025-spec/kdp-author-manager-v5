# Operación, despliegue y mantenimiento

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

## Configuración mínima

Configure `APP_URL`, `APP_KEY`, base de datos, sesión, caché, cola, correo y zona horaria. Los secretos de proveedores externos solo se guardan en `.env`. `KDP_DEMO_MODE=true` es exclusivo de demostración.

## Migraciones

En producción:

```bash
php artisan migrate --force
```

Realice una copia de seguridad antes. No use `migrate:fresh` con datos que deban conservarse.

## Almacenamiento

Los informes KDP están en el disco `local`, bajo una ruta privada. El servidor web no debe publicar `storage/app/private`. Configure copias de seguridad cifradas y una política de retención. Eliminar un lote debe tratarse como una operación auditada porque elimina sus filas derivadas.

## Colas y tareas programadas

La configuración predeterminada usa la cola de base de datos. En producción ejecute un worker supervisado. Los comandos disponibles incluyen revisión de tareas vencidas, promociones, estadísticas de manuscrito, copia KDP y sincronización simulada/configurada.

## Caché

Tras cambios de código o configuración:

```bash
php artisan optimize:clear
php artisan optimize
```

Los widgets heredados usan caché por usuario. El widget KDP consulta el detalle actual para reflejar inmediatamente un reprocesado.

## Despliegue

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Use HTTPS, `APP_DEBUG=false`, permisos mínimos, cookies seguras y un proceso separado para colas. Compruebe que `vendor`, `storage` y `bootstrap/cache` tienen permisos adecuados.

## Observabilidad

Revise `storage/logs`, trabajos fallidos, lotes `failed`, filas con errores y antigüedad de la última importación. No registre claves API, cookies, contenido completo de manuscritos ni datos bancarios.

## Copias de seguridad

Incluya base de datos y archivos KDP privados. Pruebe restauraciones periódicas. Una copia sin los archivos originales permite ver las filas, pero impide reprocesar lotes.

