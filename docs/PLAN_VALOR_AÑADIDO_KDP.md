# Plan de valor añadido frente a Amazon KDP

## 1. Principio de diseño

Amazon KDP es la fuente de hechos de ventas, lecturas, regalías y pagos. La aplicación no debe inventar datos que KDP no entrega ni presentar estimaciones como importes definitivos.

El valor de la aplicación está en unir esos hechos con el catálogo editorial, conservar su procedencia, analizarlos por obra y publicación, y convertirlos en decisiones y tareas accionables.

## 2. Qué entrega KDP y qué aporta la aplicación

| Información | KDP | Aplicación |
|---|---|---|
| Ventas, unidades, devoluciones y regalías | Sí, en informes separados | Unifica informes, elimina duplicados y conserva el detalle por fila, lote, moneda y periodo |
| Lecturas KENP | Sí | Separa KENP de ventas, distingue provisional/definitivo y relaciona la lectura con obra/publicación |
| Pagos | Sí, sin desglose por obra | Importa el pago, conserva el original y permite conciliarlo con regalías y asignaciones por obra con nivel de confianza |
| Obras y publicaciones | KDP muestra productos/ASIN | Agrupa formatos de una misma obra y separa cada publicación, marketplace y edición |
| Acumulado por obra | No como vista editorial transversal completa | Suma regalías importadas por obra y moneda, mostrando también títulos aún no vinculados |
| Comparación de periodos | Parcial, distribuida entre informes | Evolución mensual, variaciones absolutas y porcentuales, siempre separadas por moneda |
| Rentabilidad de campañas | KDP aporta ventas; no el coste editorial/marketing completo | ROI de promociones usando costes, resultados diarios y regalías vinculadas |
| Estado editorial | No | Flujo idea, redacción, revisión, preparación y publicada |
| Progreso del manuscrito | No | Versiones, capítulos, palabras, estado final/publicado y trazabilidad de cambios |
| Plan de trabajo | No | Tareas por obra o publicación, responsables, prioridades, vencimientos y checklists |
| Calidad del catálogo | No ofrece una vista de completitud editorial | Detecta ASIN sin obra, publicación sin manuscrito final, idioma faltante, metadatos incompletos y conflictos |
| Trazabilidad | Informa del archivo descargado | Lote → fila → catálogo → obra → publicación → pago/asignación |
| Alertas operativas | Limitadas al panel de KDP | Pagos no conciliados, importaciones con errores, caídas de ventas, promociones próximas y tareas vencidas |
| Privacidad por autor | Cuenta KDP aislada | Permisos por usuario, obra, publicación y sesión de importación dentro de una misma aplicación |

## 3. Funcionalidad disponible ahora

- Acumulado de regalías por obra en el panel inicial.
- Separación obligatoria por moneda, sin sumar EUR, USD u otras monedas sin tipo de cambio.
- Fallback a título de fila cuando todavía no existe una obra vinculada.
- Acumulado por moneda y por obra desde `kdp_report_rows`.
- Catálogo detectado para revisar y vincular títulos/ASIN.
- Importación múltiple CSV/XLSX/ZIP, detección de tipo y periodo, deduplicación por huella y reprocesado.
- Pagos KDP y asignaciones, incluyendo registros manuales de distribuidores externos.
- Promociones, costes, resultados diarios y cálculo de ROI.
- Obras, publicaciones, manuscritos, idiomas, géneros, tareas y checklists.

## 4. Mejoras prioritarias

### Prioridad 1: información económica fiable

1. Añadir filtros de periodo, obra, publicación, marketplace, formato y moneda al acumulado.
2. Mostrar total de regalías, unidades, KENP y pagos recibidos en tarjetas separadas.
3. Diferenciar `royalty` definitiva, `royalty_estimate`, `order`, `preorder` y `kenp`.
4. Mostrar por cada cifra el lote y el informe de origen.
5. Comparar regalía devengada, pago recibido y diferencia no asignada.
6. Añadir exportación CSV/XLSX del resumen por obra.
7. Crear una vista de conciliación de pagos con estados: sin conciliar, parcial, conciliado y discrepancia.

### Prioridad 2: decisiones editoriales

1. Evolución mensual por obra, publicación, formato y marketplace.
2. Ingreso por unidad, unidades netas por publicación y tendencia de KENP.
3. Ranking de obras por regalías, unidades y crecimiento, sin mezclar monedas.
4. Alertas de caída sostenida, ausencia de ventas, aumento de devoluciones y cambios de marketplace.
5. Comparación entre publicaciones de una misma obra.
6. Relación entre promoción, coste, unidades, regalía y ROI.

### Prioridad 3: catálogo y metadatos

1. Índice de completitud de cada obra y publicación.
2. Avisos de ASIN sin obra, obra sin publicación, publicación sin idioma o manuscrito final.
3. Detección de títulos/autores/formats incompatibles entre informes.
4. Historial de cambios de precio, estado, metadatos y versiones de manuscrito.
5. Comparación entre metadatos previstos y metadatos observados en KDP.
6. Control de categorías, géneros, subgéneros, palabras clave y descripción como datos editoriales revisables.

### Prioridad 4: planificación y operación

1. Plantillas de checklist para lanzamiento, traducción, revisión y publicación.
2. Tipos de tarea configurables y tareas por obra o publicación.
3. Calendario combinado de tareas, eventos, promociones y fechas de publicación.
4. Dependencias entre tareas y avisos de vencimiento.
5. Registro de decisiones del autor y justificación de cambios.
6. Panel de errores de importación con fila, campo, solución sugerida y acción de reintento.

## 5. Información derivada que la aplicación sí puede ofrecer

### Acumulados y comparativas

- Regalía acumulada por obra, publicación, ASIN, formato y marketplace.
- Unidades netas acumuladas y porcentaje de cada obra sobre el total de su moneda.
- Evolución por periodo y comparación con el periodo anterior.
- Separación entre datos definitivos y estimados.
- Tendencia de KENP por obra y título.

### Rentabilidad y campañas

- Coste total de una promoción.
- Ingreso bruto y neto observado durante la campaña.
- ROI y coste por unidad.
- Comparación con periodos sin promoción.
- Promociones activas solapadas o con resultados anómalos.

Estas métricas son cálculos de la aplicación y deben etiquetarse como derivados. No sustituyen la liquidación oficial de KDP.

### Calidad editorial

- Porcentaje de publicaciones con manuscrito final.
- Obras sin idioma configurado.
- Publicaciones sin ASIN, marketplace o metadatos KDP completos.
- Versiones publicadas que no coinciden con la publicación.
- Tareas vencidas y checklists incompletas antes de publicar.
- Cambios entre versiones de manuscrito y resumen de modificaciones.

### Conciliación

- Pago KDP sin filas de regalías compatibles.
- Regalías definitivas todavía no asociadas a una publicación.
- Diferencia entre regalía devengada y pago recibido.
- Importe asignado por obra y porcentaje de confianza.
- Informes duplicados, incompletos o con errores de normalización.

## 6. Reglas para no sobreinterpretar los datos

- Nunca sumar monedas sin tipo de cambio, fuente y fecha.
- Nunca sumar pedidos con regalías como si fueran el mismo hecho.
- Nunca tratar el estimador como pago definitivo.
- Nunca atribuir un Payment Report a una obra si KDP no proporciona una clave conciliable.
- Nunca crear idioma, género, manuscrito o categoría porque falte en un informe económico.
- Mantener el importe original, la moneda, el periodo, el marketplace y el lote de procedencia.
- Marcar las métricas calculadas como `derivadas`, `estimadas` o `conciliadas`.

## 7. Roadmap técnico

### Fase A: resumen por obra

- filtros persistentes de periodo, moneda, marketplace y formato;
- consulta agrupada por obra y moneda;
- exportación y enlace al desglose KDP;
- prueba de no mezcla de monedas.

### Fase B: conciliación económica

- transacciones definitivas normalizadas;
- conciliación Payment Report + Prior Months' Royalties;
- asignaciones por publicación/obra con método y confianza;
- diferencias no asignadas y auditoría.

### Fase C: decisiones y alertas

- series temporales por obra;
- alertas configurables;
- comparativas de promociones;
- generación de tareas desde una alerta.

### Fase D: control editorial

- índice de completitud;
- preflight antes de publicar;
- checklists por tipo de lanzamiento;
- comparación de metadatos y versiones.

## 8. Criterios de éxito

- Toda cifra del panel puede abrir el detalle de filas y lotes que la componen.
- Ningún acumulado mezcla monedas o naturalezas de hecho.
- Un usuario sólo ve las obras y publicaciones autorizadas.
- El reprocesado mantiene los acumulados idempotentes.
- Un pago no se marca conciliado sin una suma de control documentada.
- Las decisiones editoriales pueden convertirse en tareas y comprobarse mediante checklist.
