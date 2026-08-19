# Plan de implementación del módulo de audiolibros

> Estado a 19/08/2026: el núcleo transversal de las fases 1–5 está implementado y probado. Incluye edición, narradores, producción, capítulos, activos versionados, pronunciaciones, QA, distribución, costes y regalías. Las audiciones/ofertas avanzadas y los importadores ACX/KDP de audio permanecen condicionados a disponer de muestras reales y se mantienen como ampliación; véase `RESULTADO_IMPLEMENTACION_AUDIOLIBROS.md`.

## Fase 0 — decisiones y muestras (1 semana)

- confirmar países/cuentas disponibles y derechos de audio;
- obtener ejemplos anonimizados de informes KDP Virtual Voice y ACX;
- validar métodos, estados, vigencias y vocabulario;
- aprobar ER, matriz de permisos y política de archivos.

Salida: ADR de arquitectura, fixtures y criterios de aceptación.

## Fase 1 — núcleo editorial (2 semanas)

- migraciones de ediciones, producción, narradores y pivote;
- enums/estados y relaciones;
- recursos Filament de edición y narrador;
- elegibilidad/derechos básica;
- policies por autor.

Salida: registrar varias ediciones de audio por obra sin archivos ni finanzas.

## Fase 2 — casting y contratos (2 semanas)

- audiciones, ofertas y contratos P4P/RS/RSP/DIY;
- presupuesto PFH, porcentajes y fechas;
- documentos privados y permisos financieros;
- alertas de exclusividad/consentimiento.

Salida: flujo humano y Voice Replica trazable.

## Fase 3 — capítulos, activos y QA (3 semanas)

- capítulos de audio y asignación de voces;
- versiones de archivos privados y hashes;
- pronunciaciones y revisiones;
- reglas técnicas configurables y resultados QA;
- progreso y checklist.

Salida: producción aprobable sin sobrescribir activos.

## Fase 4 — publicación y distribución (2 semanas)

- canales, territorios, IDs, precios y estados;
- publicación `audiobook` vinculada;
- exclusividad y KDP Select;
- panel de catálogo/distribución.

Salida: trazabilidad desde obra hasta canal publicado.

## Fase 5 — importación, regalías y pagos (3 semanas)

- parsers versionados KDP/ACX con fixtures reales;
- hechos de regalías de audio;
- costes, reparto y asignación de pagos;
- conciliación y ambigüedades;
- panel financiero por moneda/fuente.

Salida: rendimiento y liquidaciones auditables.

## Fase 6 — endurecimiento (1–2 semanas)

- pruebas de aislamiento, carga y archivos;
- backups/restauración;
- accesibilidad y rendimiento;
- documentación, migración de demo y monitorización.

## Dependencias y puertas de calidad

- No iniciar finanzas sin informes reales.
- No publicar sin derechos y QA aprobados.
- No habilitar Voice Replica sin consentimiento.
- No sumar monedas sin FX trazable.
- Cada fase debe incluir migraciones reversibles, tests, policies, seeders y documentación.

## MVP recomendado

Fases 0–3: inventario de audiolibros, método, narradores/contrato y control de producción. Distribución e ingresos pueden añadirse después sin rediseñar el núcleo.
