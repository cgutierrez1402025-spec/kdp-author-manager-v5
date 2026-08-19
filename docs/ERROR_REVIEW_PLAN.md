# Plan de revisión de errores

## Objetivo

Validar que KDP Author Manager se pueda instalar, utilizar y mantener sin errores de integridad, autorización ni compatibilidad entre Laravel, Filament, Livewire y la base de datos.

## 1. Instalación y entorno — prioridad crítica

- Ejecutar una instalación desde una base vacía con `php artisan migrate:fresh --seed` sobre una base temporal.
- Verificar versiones de PHP, Laravel, Filament, Livewire, Composer y Node.
- Comprobar que no haya migraciones pendientes.
- Revisar que `.env.example` contenga todas las variables necesarias sin secretos.
- Probar cachés de configuración, rutas y vistas mediante `php artisan optimize`.

Criterio de aceptación: migraciones y seeders terminan correctamente tanto en SQLite de pruebas como en el motor elegido para producción.

## 2. Integridad del modelo de datos — prioridad crítica

- Ejecutar comprobaciones de claves foráneas y registros huérfanos.
- Verificar restricciones únicas de ASIN, regalías mensuales e idiomas por obra.
- Probar eliminaciones y actualizaciones en cascada.
- Contrastar migraciones, `$fillable`, casts y relaciones Eloquent.
- Confirmar que los valores obligatorios tienen validación o un valor predeterminado.

Criterio de aceptación: ninguna violación referencial y todas las operaciones fallidas se revierten de forma atómica.

## 3. Autenticación y autorización — prioridad crítica

- Comprobar acceso anónimo, registro, inicio y cierre de sesión y recuperación de contraseña.
- Validar los roles `admin`, `author`, `editor` y `accountant`.
- Probar permisos directos y heredados por rol.
- Confirmar que cada autor solo puede consultar y modificar sus datos.
- Verificar que un identificador manipulado en una URL no permita acceder a registros de otro autor.

Criterio de aceptación: las pruebas cubren acceso permitido y denegado para cada rol y recurso sensible.

## 4. Flujo editorial — prioridad alta

- Crear y editar una obra con su idioma original.
- Crear versiones padre e hijas y recalcular capítulos, imágenes y palabras.
- Publicar únicamente manuscritos finales pertenecientes a la obra e idioma seleccionados.
- Validar plataforma, marketplace, moneda, formato, ISBN y ASIN.
- Comprobar metadatos KDP y periodos KDP Select.

Criterio de aceptación: funciona el recorrido obra → idioma → manuscrito → publicación sin introducir combinaciones incoherentes.

## 5. Regalías y promociones — prioridad alta

- Importar o crear regalías sin duplicar publicación, año y mes.
- Contrastar totales, monedas, ventas, costes y ROI.
- Verificar promociones solapadas y fechas inválidas.
- Probar periodos sin ventas, costes cero y valores negativos rechazados.

Criterio de aceptación: los cálculos coinciden con casos conocidos y las operaciones financieras conservan trazabilidad.

## 6. IA, importaciones y archivos — prioridad alta

- Simular respuestas válidas, errores, timeouts y límites del proveedor de IA.
- Evitar almacenar claves API en texto visible o registros de actividad.
- Validar tipo, tamaño, codificación y contenido de archivos importados.
- Probar reintentos de colas y evitar que un mismo archivo se procese dos veces.
- Revisar permisos de almacenamiento y eliminación de archivos temporales.

Criterio de aceptación: los fallos externos no corrompen datos y pueden diagnosticarse o reintentarse.

## 7. Interfaz y compatibilidad — prioridad media

- Abrir todos los índices y formularios Filament.
- Probar búsqueda, filtros, ordenación, paginación y acciones masivas.
- Revisar diseño responsive, navegación con teclado, etiquetas y mensajes en español.
- Probar estados vacíos, grandes volúmenes y textos largos.

Criterio de aceptación: no hay respuestas HTTP 500 ni errores JavaScript en los recorridos principales.

## 8. Rendimiento y operación — prioridad media

- Detectar consultas N+1 y revisar índices de claves de búsqueda.
- Medir paneles con al menos 1.000 obras y varios años de regalías.
- Verificar colas, tareas programadas, logs, copias de seguridad y restauración.
- Ejecutar análisis de dependencias y revisar configuración de producción.

Criterio de aceptación: tiempos de respuesta acordados, restauración ensayada y tareas operativas documentadas.

## Comandos de control continuo

```bash
php artisan migrate:status
php artisan test --compact
npm run build
php artisan route:list --except-vendor
php artisan db:show --counts
```

En SQLite se puede añadir:

```bash
sqlite3 database/database.sqlite 'PRAGMA foreign_key_check;'
```

## Registro de incidencias

Cada error debe registrar: identificador, fecha, entorno, módulo, pasos para reproducir, resultado esperado, resultado observado, severidad, evidencia, causa raíz, corrección, prueba de regresión y versión donde quedó resuelto.
