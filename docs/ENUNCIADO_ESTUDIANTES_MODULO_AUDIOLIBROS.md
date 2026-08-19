# Proyecto para estudiantes: módulo de gestión de audiolibros

## Contexto

KDP Author Manager gestiona obras, idiomas, versiones de manuscrito, publicaciones, tareas, regalías e importaciones KDP. Se desea añadir un módulo que controle audiolibros creados mediante KDP Virtual Voice, narración humana/DIY a través de ACX, contratación de productores y Voice Replica autorizado.

Lea previamente [INFORME_INTEGRACION_AUDIOLIBROS.md](INFORME_INTEGRACION_AUDIOLIBROS.md) y consulte las fuentes oficiales enlazadas. No codifique porcentajes o requisitos temporales sin fecha de vigencia.

## Objetivo

Diseñar e implementar un módulo completo que permita saber qué obras tienen ediciones de audio, cómo se produjeron, quién narró, qué contrato se utilizó, el avance por capítulos, controles de calidad, canales de distribución, costes, regalías y pagos.

## Alcance obligatorio

### Análisis

1. Diagrama entidad‑relación y justificación de cardinalidades.
2. Matriz que compare KDP Virtual Voice, ACX humano, Voice Replica, DIY y producción externa.
3. Estados y transiciones del proyecto desde idea hasta publicación/retirada.
4. Reglas de derechos, exclusividad, consentimiento de voz y privacidad.
5. Estrategia para datos estimados, definitivos, monedas y vigencias.

### Base de datos

Implemente migraciones reversibles para, como mínimo:

- `audiobook_editions`;
- `audiobook_productions`;
- `narrators` y `audiobook_narrators`;
- `audiobook_chapters`;
- `audio_assets` y versiones;
- `audiobook_pronunciations`;
- `audiobook_distributions`;
- comprobaciones de elegibilidad/QA;
- costes y regalías de audio.

Decida si audiciones/ofertas son tablas independientes y justifique la decisión. Añada índices, claves únicas, claves foráneas, estados y borrado apropiado. No use un booleano en `works` como sustituto del modelo.

### Modelos y dominio

- Relaciones Eloquent bidireccionales.
- Enums para método de narración, producción, estado, contrato y distribución.
- Servicios para transición de estados y cálculo de presupuesto, sin lógica financiera en controladores.
- Reglas para impedir publicación sin derechos, capítulos aprobados o QA.
- Auditoría de cambios sensibles.

### Formularios Filament

1. Listado de audiolibros con obra, idioma, método, estado, narrador, duración y canales.
2. Asistente de creación en pasos: origen, derechos, producción, voz/narrador, economía y distribución.
3. Gestor de narradores con muestras y consentimiento.
4. Casting con audiciones, evaluación y ofertas.
5. Producción por capítulos con versiones de archivos.
6. Formulario de pronunciaciones y dirección de voz.
7. QA con validaciones técnicas configurables.
8. Distribución, precios e identificadores externos.
9. Costes, regalías y reparto.

Utilice selectores limitados a obras del usuario. Muestre campos condicionales: una voz virtual no solicita PFH humano; Royalty Share exige exclusividad y porcentajes; Voice Replica exige consentimiento.

### Tablas y paneles

- progreso de producción;
- capítulos pendientes/rechazados;
- presupuesto previsto frente a coste real;
- duración y coste por hora final;
- distribución por canal;
- regalías separadas por moneda y fuente;
- pagos/repartos pendientes;
- alertas de derechos, exclusividad y QA.

### Archivos

Guarde archivos fuera de la base de datos y de rutas públicas. Registre hash, versión, duración y metadatos técnicos. Valide tipo/tamaño. Diseñe almacenamiento escalable y descarga autorizada. No es necesario implementar un editor de audio.

### Importación

Extienda el parser solo con fixtures anonimizados. Debe reconocer audiolibros sin confundir suscripción de ebook/audio cuando KDP no los separa. Las filas ambiguas quedan sin asignar y se resuelven mediante interfaz de conciliación.

### Seguridad

- aislamiento completo entre autores;
- permisos diferenciados para contratos, archivos y finanzas;
- consentimiento de voz replica;
- protección de datos de narradores;
- trazabilidad de IA;
- prevención de acceso directo a archivos privados.

## Historias de usuario mínimas

1. Como autor, creo una edición de audio desde una obra y versión concretas.
2. Como autor, compruebo elegibilidad para Virtual Voice sin asumir que tengo invitación.
3. Como titular, confirmo derechos de audio y territorios.
4. Como productor, registro una audición y una oferta P4P/Royalty Share/RSP.
5. Como editor, asigno narradores diferentes a capítulos.
6. Como técnico, cargo versiones y registro QA.
7. Como autor, apruebo o solicito revisiones.
8. Como gestor, publico en uno o varios canales.
9. Como contable, registro costes, regalías y reparto.
10. Como autor, veo alertas y rendimiento sin mezclar monedas.

## Pruebas obligatorias

- un autor no accede a audiolibros/archivos/contratos de otro;
- una obra admite varias ediciones de audio;
- no se duplica `(obra, idioma, edición, proveedor)` sin confirmación;
- estados inválidos son rechazados;
- Royalty Share/RSP exige distribución exclusiva;
- Voice Replica exige consentimiento vigente;
- QA falla con especificaciones fuera de rango;
- un archivo nuevo crea versión y no sobrescribe el anterior;
- cálculos PFH y reparto son reproducibles;
- monedas diferentes no se suman;
- una importación repetida es idempotente;
- datos ambiguos permanecen sin asignar;
- migraciones `up/down`, seeders y claves foráneas son correctos.

## Entregables

1. Documento de análisis y decisiones.
2. Diagrama ER y diagrama de estados.
3. Migraciones, modelos, enums, servicios, recursos Filament y políticas.
4. Factories/seeders con escenarios Virtual Voice, P4P, Royalty Share y DIY.
5. Suite automatizada.
6. Manual de usuario y guía de despliegue.
7. Registro de riesgos, limitaciones y trabajo futuro.

## Criterios de evaluación

- modelo e integridad: 25%;
- seguridad/autorización: 15%;
- formularios y experiencia: 15%;
- servicios y reglas: 15%;
- pruebas: 20%;
- documentación y trazabilidad: 10%.

Se penalizará inventar datos ausentes, almacenar audio en columnas BLOB, mezclar monedas, aceptar IA no autorizada, sobrescribir versiones o colocar reglas complejas directamente en recursos/controladores.

