# Informe: panel de ayuda a la toma de decisiones, importación KDP e IA

**Fecha de análisis:** 19 de agosto de 2026  
**Aplicación analizada:** KDP Author Manager (Laravel + Filament)

## 1. Resumen ejecutivo

La aplicación ya contiene buena parte de la información necesaria para evolucionar desde un panel descriptivo hacia un panel de ayuda a la toma de decisiones: obras, publicaciones por formato y marketplace, regalías, KENP, pagos, campañas, costes, resultados diarios, KDP Select, tareas, eventos y estado editorial.

La propuesta es construir el producto en tres capas:

1. **Datos fiables:** importar los archivos que el autor descarga de KDP, conservar el original, normalizar cada fila y permitir su conciliación.
2. **Reglas e indicadores:** calcular tendencias, rentabilidad, alertas y oportunidades con fórmulas reproducibles.
3. **Asistente de IA:** explicar los indicadores, proponer alternativas y crear borradores o tareas, siempre con aprobación humana.

La conclusión más importante es que **no debe plantearse una conexión directa a la cuenta KDP mediante una supuesta API de KDP**. La documentación pública de KDP ofrece descarga de informes desde `kdpreports.amazon.com`, pero no documenta una API pública para leer ventas de una cuenta, cambiar metadatos o publicar libros. La Product Advertising API —y su sucesora, Creators API— sirve para consultar información pública de productos/afiliación, no datos privados de ventas ni administración de KDP. Por tanto, la vía soportable es una importación asistida de XLSX/CSV; Amazon Ads puede integrarse por separado si posteriormente se obtiene acceso a su API.

Para IA, conviene sustituir el acoplamiento actual exclusivo a OpenAI por un proveedor intercambiable. Como primera opción sin coste de consumo, se recomienda **Groq Free Plan** mediante su endpoint compatible con OpenAI; como alternativa, **Gemini Developer API Free Tier**. Las cuotas gratuitas son límites promocionales/operativos, pueden cambiar y no deben tratarse como un SLA ni como “tokens gratis para siempre”.

## 2. Situación actual de la aplicación

### Activos reutilizables

La base de datos ya modela:

- catálogo editorial: `works`, idiomas, ediciones, manuscritos, capítulos y publicaciones;
- distribución KDP: ASIN, ISBN, formato, marketplace, precio, moneda y metadatos;
- negocio: `royalty_entries`, `royalty_payments` y umbrales de pago;
- marketing: promociones, costes y resultados diarios;
- operación: tareas, checklists, eventos y periodos KDP Select;
- IA: herramientas, tareas de IA y registro de prompts;
- importación auditable: lotes, mapeos, filas y errores de importación.

El panel actual ya muestra tarjetas resumen, evolución de ingresos de seis meses, obras con más ingresos, promociones activas con ROI, tareas, eventos y vencimientos de KDP Select. Esto permite ampliar el panel en vez de reconstruirlo.

### Carencias y riesgos detectados

1. `RoyaltyImportService` no procesa un archivo KDP real: espera filas ya transformadas con un `publication_id` interno.
2. `royalty_entries` agrega por publicación/año/mes. Su clave única no conserva marketplace, moneda, tipo de transacción, devoluciones, precio, coste de entrega ni fila de origen. Dos ventas del mismo ASIN en monedas o mercados distintos pueden colisionar o quedar mezcladas.
3. El panel suma importes en monedas distintas como si fueran equivalentes. Antes de presentar totales globales se necesita conversión a una moneda base y conservar importe y moneda originales.
4. `KdpApiService` contiene rutas como `/reports/sales` y `/metadata/{asin}` bajo PA-API que no pertenecen a las operaciones documentadas de Product Advertising API. Deben deshabilitarse en producción. Su consulta pública por ASIN también deberá migrarse, porque Amazon indica que PA-API se retira el 15 de mayo de 2026 en favor de Creators API.
5. `AiService` solo implementa OpenAI y envía un prompt de texto sin contexto estructurado, límites de salida, historial de uso, presupuesto por usuario ni protección específica frente a datos sensibles.
6. Los datos mensuales actuales sirven para dirección general, pero no para atribución diaria de campañas o detección rápida de cambios.

## 3. Panel de ayuda a la toma de decisiones

### Principio de diseño

Cada recomendación debe mostrar cuatro elementos: **qué ocurre, qué datos lo demuestran, qué acción se propone y con qué confianza**. Los cálculos serán deterministas; la IA solo los explicará o ayudará a ejecutar una acción.

### Bloques recomendados

| Bloque | Indicadores | Decisión que facilita |
|---|---|---|
| Salud del catálogo | ingresos 30/90/365 días, unidades, KENP, ingreso por formato, crecimiento frente al periodo anterior | qué títulos requieren atención |
| Tendencias | media móvil, variación intermensual, estacionalidad y títulos en aceleración/caída | dónde invertir tiempo o promoción |
| Rentabilidad | regalía neta, coste promocional, beneficio, ROI y plazo de recuperación | mantener, detener o repetir campañas |
| Marketplace y formato | ingreso/unidades/KENP por país, moneda y ebook/tapa blanda/tapa dura | ajustar formato, precio o expansión territorial |
| Concentración y riesgo | porcentaje de ingresos de los 1/3/5 títulos principales | diversificar el catálogo |
| KDP Select | fin de periodo, días promocionales restantes, KENP por día e ingreso KENP estimado | renovar, probar promoción o revisar exclusividad |
| Cobros | devengado frente a pagado, retenciones, pagos fallidos y antigüedad | conciliar y reclamar incidencias |
| Producción editorial | obras bloqueadas, tareas vencidas, avance de manuscrito y proximidad a publicación | ordenar el trabajo semanal |
| Calidad de datos | última importación, filas rechazadas, ASIN sin vincular y periodos sin datos | saber si las conclusiones son fiables |

### Centro de decisiones

Además de gráficos, conviene añadir una cola priorizada de “decisiones pendientes”. Ejemplos:

- **Caída relevante:** “El ebook X baja un 28 % en regalía neta frente a su media de 90 días, con volumen suficiente para que el cambio sea significativo”.
- **Promoción no rentable:** “La campaña Y ha generado 42 EUR y ha costado 65 EUR; ROI provisional −35,4 %”.
- **Oportunidad:** “El mercado ES aporta el 38 % de KENP pero solo el 12 % de ventas; valorar una promoción de lectura o revisar la ficha”.
- **Cobro pendiente:** “El importe recibido no coincide con el pago esperado del marketplace DE”.
- **Operación:** “KDP Select vence en 12 días y quedan 3 días de promoción gratuita”.

Cada tarjeta tendrá `ver evidencia`, `descartar`, `posponer` y `crear tarea`. La aceptación o rechazo debe guardarse para medir si las reglas son útiles; no se necesita aprendizaje automático para la primera versión.

### Métricas y cautelas

- **ROI de promoción:** `(regalía incremental atribuida - coste) / coste`. No utilizar toda la regalía del periodo como incremental; comparar contra una línea base anterior y mostrar que es una estimación.
- **Ingreso por unidad:** regalía neta / unidades netas, segmentada por moneda, marketplace y formato.
- **Valor KENP:** regalía KENP / páginas KENP, solo cuando el informe definitivo del mes lo permita.
- **Moneda base:** guardar el tipo de cambio, fuente y fecha utilizados. Nunca sobrescribir el importe original.
- **Datos estimados frente a definitivos:** etiquetar explícitamente los informes actuales como estimados y los de “Prior Months' Royalties” como consolidados.
- **Comparaciones:** evitar porcentajes engañosos cuando el periodo anterior sea cero o tenga poco volumen; mostrar entonces valores absolutos.

### Experiencia de usuario propuesta

1. Franja superior: fecha de última actualización, cobertura del periodo y estado de calidad.
2. Primera fila: beneficio estimado, regalía consolidada, unidades netas, KENP y cobros pendientes.
3. Segunda fila: decisiones prioritarias ordenadas por impacto/confianza/urgencia.
4. Tercera fila: tendencias y desglose por obra, formato y marketplace.
5. Zona operativa: tareas, campañas, eventos y KDP Select.
6. Asistente lateral: preguntas sobre el catálogo y acciones guiadas, siempre limitado al usuario autenticado.

## 4. Importación de información de Amazon KDP

### Qué puede obtenerse de forma soportada

KDP permite filtrar y descargar informes desde su panel. La documentación oficial describe, entre otros:

- informes de pedidos, estimación de regalías y KENP;
- **Prior Months' Royalties**, con título, autor, ASIN, marketplace, moneda, formato, unidades, devoluciones, unidades netas, tipo de regalía/transacción, precios medios, coste de entrega, KENP y ganancias;
- **Payment Report**, con número y estado de pago, marketplace, fecha, método, periodo de ventas, regalía devengada, retención, tipo de cambio e importe pagado;
- informe histórico y el antiguo Sales and Royalties Report mientras continúe disponible.

Fuentes: [visión general y descarga de KDP Reports](https://kdp.amazon.com/en_US/help/topic/GVTTXHKHVPAPBEDQ/), [Prior Months' Royalties](https://kdp.amazon.com/en_US/help/topic/G200641190), [Payment Report](https://kdp.amazon.com/en_US/help/topic/G201436840) y [Sales and Royalties Report](https://kdp.amazon.com/en_US/help/topic/G201488550/).

### Flujo de importación recomendado

1. El autor descarga XLSX/CSV de KDP y lo arrastra a “Importaciones > Amazon KDP”.
2. El servidor valida tipo MIME, tamaño y estructura; calcula SHA-256 y rechaza duplicados exactos.
3. Se guarda el original en almacenamiento privado y se crea `import_batches` con usuario, tipo de informe, zona/idioma detectado y estado.
4. Un trabajo en cola lee todas las hojas y detecta el informe por nombres y conjunto de columnas, no solo por el nombre del archivo.
5. Se conserva cada fila original en `import_rows` y se normalizan fechas, decimales, separadores, monedas, formatos y tipos de transacción mediante diccionarios versionados.
6. La publicación se resuelve por `ASIN + formato`; marketplace es una dimensión de la transacción, no necesariamente una publicación distinta. Los casos ambiguos se presentan al usuario para vincularlos una vez y guardar el mapeo.
7. Se valida que unidades vendidas menos devoluciones coincidan con unidades netas y se ejecutan controles de totales por hoja/moneda.
8. Se muestra una previsualización: nuevas filas, actualizaciones, duplicados, ASIN no vinculados, errores y totales de control.
9. Tras confirmación, una transacción de base de datos consolida los datos. Si falla, se revierte completa; el lote y sus errores quedan disponibles para diagnóstico.
10. Se recalculan agregados y se invalida la caché del panel del usuario.

### Modelo de datos necesario

No conviene forzar el detalle KDP dentro de la tabla mensual existente. Se recomienda añadir:

- `kdp_report_rows`: `user_id`, `import_batch_id`, huella natural de fila, ASIN, título/autor originales, formato, marketplace, periodo/fecha, tipo de transacción, unidades, devoluciones, unidades netas, KENP, precio medio, coste de entrega, regalía, moneda, bonus y JSON original;
- `kdp_payments`: número de pago, marketplace, periodo, estado, fechas, método, devengado, retención, FX, importe y monedas origen/destino;
- `exchange_rates`: moneda origen/destino, fecha, tipo, fuente y fecha de obtención;
- opcionalmente `decision_signals` y `decision_actions` para guardar reglas disparadas, evidencia, prioridad, estado y respuesta del autor.

La tabla `royalty_entries` puede mantenerse como agregado mensual compatible con los widgets actuales, pero deberá regenerarse desde las filas detalladas y su clave incluir la granularidad adecuada. Para idempotencia se utilizará una huella de los campos naturales más `import_batch_id`, no únicamente publicación/mes.

### Actualización gradual

- **Fase 1:** Prior Months' Royalties y Payment Report, porque son definitivos y cubren ingresos y conciliación.
- **Fase 2:** informes actuales de pedidos/KENP para señales más rápidas, marcados como estimados.
- **Fase 3:** Amazon Ads mediante su API oficial, solo si se obtiene autorización; así se incorporan impresiones, clics, gasto y ventas atribuidas sin confundir Ads con KDP.

No se recomienda automatizar el inicio de sesión de KDP mediante scraping ni pedir al usuario sus credenciales/cookies: es frágil, aumenta el riesgo de seguridad y puede entrar en conflicto con las condiciones del servicio. Tampoco debe usarse Product Advertising/Creators API para simular funciones privadas de KDP: su operación documentada `GetItems` consulta atributos públicos de productos. Fuente: [GetItems de Product Advertising API](https://webservices.amazon.com/paapi5/documentation/get-items.html).

## 5. Asistente de IA con cuota gratuita

### Casos de uso conectados con la base de datos

| Contexto de la aplicación | Ayuda de IA | Acción resultante |
|---|---|---|
| obra + metadatos KDP | mejorar descripción, proponer palabras clave y detectar incoherencias de título/audiencia | borrador revisable, nunca publicación automática |
| manuscrito/capítulos | resumen, checklist editorial, consistencia de personajes/terminología | tareas o comentarios vinculados a capítulo/versión |
| fuentes y usos | sugerir cita, localizar afirmaciones sin verificar y explicar riesgos | tarea de verificación; la IA no inventa fuentes |
| traducciones | primer borrador y glosario consistente | nueva versión marcada como traducción asistida pendiente de revisión humana |
| promociones + métricas | explicar ROI y proponer hipótesis/experimentos | plan de campaña o tarea con objetivo y métrica |
| regalías/KENP | responder preguntas en lenguaje natural sobre agregados autorizados | explicación con periodo, moneda y registros usados |
| tareas/eventos/KDP Select | ordenar prioridades y crear un plan semanal | tareas en borrador que confirma el autor |

### Proveedor recomendado

**Opción inicial: Groq Free Plan.** Publica límites gratuitos explícitos por modelo y una API de chat compatible con el formato OpenAI, lo que reduce el trabajo de integración. Por ejemplo, sus límites gratuitos actuales incluyen cuotas diarias y por minuto que varían por modelo; deben consultarse dinámicamente o tratarse como configuración, no codificarse en lógica de negocio. Fuente: [límites oficiales de Groq](https://console.groq.com/docs/rate-limits).

**Alternativa: Gemini Developer API Free Tier.** Ofrece entrada y salida gratuitas para determinados modelos y proyectos dentro de sus límites. En el nivel gratuito Google indica que el contenido puede utilizarse para mejorar sus productos, por lo que no se deben enviar manuscritos completos, datos personales o contenido confidencial sin consentimiento informado; para producción sensible debe evaluarse el nivel de pago, donde la política indicada es diferente. Fuentes: [precios de Gemini API](https://ai.google.dev/gemini-api/docs/pricing) y [niveles de facturación](https://ai.google.dev/gemini-api/docs/billing).

OpenAI puede seguir disponible como proveedor de pago, pero la aplicación no debe prometer crédito gratuito. La selección será configurable por entorno y, preferiblemente, con modalidad **BYOK** (cada autor aporta su clave) para no concentrar coste y cuota en una sola cuenta.

### Arquitectura técnica

Crear un contrato común, por ejemplo:

```php
interface AiProvider
{
    public function generate(AiRequest $request): AiResponse;
}
```

Implementaciones iniciales:

- `GroqProvider` → `POST /openai/v1/chat/completions`;
- `GeminiProvider` → endpoint `generateContent` del modelo permitido;
- `OpenAiProvider` → Responses API, reutilizando la lógica actual.

Sobre el contrato se añadirá `AuthorAssistantService`, responsable de:

1. autorizar el acceso a obra y registros del usuario;
2. construir contexto estructurado y mínimo;
3. aplicar una plantilla versionada según la tarea;
4. exigir salida JSON con `resumen`, `evidencia`, `recomendaciones`, `advertencias` y `acciones_propuestas`;
5. validar el JSON y mostrarlo como borrador;
6. registrar proveedor, modelo, tokens, latencia, coste estimado, prompt versionado y resultado;
7. crear/modificar datos solo después de una confirmación explícita.

Para el chat sobre datos se usarán herramientas cerradas de consulta (`getWorkSummary`, `getRoyaltyTrend`, `getPromotionRoi`, `listOverdueTasks`) en lugar de permitir que el modelo genere SQL. Cada herramienta aplicará las políticas de autor ya existentes.

### Control de cuota y fallos

- límites diarios por usuario y por función;
- máximo de tokens de entrada/salida y truncado consciente por capítulos;
- caché por hash de contexto + plantilla cuando el caso lo permita;
- trabajos en cola para traducciones/resúmenes extensos;
- tratamiento de `429` con espera indicada por proveedor, reintentos acotados y mensaje claro;
- interruptor global y por proveedor;
- fallback manual: copiar prompt y registrar después el resultado;
- no cambiar automáticamente de proveedor si eso modifica la política de privacidad aceptada por el usuario.

### Seguridad y confianza

- cifrar claves API y no exponerlas al navegador ni registrarlas en logs;
- consentimiento separado para enviar contenido a cada proveedor;
- minimizar o seudonimizar datos; excluir pagos, datos bancarios y datos personales;
- permitir seleccionar fragmentos, no enviar por defecto el manuscrito completo;
- política de retención y borrado de conversaciones compatible con RGPD;
- protección frente a instrucciones maliciosas dentro de manuscritos/documentos;
- identificación visible de contenido generado por IA y revisión humana antes de su uso;
- aviso de que las sugerencias comerciales son orientativas y dependen de datos importados y completos.

## 6. Plan de ejecución

### Etapa 0 — saneamiento (1 semana)

- desactivar las falsas operaciones KDP del servicio actual;
- decidir moneda base por usuario;
- corregir sumas multimoneda y etiquetar datos estimados/definitivos;
- preparar muestras anonimizadas de cada informe y pruebas de regresión.

### Etapa 1 — importación consolidada (2–3 semanas)

- subida privada XLSX/CSV, detector de informe y parsers versionados;
- detalle transaccional, resolución ASIN/formato y previsualización;
- importación idempotente, errores por fila, conciliación y auditoría;
- soporte para Prior Months' Royalties y Payment Report.

**Criterio de aceptación:** reimportar el mismo archivo no duplica datos; los totales por moneda coinciden con KDP; ninguna fila queda silenciosamente descartada.

### Etapa 2 — panel de decisiones (2–3 semanas)

- modelo de señales/acciones y motor de reglas;
- indicadores por periodo, título, formato y marketplace;
- cola de decisiones con evidencia y creación de tareas;
- calidad/frescura de datos y pruebas de aislamiento por usuario.

**Criterio de aceptación:** cada cifra se puede rastrear hasta filas importadas y cada recomendación explica su fórmula, periodo y nivel de confianza.

### Etapa 3 — IA controlada (2 semanas)

- contrato multiproveedor y adaptadores Groq/OpenAI; Gemini opcional;
- asistente contextual en la ficha de obra y centro de decisiones;
- salidas estructuradas, aprobación antes de escribir y telemetría de cuota;
- funciones iniciales: explicación de rendimiento, descripción comercial y plan semanal.

**Criterio de aceptación:** el modelo nunca accede a obras ajenas, no ejecuta SQL, no cambia registros sin confirmación y la aplicación sigue siendo útil cuando se agota la cuota.

### Etapa 4 — optimización (posterior)

- informes diarios estimados, análisis de cohortes y estacionalidad;
- integración separada de Amazon Ads si se aprueba el acceso;
- evaluación de recomendaciones aceptadas y resultados posteriores;
- previsiones, solo cuando exista suficiente histórico y mostrando intervalos de incertidumbre.

## 7. Prioridad recomendada

La mejor primera entrega no es el chat: es **importar correctamente Prior Months' Royalties y Payment Report, resolver monedas y crear señales trazables**. Una vez que esos datos sean fiables, el asistente puede aportar valor real explicando tendencias y convirtiéndolas en acciones. Implementar primero un chat sobre datos agregados incompletos produciría respuestas atractivas, pero decisiones poco fiables.

El MVP aconsejado combina:

1. importación manual asistida de los dos informes consolidados;
2. panel con calidad de datos, tendencia por título/formato/mercado, ROI y cobros;
3. cinco reglas de decisión de alto valor;
4. Groq Free Plan como proveedor inicial intercambiable y OpenAI como alternativa;
5. tres acciones IA muy acotadas: explicar una señal, redactar una descripción y convertir recomendaciones en tareas en borrador.

Esta secuencia reutiliza casi todo el modelo actual, reduce riesgos legales y técnicos alrededor de KDP y permite demostrar utilidad antes de asumir costes recurrentes de IA o integraciones externas más complejas.
