# Resultado de la implementación del módulo de audiolibros

Fecha de verificación: 19 de agosto de 2026.

## Alcance ejecutado

El panel incorpora dos entradas en el grupo **Publicaciones**:

- **Audiolibros** (`/admin/audiolibros`), para crear y editar una edición vinculada a una obra, método de producción, derechos, estado, duración, precio y regalía prevista.
- **Narradores y voces** (`/admin/narradores`), para gestionar personas, voces virtuales y réplicas, idiomas, proveedor y vigencia del consentimiento.

El modelo conserva trazabilidad de los procesos que después podrán tener formularios especializados: producción y contrato, asignación de narradores, capítulos, versiones de archivos, diccionario de pronunciación, distribución por canal y territorio, costes, regalías y resultados de control de calidad.

## Tablas añadidas

| Tabla | Finalidad y control de duplicados |
|---|---|
| `audiobook_editions` | Una edición de audio por obra/idioma/proyecto, con derechos y ciclo de vida. |
| `narrators` | Identidad de persona o voz; pertenece al usuario. |
| `audiobook_narrators` | Reparto y función; única por edición, narrador y rol. |
| `audiobook_productions` | Proveedor, contrato, PFH/reparto, presupuesto y fechas. |
| `audiobook_chapters` | Orden de producción; único por edición y posición. |
| `audio_assets` | Archivo, hash, métricas y versión; no sobrescribe entregas anteriores. |
| `audiobook_pronunciations` | Términos y pronunciaciones aprobadas. |
| `audiobook_quality_checks` | Regla versionada, evidencia y revisor. |
| `audiobook_distributions` | Canal, mercado, territorio, precio y estado; clave compuesta anti-duplicado. |
| `audiobook_costs` | Costes reales por tipo, fecha y moneda. |
| `audiobook_royalties` | Unidades e ingresos por edición, distribuidor, mercado y periodo. |

Las claves foráneas evitan registros huérfanos. Los importes conservan moneda y no deben agregarse entre divisas sin una conversión trazable.

## Reglas implantadas

`AudiobookWorkflowService` controla las transiciones de estado. Para pasar de `ready` a `published` exige:

1. derechos de audio confirmados;
2. al menos un capítulo y todos aprobados;
3. al menos un activo y todos con QA superado;
4. ningún control de calidad fallido;
5. consentimiento vigente de todos los narradores cuando el método sea réplica de voz.

Los listados se filtran por `user_id`, de modo que un autor sólo consulta sus audiolibros y voces. Los administradores conservan la visión global.

## Datos de demostración

`ComprehensiveDemoSeeder` crea una edición ficticia coherente con narrador, contrato PFH, capítulo, MP3 versionado, pronunciación, control técnico, distribución Audible, coste y regalía. No representa una integración activa con Audible/ACX ni prueba que esas condiciones comerciales sigan vigentes.

## Límites pendientes y decisión

No se ha simulado un importador financiero de audiolibros sin muestras oficiales reales. Los informes de KDP ya se importan en `kdp_report_rows`; un informe ACX o de Virtual Voice debe incorporarse mediante un parser versionado y fixtures anonimizados antes de mapearlo a `audiobook_royalties`. Esto evita inventar columnas, perder datos o atribuir pagos a una obra incorrecta.

Las tablas de audiciones, ofertas y cláusulas contractuales detalladas quedan como ampliación didáctica descrita en el enunciado de estudiantes. El núcleo implementado permite añadirlas sin modificar la identidad de las ediciones ni los activos existentes.

## Verificación

- Migraciones verificadas sobre SQLite en memoria y aplicadas de forma aditiva a la base local.
- Datos KDP locales preservados: 3 lotes y 169 filas normalizadas tras la migración.
- Pruebas específicas: trazabilidad completa, publicación válida, bloqueo de réplica sin consentimiento y acceso a ambos recursos.
- Suite completa: **67 pruebas y 516 aserciones, todas correctas**.
- Antes de migrar se creó `/private/tmp/kdp-author-manager-v5-before-audiobooks.sqlite` como copia recuperable de la base local.
