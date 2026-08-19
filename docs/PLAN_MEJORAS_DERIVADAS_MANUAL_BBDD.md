# Plan de mejoras derivadas del manual profesional de Bases de Datos

## Criterio de lectura

El manual adjunto describe un proyecto didáctico MySQL 8/MongoDB más amplio que la aplicación Laravel. Sus ejemplos no son automáticamente requisitos de producción. Este plan adopta sólo ideas que aportan valor al producto y separa los laboratorios educativos de la base operativa portable.

## Situación tras esta revisión

Ya están implementados:

- histórico de precios por publicación, mercado y vigencia;
- observaciones temporales de valoración, reseñas y ranking;
- consentimiento revocable para analítica agregada;
- restricción contra periodos de precio solapados;
- diagnóstico operativo y verificación de despliegue en CI.

## Fase 1 — taxonomía y segmentación normalizadas

1. Sustituir progresivamente `genre`, `subgenre` y `target_audience` de texto por catálogos compatibles.
2. Crear categorías jerárquicas con `parent_id`, profundidad y prevención de ciclos.
3. Crear `work_categories` N:M con indicador de categoría principal y vigencia.
4. Crear públicos objetivo N:M con prioridad y notas.
5. Crear características editoriales abiertas: ilustrada, bilingüe, ejercicios, mapas o material descargable.
6. Mantener los campos antiguos durante una migración en dos versiones y generar un informe de equivalencias.

Criterio de aceptación: una obra puede tener múltiples categorías, públicos y características sin valores separados por comas ni ciclos en la taxonomía.

## Fase 2 — costes y rentabilidad comparable

1. Unificar un catálogo ampliable de tipos de coste.
2. Relacionar costes con obra y, opcionalmente, publicación, campaña, traducción o audiolibro.
3. Definir periodos contables y fuente de tipo de cambio.
4. Calcular beneficio y ROI como consultas derivadas, conservando importes originales.
5. Añadir pruebas que prohíban agregar monedas diferentes sin conversión fechada.

Criterio de aceptación: todo beneficio muestra periodo, monedas originales, costes incluidos y fuente FX.

## Fase 3 — especialización de publicaciones

1. Mantener `publications` como identidad comercial común.
2. Evaluar extensiones 1:1 `digital_publication_details` y `print_publication_details`.
3. Reutilizar `audiobook_editions` para audio, evitando duplicar narradores, activos y QA.
4. Modelar en digital DRM y tamaño; en impreso páginas, dimensiones, papel, tinta y coste.
5. Validar que los detalles corresponden al formato declarado.

Criterio de aceptación: no aparecen columnas de impresión vacías en ebooks ni atributos de DRM en impresos.

## Fase 4 — inteligencia de decisión con privacidad

1. Crear un mart mensual seudonimizado sólo con autores que mantengan `analytics_opt_in` activo.
2. Separar OLTP de agregados analíticos regenerables.
3. Aplicar umbrales mínimos de grupo para evitar reidentificación.
4. Incorporar análisis por género, formato, longitud, precio, mercado, serie y campaña.
5. Mostrar siempre unidades, regalías, ingresos y beneficio como conceptos diferentes.
6. Documentar procedencia, fecha, cobertura y limitaciones de cada indicador.

Criterio de aceptación: retirar el consentimiento excluye al autor de futuras reconstrucciones del mart sin borrar sus datos operativos legítimos.

## Fase 5 — rendimiento y SQL profesional

1. Capturar consultas reales del dashboard y probarlas con 10.000, 50.000 y 100.000 filas.
2. Guardar planes `EXPLAIN` de referencia para MySQL y verificar índices compuestos.
3. Eliminar N+1 y agregaciones no acotadas.
4. Medir duración y memoria de CSV/XLSX/ZIP.
5. Mover los lotes grandes a cola conservando el modelo de sesiones.
6. Definir presupuestos: listados <500 ms y panel agregado <2 s en el conjunto acordado.

## Fase 6 — laboratorio MySQL 8 separado

1. Crear `database/labs/mysql8` con vistas, funciones, procedimientos y triggers del temario.
2. No incluir esos objetos en migraciones portables de producción.
3. Proporcionar scripts de instalación y reversión sobre una base desechable.
4. Relacionar cada práctica con una consulta Eloquent equivalente.
5. Añadir ejercicios de transacción, `SAVEPOINT`, concurrencia, auditoría y `EXPLAIN`.

## Fase 7 — proyección MongoDB educativa

1. Definir un documento de catálogo derivado y versionado, sin convertir MongoDB en fuente de verdad.
2. Exportar únicamente datos autorizados y no sensibles.
3. Comparar incrustación y referencias con el modelo relacional.
4. Añadir JSON Schema, índices, CRUD y agregaciones reproducibles.
5. Regenerar la colección desde SQL para demostrar trazabilidad.

## Fase 8 — evidencias para prácticas profesionales

Cada incremento debe incluir:

- requisito y políticas de negocio;
- diagrama y diccionario de datos;
- migración reversible;
- datos de prueba positivos y negativos;
- consulta y plan de ejecución;
- evidencia de seguridad y privacidad;
- manual de uso;
- defensa breve de decisiones alternativas.

## Orden recomendado

Taxonomía → costes/FX → especialización → mart privado → rendimiento → laboratorios MySQL → MongoDB. No debe iniciarse el mart antes de normalizar dimensiones y definir moneda, consentimiento y granularidad temporal.
