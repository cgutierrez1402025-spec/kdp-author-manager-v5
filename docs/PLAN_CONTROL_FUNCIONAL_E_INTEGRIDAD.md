# Plan de control funcional e integridad

## Objetivo

Detectar antes de cada entrega los errores habituales en una aplicación Laravel + Filament de gestión editorial:

- formularios que no insertan o modifican datos;
- relaciones obligatorias que se pierden o apuntan a otro registro;
- usuarios que pueden ver o modificar datos ajenos;
- valores de catálogos inexistentes o desactivados;
- estados inválidos y transiciones incoherentes;
- duplicados, huérfanos y eliminaciones accidentales;
- tablas Filament que fallan al ordenar, filtrar o ejecutar acciones;
- importaciones que dejan filas parciales o datos derivados desactualizados.

## Situación comprobada

La aplicación dispone actualmente de:

- `AdminResourcesSmokeTest`: renderiza índices, dashboard, formularios de alta y relaciones Filament.
- `FormRelationshipIntegrityTest`: comprueba inserciones de fuentes, tareas y eventos con sus usuarios y obras.
- `ModelRelationshipsTest`: resuelve las relaciones declaradas de los modelos contra datos sembrados.
- pruebas de integridad editorial para idioma, manuscrito, publicación y marketplace.
- pruebas de importación KDP, reprocesado idempotente, pagos y catálogo.
- diagnóstico `php artisan app:health` para base de datos, claves foráneas, almacenamiento y migraciones.

La suite completa se ejecutó con resultado correcto: 93 pruebas y 648 aserciones.

## Modelo relacional que debe protegerse

| Área | Cardinalidad y regla |
|---|---|
| Usuario - Obra | Un usuario tiene muchas obras; una obra pertenece a un usuario. Nunca se permite seleccionar una obra de otro usuario. |
| Obra - Idioma | Una obra tiene uno o varios `work_languages`; cada fila usa un código existente en `languages`. El idioma original de la obra debe tener una fila correspondiente. |
| Obra - Género | Muchos a muchos; el formulario limita a tres géneros, que corresponde al máximo operativo de categorías KDP por publicación. |
| Género - Subgénero | Un género tiene muchos subgéneros; cada subgénero sólo pertenece a un género padre. |
| Obra - Subgénero | Muchos a muchos; el formulario limita a tres y debe impedir combinaciones incompatibles con los géneros elegidos. |
| Obra - Manuscrito | Una obra tiene muchas versiones; cada versión pertenece a un idioma de esa obra. |
| Obra - Publicación | Una obra tiene muchas publicaciones; cada publicación pertenece además a idioma, manuscrito, plataforma y marketplace compatibles. |
| Obra - Tarea | Una obra tiene muchas tareas. La tarea puede apuntar opcionalmente a una publicación de la misma obra. |
| Obra - Checklist | Una obra tiene muchas checklists y cada checklist muchos elementos. |
| Publicación - Promoción | Una publicación tiene muchas promociones; una promoción tiene muchos costes y resultados diarios. No se permiten promociones activas solapadas. |
| Informe - Pago KDP | Una fila de pago puede materializar un pago; la asignación conserva la fila fuente y puede quedar sin asignar si no hay publicación inequívoca. |

## Controles automáticos obligatorios

### 1. Formularios CRUD

Para cada recurso con inserción y modificación se debe comprobar:

1. alta válida desde Livewire/Filament;
2. persistencia de los campos y relaciones;
3. edición de un campo existente;
4. validación de campos obligatorios, formatos, longitudes y fechas;
5. rechazo de relaciones de otro usuario;
6. eliminación o baja sólo cuando la política lo permita;
7. que el registro aparezca en el índice después de guardar.

Recursos prioritarios:

- obras;
- idiomas de obra;
- géneros y subgéneros;
- manuscritos y capítulos;
- publicaciones;
- tareas y tipos de tarea;
- checklists y elementos;
- promociones y costes;
- pagos manuales y pagos importados.

### 2. Integridad de relaciones

Cada prueba de alta debe verificar las dos direcciones de la relación:

- `work->publications` y `publication->work`;
- `work->workLanguages` y `workLanguage->work`;
- `work->tasks`, `task->work` y, cuando exista, `task->publication`;
- `work->checklists` y `checklist->items`;
- `publication->priceHistories`, `marketObservations`, `kdpMetadata` y `tasks`;
- `genre->subgenres`, `genre->works` y `subgenre->works`;
- `kdpPayment->allocations` y `allocation->reportRow`.

También se debe ejecutar `PRAGMA foreign_key_check` y probar eliminaciones en cascada o puesta a `NULL` según la migración.

### 3. Autorización y aislamiento

Para cada recurso propiedad del autor:

- autor A no puede ver el registro de autor B;
- autor A no puede editarlo ni eliminarlo;
- los selects filtrados no muestran opciones de B;
- las acciones de relación comprueban tanto el propietario como el registro relacionado;
- un administrador puede consultar todos los datos permitidos.

### 4. Catálogos y estados

Los desplegables deben leer tablas de catálogo y no listas duplicadas en varios formularios:

- `languages` para idiomas;
- `genres` y `subgenres` para clasificación;
- `task_types` para tipos de tarea.

Pendiente de completar: convertir los estados de obra en catálogo administrable. Hasta entonces, los estados deben validarse contra el flujo editorial definido y no aceptar cadenas arbitrarias.

### 5. Tablas Filament

Para cada tabla con varios registros se debe comprobar:

- renderizado con cero, uno y muchos registros;
- búsqueda, filtros, paginación y orden ascendente/descendente;
- columnas ocultables y redimensionables;
- acciones de fila y acciones de cabecera en el lugar correcto;
- que las columnas calculadas tengan una consulta de ordenación explícita o no se marquen como ordenables;
- que no se use `CreateAction` dentro de `bulkActions`.

### 6. Importaciones y pagos

Cada importación debe probar:

- detección del tipo y periodo;
- validación de cabeceras;
- archivo duplicado sin duplicar filas;
- transacción atómica ante una fila inválida;
- reprocesado idempotente;
- conservación del archivo original;
- materialización de pagos sólo cuando exista identificador de pago;
- pagos sin publicación inequívoca en estado `unallocated`;
- no sustituir errores del proveedor por éxitos simulados.

## Plan de ejecución por fases

### Fase 1: controles base

- Mantener `php artisan test` como puerta de entrada.
- Ejecutar `php artisan app:health`.
- Ejecutar `php artisan migrate --pretend` en CI.
- Añadir una prueba de humo para cada recurso nuevo.
- Revisar `PRAGMA foreign_key_check` después de sembrar datos.

### Fase 2: CRUD y relaciones

- Añadir pruebas Livewire de alta y edición para obra, idioma de obra, publicación y tarea.
- Añadir prueba de tarea general de obra y tarea específica de publicación.
- Añadir prueba de checklist y elemento asociado.
- Añadir prueba de género/subgénero y límites máximos.
- Añadir pruebas de autorización cruzada.

### Fase 3: reglas de negocio

- Validar que el idioma de manuscrito pertenece a la obra.
- Validar que publicación, manuscrito y `work_language` pertenecen a la misma obra.
- Validar que la publicación pertenece al marketplace de la plataforma seleccionada.
- Validar transiciones de estado.
- Validar que una tarea con publicación no apunta a otra obra.
- Validar promociones activas solapadas y costes con importe/moneda válidos.

### Fase 4: importaciones y recuperación

- Probar CSV, XLSX y ZIP.
- Probar archivos vacíos, cabeceras desconocidas, fechas inválidas y separadores regionales.
- Confirmar resumen de sesión tras éxito parcial.
- Confirmar que reprocesar no duplica pagos, catálogo ni filas.
- Registrar errores con fila y solución sugerida.

### Fase 5: interfaz y regresión

- Probar tablas en escritorio y móvil.
- Pulsar cada cabecera ordenable.
- Redimensionar columnas largas y confirmar desplazamiento horizontal.
- Revisar hints, estados vacíos, mensajes de validación y permisos.
- Ejecutar la suite completa y compilar Vite antes de publicar.

## Criterios de aceptación por entrega

Una entrega no se considera lista si ocurre cualquiera de estas situaciones:

- una ruta Filament devuelve 500 al renderizar;
- un alta válida no crea el registro o su relación;
- una edición válida no conserva los cambios;
- un usuario puede consultar o modificar datos de otro usuario;
- existe una clave foránea huérfana;
- una tabla ordenable lanza una excepción SQL o Filament;
- una importación parcial deja datos derivados inconsistentes;
- una migración nueva no tiene datos mínimos de catálogo cuando el formulario los necesita.

## Comandos de control

```bash
php artisan migrate --pretend
php artisan app:health
php artisan test
npm run build
```

Para cambios de un recurso concreto, ejecutar primero su prueba filtrada y después la suite completa.
