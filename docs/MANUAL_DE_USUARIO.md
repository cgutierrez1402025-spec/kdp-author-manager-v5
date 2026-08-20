# Manual ilustrado de KDP Author Manager

Este manual explica todas las opciones del panel. Las capturas proceden de la aplicación real con datos de demostración y perfil administrador. Las opciones visibles pueden reducirse según el rol.

## 1. Acceso y navegación

Abra `/admin` e identifíquese. En desarrollo puede usar `admin@kdpmanager.local` o `author@example.com`, con contraseña `password`. Sustituya estas credenciales en producción.

El administrador consulta todos los autores; el autor sólo sus propios datos. Editor y contable acceden según las políticas configuradas. La barra lateral agrupa las opciones por dominio, el buscador superior localiza recursos y el avatar permite cerrar sesión.

## 2. Inicio — Resumen editorial

![Resumen editorial](images/manual/inicio.png)

El inicio reúne obras, publicaciones, regalías, tareas, eventos, promociones y datos importados. Las regalías identificadas como **Informes KDP** proceden de `kdp_report_rows`; si existen entradas manuales, la gráfica usa `royalty_entries`. Las monedas se presentan por separado.

Revise aquí el último periodo, la evolución de ingresos, unidades, KENP, ASIN, títulos y marketplaces. Una importación o reprocesamiento invalida automáticamente la caché del panel.

## 3. Catálogo editorial

### Obras

![Listado de obras](images/manual/obras.png)

La obra es la entidad principal. Puede buscar, filtrar, abrir y editar sus idiomas, manuscritos, publicaciones, tareas y fuentes. Orden recomendado: obra → idioma → manuscrito final → publicación.

### Crear una obra

![Formulario de nueva obra](images/manual/obra-nueva.png)

Complete título público, autor, idioma original y estado. Añada género, audiencia, descripción y fechas cuando estén confirmados. Al guardar se asignan usuario e idioma original. No invente ASIN, ISBN ni fechas.

### Versiones de manuscrito

![Versiones de manuscrito](images/manual/manuscritos.png)

Cada versión pertenece a una obra y un idioma. Registre número, estado, contenido, archivo privado, versión padre y cambios. Sólo un manuscrito final coherente puede asociarse a una publicación.

### Tareas

![Listado de tareas](images/manual/tareas.png)

Una tarea puede ser general de una obra o específica de una publicación. Indique responsable, tipo, prioridad, estado y vencimiento. La acción **Nueva tarea** de una publicación precarga ambos vínculos y rechaza combinaciones incoherentes.

### Tipos de tarea

![Tipos de tarea](images/manual/tipos-tarea.png)

Autores, editores y administradores pueden crear tipos compartidos. Pulse **Nuevo tipo** o créelo desde el selector de una tarea. El nombre debe ser único. Desactive tipos obsoletos; las tareas existentes conservarán su relación.

### Listas de verificación

![Listas de verificación](images/manual/checklists.png)

Una checklist puede aplicarse a toda una obra o a una publicación concreta. Déjela sin publicación para controles reutilizables. Gestione las comprobaciones desde la pestaña **Elementos**.

## 4. Publicaciones

### Ediciones publicadas

![Ediciones publicadas](images/manual/publicaciones.png)

Una publicación relaciona obra, idioma, manuscrito final, plataforma, marketplace y formato. Puede contener ASIN, ISBN, precio, moneda, URL, estado y fecha. Desde el listado puede editarla o crear tareas y checklists vinculadas.

### Audiolibros

![Audiolibros](images/manual/audiolibros.png)

Registra ediciones de audio, producción, narración, capítulos, activos, calidad, distribución, costes y regalías. No marque como publicable una edición sin derechos y activos aprobados.

### Narradores y voces

![Narradores y voces](images/manual/narradores.png)

Registra narradores humanos, voces virtuales y réplicas autorizadas. Complete idiomas, características y consentimiento. La réplica de voz exige consentimiento vigente.

### Metadatos KDP

![Metadatos KDP](images/manual/metadatos-kdp.png)

Conserva título, subtítulo, autor, descripción, palabras clave, categorías, edades, derechos y declaración de IA. Es una referencia de preparación; no modifica directamente la cuenta Amazon.

### Periodos KDP Select

![Periodos KDP Select](images/manual/periodos-kdp-select.png)

Registre publicación, fechas, renovación, días gratuitos y estado. El panel avisa de vencimientos. Use fechas confirmadas en KDP, no inferidas desde ventas.

### Plataformas

![Plataformas](images/manual/plataformas.png)

Define canales de distribución como Amazon KDP. Se utilizan al crear publicaciones y mercados. Evite duplicados ortográficos.

### Marketplaces

![Marketplaces](images/manual/marketplaces.png)

Representa tiendas asociadas a una plataforma. Configure nombre, país, dominio y moneda. Una publicación sólo puede usar un mercado de su plataforma.

## 5. Informes Amazon KDP

### Importar informes KDP

![Importación KDP](images/manual/importar-kdp.png)

1. Descargue el CSV o XLSX desde KDP Reports.
2. Seleccione hasta veinte archivos o un ZIP.
3. Mantenga la detección automática.
4. Indique un periodo sólo como respaldo.
5. Guarde y revise el resultado.

Los archivos son privados. Las huellas SHA-256 detectan duplicados y cada fila conserva valores originales y normalizados.

### Sesiones de importación

![Sesiones de importación](images/manual/sesiones-importacion.png)

Una carga múltiple crea una sesión con archivos completados, duplicados y fallidos. Abra sus lotes o use **Reprocesar sesión** tras actualizar reglas. Si un fichero falla se conservan los datos anteriores.

### Catálogo detectado KDP

![Catálogo detectado KDP](images/manual/catalogo-kdp.png)

Contiene títulos encontrados. La aplicación vincula por ASIN y formato o crea registros provisionales. Revise idioma, clasificación, manuscrito y obra. Use **Vincular a obra** si ya existe.

### Desglose de informes

![Desglose KDP](images/manual/desglose-kdp.png)

Muestra filas normalizadas con informe, fecha, título, ASIN, mercado, unidades, KENP, importe y moneda. Filtre para investigar diferencias. Las filas sin actividad se conservan para auditoría.

### Pagos KDP

![Pagos KDP](images/manual/pagos-kdp.png)

Incluye número, fecha de transferencia, método, periodo, mercado, moneda, neto, retención, cambio e importe. Un pago agregado permanece sin obra si Amazon no aporta una relación inequívoca.

## 6. Marketing

### Promociones

![Promociones](images/manual/promociones.png)

Registre publicación, mercado, tipo, fechas, precios, objetivo y estado. No solape periodos incompatibles. El ROI requiere costes e ingresos completos en moneda comparable.

### Costes de promoción

![Costes de promoción](images/manual/costes-promocion.png)

Asocie fecha, concepto, proveedor, importe y moneda a una promoción. Registre gastos reales y conserve su trazabilidad.

### Resultados diarios

![Resultados diarios](images/manual/resultados-promocion.png)

Registre ventas, ingresos, impresiones, clics y otras métricas por día. Identifique la fuente y evite duplicar datos importados.

## 7. Eventos

### Calendario de eventos

![Calendario de eventos](images/manual/eventos.png)

Registra presentaciones, firmas y ferias con fecha, lugar, estado y notas. Los próximos eventos aparecen en el inicio.

### Libros en eventos

![Libros en eventos](images/manual/libros-eventos.png)

Relaciona obras con eventos y permite registrar ejemplares, ventas e ingresos. Sólo se ofrecen obras accesibles para el usuario.

## 8. Documentación

### Fuentes

![Fuentes documentales](images/manual/fuentes.png)

Registre título, autor, año, tipo, idioma, URL, cita, resumen, derechos, licencia y fiabilidad. Los archivos admitidos se guardan de forma privada.

### Usos de fuente

![Usos de fuente](images/manual/usos-fuente.png)

Documenta cómo se utilizó una fuente en una obra: fragmento, finalidad, ubicación y observaciones. Facilita revisar derechos y atribuciones.

### Ayuda integrada

![Ayuda de la aplicación](images/manual/ayuda.png)

Ofrece accesos rápidos a los flujos frecuentes. Para incidencias técnicas consulte `PRUEBAS_Y_RESOLUCION_DE_PROBLEMAS.md`.

## 9. Inteligencia artificial

### Prompts

![Prompts](images/manual/prompts.png)

Registre objetivo, instrucciones, obra, herramienta y versión. No incluya claves, datos bancarios ni información personal innecesaria.

### Tareas IA

![Tareas IA](images/manual/tareas-ia.png)

Agrupa trabajos asistidos, entrada, resultado, estado y herramienta. Revise siempre el resultado antes de incorporarlo a una publicación.

## 10. Ilustraciones

### Anclajes de ilustraciones

![Anclajes de ilustraciones](images/manual/anclajes-ilustraciones.png)

Relaciona una ilustración con una obra y una ubicación editorial. Conserva intención, posición y estado de aplicación entre versiones.

## 11. Configuración editorial

### Idiomas

![Idiomas](images/manual/idiomas.png)

Administra códigos, nombres y estado de los idiomas usados en obras, traducciones, manuscritos y audiolibros. Evite duplicados.

## 12. Resolución rápida de problemas

### Resumen o gráfica vacíos

- Importe un informe de ventas/regalías, no sólo pagos.
- Compruebe propietario, periodo y tipo de fila en **Desglose**.
- Revise errores en **Sesiones de importación**.
- Tras desplegar una versión ejecute `php artisan optimize:clear`.

### Archivo duplicado

La huella coincide con una carga previa. Abra el lote anterior o use **Reprocesar**; no renombre el mismo archivo para forzarlo.

### Publicación sin vínculo

Revise ASIN, formato y propietario. Complete el vínculo desde **Catálogo detectado KDP**.

### Opción no visible

Puede estar restringida por rol. Solicite al administrador revisar permisos.

## 13. Regenerar las capturas

Con la aplicación disponible en el puerto 8030:

```bash
MANUAL_BASE_URL=http://127.0.0.1:8030 node scripts/capture-user-manual.mjs
```

Para una sola pantalla:

```bash
MANUAL_PAGE=obras node scripts/capture-user-manual.mjs
```

El script usa Google Chrome headless y guarda las imágenes en `docs/images/manual`.
