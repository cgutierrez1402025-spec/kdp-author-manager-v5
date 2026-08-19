# KDP Author Manager v5

La versión 5 incorpora importación auditable de los informes CSV/XLSX descargados desde Amazon KDP. En el panel de administración, la opción **Publicaciones → Importar informes KDP** permite seleccionar hasta 20 archivos simultáneos —o un ZIP—, detectar automáticamente regalías, ventas, pedidos, KENP, pagos e históricos y procesarlos como una sesión. El panel muestra unidades, regalías por moneda, KENP, títulos, marketplaces y evolución diaria directamente desde los datos normalizados.

Cada archivo se conserva en almacenamiento privado, se registra como lote y sus filas se normalizan en `kdp_report_rows`, manteniendo también los valores originales. Las reimportaciones se detectan mediante huellas SHA-256 y las filas se vinculan con publicaciones propias por ASIN y formato cuando existe una coincidencia inequívoca.

Los títulos ausentes se registran en `kdp_catalog_items` y se materializan automáticamente como obras y publicaciones provisionales. Las obras con igual título y autor comparten identidad aunque aparezcan en varios formatos o mercados. En **Catálogo detectado KDP** pueden revisarse y completar idioma, manuscrito y clasificación, que nunca se inventan si Amazon no los proporciona.

Desde ese catálogo puede utilizarse **Crear obra y edición** para incorporarlos a `works`, o **Vincular a obra** cuando ya existen. Los informes de pagos se proyectan en **Publicaciones → Pagos KDP** y permanecen sin asignar a una obra mientras Amazon no proporcione una relación inequívoca.

La versión incluye además el núcleo operativo de audiolibros en **Publicaciones → Audiolibros** y **Narradores y voces**. Permite relacionar cada edición de audio con su obra, idioma y manuscrito; registrar producción humana, voz virtual, réplica de voz o modelos híbridos; y conservar capítulos, activos versionados, pronunciaciones, controles de calidad, distribución, costes y regalías. El servicio de flujo impide publicar sin derechos confirmados, capítulos y activos aprobados o, en una réplica, consentimiento vigente.

Cada publicación dispone de históricos de precio y observaciones de mercado para analizar cambios sin sobrescribir el pasado. El perfil permite aceptar o retirar de forma explícita la participación en analítica agregada. El diagnóstico operativo puede ejecutarse con `php artisan app:health`.

Aplicación web para gestionar el ciclo editorial de obras publicadas mediante Amazon KDP: manuscritos, publicaciones, metadatos, regalías, promociones, eventos, fuentes, ilustraciones y tareas asistidas por IA.

El panel de administración está construido con Laravel 12, Filament 3, Livewire 3, SQLite/MySQL y Vite.

## Requisitos

- PHP 8.2 o posterior, con las extensiones requeridas por Laravel y PDO para la base elegida.
- Composer 2.
- Node.js compatible con Vite 8 y npm.
- SQLite para el entorno local predeterminado, o MySQL configurado mediante `.env`.

Las versiones exactas de las dependencias están fijadas en `composer.lock` y `package-lock.json`.

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

Abra `http://127.0.0.1:8000/admin`.

## Acceso de demostración

- Administrador: `admin@kdpmanager.local`
- Contraseña: `password`
- Autor: `author@example.com`
- Contraseña: `password`

Estas credenciales proceden de `DatabaseSeeder` y solo deben utilizarse en desarrollo. Cámbielas o desactive los datos demo antes de desplegar la aplicación.

## Desarrollo

Para mantener Vite observando cambios:

```bash
npm run dev
```

Después de modificar configuración, rutas o vistas puede limpiar las cachés con:

```bash
php artisan optimize:clear
```

## Pruebas y calidad

```bash
composer validate --strict
composer quality
npm run build
```

Para comprobar la instalación completa sobre una base prescindible:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh` elimina todas las tablas de la base configurada. No debe ejecutarse contra una base con información que se quiera conservar.

## Configuración

La plantilla `.env.example` usa SQLite, español y la zona horaria de Madrid. Para MySQL, configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

Las integraciones externas y proveedores de IA se configuran en `.env` siguiendo las claves declaradas en `config/services.php`. `KDP_DEMO_MODE` debe activarse expresamente para usar datos simulados; con el modo desactivado, la aplicación comunica credenciales incompletas o errores del proveedor. No se deben versionar secretos ni copias de archivos `.env`.

## Documentación

El índice de documentación técnica se encuentra en [`docs/README.md`](docs/README.md). Incluye el plan de pruebas, las relaciones de formularios, el informe de estabilización y el plan de limpieza del repositorio.

## Estructura relevante

- `app/Filament/Admin`: recursos, páginas y widgets del panel activo.
- `app/Models`: entidades y relaciones del dominio.
- `app/Services`: flujos editoriales, regalías, promociones e IA.
- `database/migrations`: fuente autoritativa del esquema.
- `database/seeders`: usuarios y datos coherentes de demostración.
- `tests`: pruebas unitarias y funcionales.
- `docs`: documentación técnica vigente e histórica.

## Despliegue

En producción deben instalarse dependencias desde los archivos de bloqueo, compilarse los activos y ejecutar las migraciones de forma explícita. Use credenciales propias, `APP_DEBUG=false`, HTTPS y una copia de seguridad antes de migrar.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

## Licencia

MIT.
