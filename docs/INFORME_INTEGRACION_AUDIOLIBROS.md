# Informe de integración de audiolibros

**Fecha:** 19 de agosto de 2026  
**Objetivo:** ampliar KDP Author Manager para gestionar ediciones de audio producidas mediante KDP Virtual Voice, ACX o producción propia/contratada.

## 1. Modalidades actuales

### KDP Audiobooks with Virtual Voice

Es una beta por invitación para ebooks elegibles. Actualmente permite producir gratis una edición de audio con voz generada, elegir entre voces, fijar precio entre 3,99 y 14,99 USD, previsualizar y editar antes de publicar. Se distribuye inicialmente en el mercado estadounidense y puede tardar hasta 72 horas en aparecer. Fuente: [inicio con Virtual Voice](https://kdp.amazon.com/en_US/help/topic/GFAQU3LUEHCRB8KD).

Amazon indica más de 80 voces, entre ellas inglés estadounidense/británico/australiano, español latinoamericano/castellano, francés e italiano; se puede escoger voz por capítulo. La edición queda etiquetada como voz virtual. Fuente: [información de Virtual Voice](https://kdp.amazon.com/en_US/help/topic/G3QRL9HQNF273Q2H).

Elegibilidad relevante:

- ebook publicado y elegible por Amazon;
- idioma principal admitido: inglés, español, italiano o francés;
- no ser principalmente dominio público;
- autor/contribuidor principal;
- tabla de contenidos Kindle/NCX válida;
- menos de 1.001 capítulos y aproximadamente menos de 240.000 palabras por capítulo;
- caracteres y HTML compatibles;
- derechos de audio disponibles.

Fuente: [elegibilidad y problemas](https://kdp.amazon.com/en_US/help/topic/GJSXT4GZLP4PL62B).

Virtual Voice Studio permite cambios de voz, velocidad, pausas, pronunciación y ubicaciones de inicio/fin. Imágenes no se narran; tablas, pies de foto, código, números, notas y fragmentos en otro idioma requieren revisión. Fuente: [Virtual Voice Studio](https://kdp.amazon.com/en_US/help/topic/GLD3SMMB6B99D5FW).

La regalía actual de la beta es 40%. Distribuye mediante Audible, Amazon, Alexa y Amazon Music Unlimited. KDP Select puede arrastrar exclusividad del audio e inclusión en Audible Plus; no admite actualmente A+ Content, Amazon Ads, preventas, promociones gratuitas ni Countdown Deals para estos audiolibros. Fuente: [precios, regalías e informes](https://kdp.amazon.com/en_US/help/topic/GHJW2N8GLTQLK9TY).

### ACX con narración humana o producción propia

ACX permite:

- **DIY/autonarración:** el titular aporta audio terminado;
- **Pay for Production (P4P):** pago por hora finalizada al productor;
- **Royalty Share:** sin pago inicial y reparto de regalías;
- **Royalty Share Plus:** pago reducido por hora más reparto;
- oferta directa o convocatoria con audiciones a narradores/productores.

Fuente: [regalías y modelos ACX](https://help.acx.com/s/article/how-royalties-work) y [ofertas](https://help.acx.com/s/article/make-an-offer).

Desde el 26 de mayo de 2026, el nuevo modelo ACX indicado para títulos nuevos ofrece 50% en distribución exclusiva y 30% no exclusiva; los acuerdos de reparto dividen la parte aplicable. Deben almacenarse modelo y vigencia, no codificar porcentajes fijos.

La distribución ACX cubre Audible, Amazon y Apple Books; la no exclusiva permite otros distribuidores. El flujo incluye reclamar título, derechos/territorios, manuscrito, audiciones/oferta, producción, revisión del titular, pago si aplica, QA y distribución.

### ACX Voice Replica

ACX ofrece una beta limitada en la que un narrador autorizado crea y controla una réplica de su propia voz, acepta proyectos y revisa la producción final. No equivale a TTS externo libre. Fuente: [Voice Replica para titulares](https://help.acx.com/s/article/voice-replica-help-for-rights-holders).

ACX prohíbe TTS/IA no autorizado en entregas normales. Los archivos deben tener narración humana salvo autorización específica. Fuente: [requisitos de audio ACX](https://help.acx.com/s/article/what-are-the-acx-audio-submission-requirements).

## 2. Requisitos técnicos de audio humano/DIY

ACX exige, entre otros:

- créditos de apertura y cierre;
- muestra comercial de hasta cinco minutos;
- un capítulo/sección por archivo y máximo 120 minutos por archivo;
- MP3 CBR de 192 kbps o más, 44,1 kHz;
- formato mono o estéreo consistente;
- RMS entre −23 dB y −18 dB;
- picos inferiores a −3 dB;
- ruido inferior a −60 dB RMS;
- audio consistente y sin ruidos/errores;
- QA antes de distribución.

Estos valores deben ser reglas configurables/versionadas, pues el proveedor puede cambiarlos.

## 3. Impacto sobre el modelo actual

Una obra puede tener varias ediciones de audio: idiomas, narradores, revisiones, territorios o distribuidores distintos. No debe añadirse un simple booleano `has_audiobook` a `works`.

`publications.format` puede aceptar `audiobook`, pero no modela producción, narradores, contratos, capítulos de audio, calidad ni distribución múltiple. Se requiere un subdominio propio enlazado con `work`, `work_language`, `manuscript_version` y, al publicarse, `publication`.

## 4. Tablas propuestas

### `audiobook_editions`

Entidad principal:

- obra, idioma, versión de manuscrito y ebook KDP de origen;
- título, idioma, edición, duración estimada/final;
- método: `kdp_virtual_voice`, `acx_human`, `acx_voice_replica`, `diy_human`, `external`;
- estado: idea, elegibilidad, casting, producción, revisión, QA, publicado, suspendido/retirado;
- derechos de audio, territorios y exclusividad;
- plataforma/proyecto externo, ASIN/product ID y URL;
- precio/moneda, modelo y porcentaje de regalía con vigencia;
- KDP Select heredado, fecha de publicación y notas.

### `audiobook_productions`

- edición de audio;
- productor/estudio;
- modelo contractual P4P, Royalty Share, RSP, DIY, Virtual Voice;
- tarifa por hora final, presupuesto, coste previsto/real;
- porcentaje del titular/productor y duración del reparto;
- fechas de audición, oferta, aceptación, 15‑minute checkpoint, entrega, aprobación y QA;
- estado, contrato/documentos privados y revisiones permitidas/usadas.

### `narrators`

Nombre legal/artístico, idiomas, acentos, rango/estilo de voz, tipo humano/voice replica/virtual, proveedor, perfil externo, contacto privado, muestra, consentimiento/licencia y notas.

### `audiobook_narrators`

Pivote edición‑narrador con rol, capítulos/rango, voz externa, orden, estado y atribución. Permite reparto por personaje o una voz distinta por capítulo.

### `audiobook_auditions` y `audiobook_offers`

Guion, muestra, candidato, evaluación, decisión; después términos de oferta, modelo económico, PFH, reparto, fechas, estado y referencia ACX. Deben conservar historial, no sobrescribir ofertas rechazadas.

### `audiobook_chapters`

Vínculo con capítulo del manuscrito, orden, título hablado, narrador/voz, texto fuente/versionado, palabras, duración prevista/final, estado, ubicaciones de lectura y observaciones.

### `audio_assets` y `audio_asset_versions`

Archivo privado, tipo (capítulo, créditos, muestra), versión, hash, duración, códec, bitrate, sample rate, canales, RMS, pico, ruido, resultado QA, proveedor de almacenamiento y aprobaciones. No guardar binarios en base de datos.

### `audiobook_pronunciations`

Término, pronunciación fonética/sustitución, idioma, ámbito (obra/capítulo), proveedor/voz, estado y revisor. Útil tanto en Virtual Voice Studio como en dirección humana.

### `audiobook_distributions`

Edición, distribuidor, canal/marketplace, identificador, exclusividad, territorios, precio, moneda, estado, fechas, URL y porcentaje vigente. Evita asumir que una publicación equivale a un único canal.

### `audiobook_costs`, `audiobook_royalties` y `audiobook_payment_allocations`

Costes de productor/estudio/mastering/cubierta; ventas/regalías por tipo ALC, add‑on, crédito o suscripción; asignación de pagos al audiolibro. Los informes KDP pueden combinar suscripción de ebook y audiolibro en determinadas vistas, por lo que debe permitirse `unallocated` o `combined_source` en vez de inventar el reparto.

### `audiobook_eligibility_checks` y `audiobook_quality_checks`

Reglas versionadas, resultado, evidencia, fecha y revisor. Incluyen derechos, idioma, dominio público, TOC, capítulos, HTML, caracteres y comprobaciones técnicas de audio.

## 5. Tablas existentes que deben modificarse

- `publications.format`: añadir valor `audiobook` y relación opcional a `audiobook_edition_id`.
- `PublicationFormat`: incorporar `AUDIOBOOK`.
- `royalty_entries`: no añadir solo `royalty_audiobook`; sustituir gradualmente por hechos detallados que incluyan formato/fuente.
- `kdp_catalog_items`: aceptar formato audiobook y vincular a edición de audio.
- `kdp_report_rows`: añadir identificador de edición de audio cuando KDP permita distinguirla.
- `promotional_assets`: admitir cubierta cuadrada y muestras de audio relacionadas.
- `tasks/checklists`: plantillas específicas de producción y QA.
- `ai_tools/ai_tasks`: distinguir síntesis autorizada, voice replica, ayuda de pronunciación y análisis, conservando consentimiento.
- `sources/rights`: registrar titularidad y alcance de derechos de audio.

## 6. Formularios y pantallas

1. **Audiolibros por obra:** ediciones, método, idioma, estado, narrador y distribución.
2. **Asistente de alta:** seleccionar obra/versión, comprobar derechos, escoger Virtual Voice, ACX humano, réplica autorizada o DIY.
3. **Elegibilidad:** checklist automática/manual con evidencias.
4. **Casting:** narradores, audiciones, puntuación y ofertas.
5. **Contrato y presupuesto:** modelo, PFH, reparto, exclusividad y fechas.
6. **Producción por capítulos:** archivos/versiones, duración, pronunciaciones, revisiones y aprobación.
7. **QA técnico:** RMS, pico, ruido, bitrate, muestra y créditos.
8. **Publicación/distribución:** canales, IDs, precios, territorios y estados.
9. **Rendimiento:** unidades, listens, regalías, costes, reparto, ROI y pagos pendientes.
10. **Alertas:** derechos no confirmados, contrato pendiente, QA fallido, exclusividad incompatible y pago sin asignar.

## 7. Seguridad, derechos e IA

- Confirmar derechos de audio y territorios antes de producción.
- No presentar TTS externo como ACX humano; ACX solo admite IA autorizada.
- Guardar consentimiento/licencia para voz replica y límites de uso.
- Proteger contratos, contactos, archivos maestros y datos de pago.
- Registrar origen humano/virtual de forma honesta.
- Mantener auditoría de voz, modelo, versión, cambios y aprobaciones.
- No asumir que KDP Select del ebook permite distribución externa de audio; validar exclusividad vigente.

## 8. Importación e informes

Los informes KDP de Virtual Voice incorporan regalías de audiolibro, pero algunas vistas agrupan suscripciones de ebook y audio. El importador debe conservar `source`, `transaction_type`, formato y dato original; si no se puede asignar, se deja pendiente.

ACX/Audible necesita un adaptador separado. Nunca se deben mezclar automáticamente ingresos KDP Virtual Voice y ACX sin plataforma, periodo, identificador y moneda.

## 9. Conclusión

La funcionalidad requiere un módulo de producción y derechos, no solo otro formato de publicación. La primera versión debe gestionar edición, método, narradores, contrato, capítulos, archivos/QA y distribución. Ingresos y conciliación se incorporan después con informes reales de KDP/ACX.

