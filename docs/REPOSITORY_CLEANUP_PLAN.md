# Plan de limpieza y actualización del repositorio

Fecha de auditoría: 17 de agosto de 2026.

## Objetivo

Reducir archivos generados, copias históricas y código duplicado sin perder trabajo útil; dejar una única documentación vigente y asegurar que el proyecto pueda instalarse, probarse y compilarse desde un clon limpio.

El plan se ejecutó el 17 de agosto de 2026. Antes de retirar archivos se creó la copia recuperable `/private/tmp/kdp-author-manager-before-cleanup-20260817.tar.gz`, excluyendo dependencias regenerables, `.git`, secretos y logs.

## Resultado de ejecución

- `vendor/` dejó de estar versionado y permanece disponible localmente.
- Se retiraron 63 copias `*.bak`, los documentos ODT accidentales, scripts temporales, volcados SQL duplicados y el prototipo `kdp_project/`.
- Se eliminaron recursos, modelos y widgets alternativos que no estaban registrados ni referenciados.
- El README raíz y el índice documental se sustituyeron por instrucciones propias del proyecto.
- `.gitignore` cubre copias de entorno, backups y configuración local.
- Se retiró `@tailwindcss/vite` 4 porque el proyecto usa Tailwind 3 mediante PostCSS.
- Los activos publicados de Filament se conservan: `composer dump-autoload` ejecuta `filament:upgrade` y confirma que son parte del proceso actual de despliegue.
- Las migraciones son la fuente autoritativa; la instalación temporal creó 71 tablas y cargó datos demo correctamente.
- Validación final: Composer válido, 50 pruebas y 396 aserciones correctas, build Vite correcto y 78 rutas registradas.

## Estado comprobado

- Rama: `stabilization/error-review`.
- Laravel 11.54.0, PHP 8.4.20 y Composer 2.9.5 en el entorno auditado.
- `composer.json` supera `composer validate --strict`.
- La suite posterior a la limpieza supera 50 pruebas y 396 aserciones.
- `npm run build` termina correctamente con Vite 8.0.14.
- Las 15 migraciones registradas están aplicadas.
- El panel registra explícitamente 19 recursos y 8 widgets.
- El repositorio ocupa aproximadamente 254 MB y contiene 12.937 archivos versionados.
- El árbol de trabajo no está limpio: hay cambios modificados, eliminados y archivos sin seguimiento. Deben preservarse antes de cualquier limpieza.

## Hallazgos

### Prioridad crítica

1. `vendor/` está versionado aunque `/.gitignore` ya lo excluye: representa 12.446 de los 12.937 archivos seguidos por Git. Debe retirarse únicamente del índice y reconstruirse con `composer install`.
2. Hay 63 archivos `*.bak` versionados dentro de recursos Filament. Son copias de trabajo, no código de ejecución.
3. El README raíz es el texto genérico de Laravel y no explica el producto, instalación, configuración, credenciales de demostración ni pruebas.
4. El árbol contiene numerosos cambios sin consolidar. Limpiar antes de crear un punto recuperable podría mezclar cambios funcionales con eliminación de artefactos.

### Prioridad alta

1. Los documentos de resultados no coinciden con el estado actual:
   - `docs/ERROR_CORRECTION_REPORT.md` indica 44 pruebas y 150 aserciones.
   - `docs/TEST_PLAN_AND_RESULTS.md` indica 48 pruebas y 383 aserciones.
   - En el momento de la auditoría el resultado era 50 pruebas y 398 aserciones; tras retirar recursos obsoletos quedó en 50 pruebas y 396 aserciones.
2. `PLAN_CORRECCION_OPTIMIZACION.md` indica PHP 8.5.6 y Composer 2.9.8, mientras el entorno comprobado usa PHP 8.4.20 y Composer 2.9.5. También conserva resultados antiguos de 33 pruebas con deprecaciones.
3. `kdp_project/` contiene 22 documentos y artefactos de un prototipo. Algunos comandos y rutas describen recursos distintos a los activos, Docker no presente y comandos Artisan que deben verificarse antes de publicarse como instrucciones.
4. Hay un árbol alternativo `app/Filament/Resources/` con 22 archivos que no está registrado por el panel actual.
5. Existen recursos alternativos en `app/Filament/Admin/Resources/` para auditorías, eventos, idiomas, mercados, pagos y promociones. Varios apuntan a modelos o tablas que no forman parte del esquema activo.
6. Los widgets `MonthlyRoyaltiesChart`, `PendingTasksWidget` y `app/Filament/Widgets/StatsOverviewWidget` no están registrados. Deben revisarse como implementaciones sustituidas antes de retirarlos.

### Prioridad media

1. `.env.bak` no está cubierto por el patrón actual de `.gitignore`.
2. Hay artefactos locales o históricos en la raíz: `.env.odt`, `.gitignore.odt`, `.DS_Store`, scripts de corrección, `composer_old.json`, `fix_pages.php`, `kdp_ddl.sql`, `.deep-copilot/` y un archivo de workspace.
3. Hay 29 activos publicados de Filament versionados. Como `composer.json` ejecuta `filament:upgrade`, se debe decidir y documentar si el despliegue los conserva en Git o los regenera. No se deben eliminar sin comprobar el proceso de despliegue.
4. `database/schema.sql`, `kdp_ddl.sql` y las migraciones pueden representar fuentes de esquema distintas. Las migraciones deben ser la fuente autoritativa o los volcados deben declarar explícitamente su finalidad y fecha.
5. `package.json` combina Tailwind CSS 3.4 con `@tailwindcss/vite` 4. La compilación funciona, pero conviene alinear o justificar las versiones para evitar futuras incompatibilidades.

## Plan de ejecución

### Fase 0 — Preservar el estado actual

1. Revisar `git status` y separar cambios funcionales, archivos nuevos y eliminaciones ya existentes.
2. Crear un commit de estabilización o una copia recuperable antes de limpiar.
3. No mezclar cambios funcionales con la retirada de archivos generados.

Criterio de aceptación: todo archivo actual puede recuperarse y el punto de partida está identificado.

### Fase 1 — Crear una documentación canónica

1. Sustituir el README genérico por uno del KDP Author Manager con propósito, requisitos reales, instalación, variables de entorno, migraciones, datos demo, acceso al panel, compilación, pruebas y solución de problemas.
2. Crear `docs/README.md` como índice y marcar cada documento como vigente, histórico o pendiente de validar.
3. Unificar los resultados de pruebas en un único documento generado o actualizado en cada entrega; mover informes antiguos a `docs/history/` si aportan trazabilidad.
4. Extraer de `kdp_project/` únicamente la información que siga siendo válida. Archivar o retirar el resto después de compararlo con el código.
5. Evitar fijar versiones del entorno salvo que procedan de `composer.lock`, `package-lock.json` o del entorno de CI.

Criterio de aceptación: una persona puede instalar y ejecutar la aplicación siguiendo solo el README, y no hay instrucciones contradictorias.

### Fase 2 — Limpiar archivos generados y copias

1. Ampliar `.gitignore` para cubrir `*.bak`, `.env.*` con excepción explícita para `.env.example`, herramientas locales y archivos de workspace que el equipo no comparta.
2. Retirar `vendor/` del índice de Git sin borrar la copia local y confirmar que `composer install` lo reconstruye.
3. Retirar las 63 copias `*.bak`, los ODT accidentales, `.DS_Store`, logs y temporales solo después de comprobar que no contienen cambios únicos.
4. Decidir si los activos publicados de Filament se versionan; documentar el comando que los regenera.

Criterio de aceptación: un clon no contiene dependencias ni copias de seguridad y se reconstruye a partir de los archivos de bloqueo.

### Fase 3 — Consolidar código y estructura Filament

1. Tomar `AdminPanelProvider` como inventario inicial de los 19 recursos y 8 widgets activos.
2. Comparar cada recurso alternativo con su equivalente activo y buscar referencias en código y pruebas.
3. Retirar o migrar `app/Filament/Resources/`, los recursos raíz no registrados y los tres widgets sustituidos en commits independientes.
4. Revisar modelos huérfanos y confirmar que no existen migraciones, relaciones, comandos o vistas que dependan de ellos.
5. Ejecutar las pruebas de navegación y formularios después de cada grupo eliminado.

Criterio de aceptación: existe una sola implementación por entidad y todo recurso conservado está registrado o documentado como reutilizable.

### Fase 4 — Consolidar base de datos y scripts

1. Declarar las migraciones como fuente autoritativa del esquema.
2. Comparar `database/schema.sql` y `kdp_ddl.sql` con las migraciones; regenerar, documentar o retirar los volcados obsoletos.
3. Revisar scripts de corrección de la raíz. Convertir operaciones reutilizables en comandos, migraciones o documentación; retirar los scripts de una sola ejecución.
4. Verificar desde una base temporal con `php artisan migrate:fresh --seed`.

Criterio de aceptación: una base vacía produce el mismo esquema y datos demo sin pasos manuales externos.

### Fase 5 — Dependencias y frontend

1. Revisar dependencias directas realmente utilizadas en Composer y npm.
2. Resolver o documentar la combinación Tailwind 3 / plugin Vite 4.
3. Mantener `composer.lock` y `package-lock.json` como fuentes reproducibles.
4. Ejecutar auditorías de dependencias y tratar cada actualización en un cambio separado de la limpieza estructural.

Criterio de aceptación: instalación y compilación reproducibles, sin dependencias directas abandonadas o inexplicadas.

### Fase 6 — Validación final

Ejecutar, en un entorno limpio o temporal:

```bash
composer validate --strict
composer install
npm ci
npm run build
php artisan migrate:fresh --seed
php artisan test --compact
php artisan route:list --except-vendor
```

Completar con una prueba HTTP del acceso a `/admin`, dashboard, listados y formularios, además de comprobar claves foráneas y relaciones.

Criterio de aceptación: instalación limpia, compilación, migraciones, seeders, pruebas y navegación finalizan sin errores.

## Orden de commits recomendado

1. `docs: replace generic project documentation`
2. `chore: ignore local and backup artifacts`
3. `chore: stop tracking Composer dependencies`
4. `chore: remove reviewed backup and temporary files`
5. `refactor: remove obsolete Filament implementations`
6. `chore: consolidate schema and maintenance scripts`
7. `chore: align frontend dependencies`

Cada commit debe superar como mínimo `php artisan test --compact`; los que afecten frontend deben superar además `npm run build`.

## Archivos que requieren decisión antes de retirarlos

- `kdp_project/`: puede contener requisitos funcionales todavía no migrados.
- `public/css/filament/` y `public/js/filament/`: dependen del método de despliegue.
- `database/schema.sql` y `kdp_ddl.sql`: pueden servir para integraciones externas.
- `composer_old.json`: puede conservar contexto de una migración, pero no debe permanecer indefinidamente en la raíz.
- Scripts de corrección y archivos ya marcados como eliminados en el árbol de trabajo: primero hay que determinar si esas eliminaciones pertenecen a un cambio previo del usuario.

## Resultado esperado

El repositorio debe contener código fuente, migraciones, pruebas, documentación vigente y archivos de bloqueo; las dependencias, compilados, logs, copias locales y prototipos obsoletos deben poder regenerarse o quedar archivados fuera de la rama principal.
