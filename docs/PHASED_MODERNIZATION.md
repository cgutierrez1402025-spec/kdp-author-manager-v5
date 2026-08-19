# Modernización por fases

## Versiones y recuperación

- Base operativa preservada: tag `operational-v3-2026-08-17`, commit `5e72c48c`.
- Rama de evolución: `improvement/phased-modernization`.
- La base operativa no se modifica; cada bloque de la evolución se registra en commits independientes.

## Fases ejecutadas

### 0. Línea base y calidad

- Actualización a Laravel 12 y versiones compatibles de Filament, Livewire y herramientas de desarrollo.
- Auditoría de Composer sin vulnerabilidades conocidas.
- CI para backend y frontend, Pint, PHPStan y comandos Composer de calidad.
- Corrección del servicio de anclaje de ilustraciones y declaración explícita de sus dependencias.

### 1. Seguridad y aislamiento

- Consultas de recursos y widgets limitadas al propietario para el rol autor.
- Cachés del dashboard separadas por usuario.
- Acceso transversal reservado a roles internos autorizados.
- Pruebas de regresión para evitar exposición de obras ajenas.

### 2. Experiencia editorial

- Vista resumen de obra con accesos directos a manuscritos y publicaciones.
- Navegación agrupada según el ciclo editorial.
- Indicadores de contenido relacionado y acciones contextuales.

### 3. Formularios y consistencia

- Eliminación de campos de propietario manipulables desde formularios.
- Selectores filtrados según los permisos del usuario.
- Precarga segura de obra en los flujos iniciados desde su resumen.
- Unificación del formulario y tabla de tareas de IA.

### 4. Dominio y atomicidad

- Los cambios que crean una versión, aplican un anclaje e incrementan su uso se ejecutan en una transacción.
- Los servicios de publicaciones y regalías conservan sus límites transaccionales existentes.

### 5. Integraciones

- OpenAI usa Responses API y un modelo configurable, con `gpt-5.6-luna` como valor inicial orientado a volumen y coste.
- Se registra el identificador y consumo devuelto por OpenAI para facilitar observabilidad en los llamadores.
- KDP/Amazon ya no sustituye silenciosamente fallos reales por datos ficticios.
- El modo demo de KDP requiere `KDP_DEMO_MODE=true` y cuenta con pruebas específicas.

### 6. Operación

- Endpoint de salud disponible en `/up`.
- Tareas programadas protegidas frente a ejecuciones solapadas.
- CI ejecuta pruebas, análisis estático, estilo, auditoría y compilación de activos.

### 7. Validación y entrega

La entrega se considera apta cuando pasan:

```bash
composer validate --strict
composer audit
composer analyse
composer format:check
php artisan test
npm run build
php artisan schedule:list
```

## Configuración antes del despliegue

1. Mantener `KDP_DEMO_MODE=false` en producción.
2. Configurar las credenciales externas solo mediante secretos del entorno.
3. Ejecutar una copia de seguridad recuperable antes de migrar.
4. Ejecutar migraciones, cachés y trabajadores de cola conforme al entorno.
5. Verificar `/up`, el scheduler y los logs después del despliegue.
