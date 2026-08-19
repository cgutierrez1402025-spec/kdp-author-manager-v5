# Manual de usuario

## Acceso y perfiles

La aplicación se abre en `/admin`. Los perfiles disponibles son administrador, autor, editor y contable. Un autor solo ve sus obras y datos asociados; administrador, editor y contable pueden consultar datos de todos los autores según las políticas configuradas.

En desarrollo existen dos usuarios demostrativos:

- `admin@kdpmanager.local` / `password`;
- `author@example.com` / `password`.

Estas credenciales no deben mantenerse en producción.

## Panel de inicio

El panel resume la actividad editorial y comercial:

- obras, publicaciones y promociones activas;
- ingresos mensuales registrados en la tabla histórica de regalías;
- tareas propias y vencidas;
- próximos eventos;
- evolución de ingresos y obras principales;
- promociones y su ROI;
- vencimientos de KDP Select;
- gráficos derivados de informes Amazon KDP importados.

El bloque **Rendimiento importado desde Amazon KDP** muestra:

- unidades netas y ASIN distintos;
- páginas KENP;
- evolución diaria de unidades;
- títulos y marketplaces con más unidades;
- KENP por título;
- regalías agrupadas por moneda.

El panel muestra únicamente títulos, mercados, fechas y monedas con un valor agregado distinto de cero. **Obras con más ingresos** incluye todas las obras con ingresos positivos, ordenadas de mayor a menor. El enlace **Ver desglose completo** abre todas las filas importadas: primero las que contienen alguna métrica distinta de cero y, a continuación, las que todavía no tienen actividad. El desglose permite filtrar por informe y tipo de dato y acceder a la publicación relacionada.

Las monedas no se suman entre sí. Un total de EUR y otro de USD aparecen separados hasta que exista una tabla de tipos de cambio.

## Catálogo editorial

Una obra es la entidad principal. Desde ella se gestionan:

- título público e interno, autor, idioma, género, audiencia y estado;
- idiomas y traducciones;
- versiones del manuscrito y capítulos;
- publicaciones por plataforma, formato y marketplace;
- fuentes documentales, tareas y prompts.

El orden recomendado es: crear obra, idioma, versión de manuscrito y finalmente publicación. Para que una fila KDP se vincule automáticamente, la publicación debe tener el ASIN y formato correspondientes.

## Publicaciones y KDP

Una publicación identifica una edición comercial en una plataforma. Puede contener ASIN, ISBN, formato, precio, moneda, marketplace, URL y estado. Los metadatos KDP se conservan como preparación y referencia; la aplicación no publica ni modifica directamente una cuenta KDP mediante una API privada.

Los periodos KDP Select permiten registrar inicio, fin, renovación y días de promoción gratuita utilizados. El panel avisa de vencimientos próximos.

## Importación KDP

1. En KDP Reports, aplica los filtros deseados y descarga el informe.
2. En la aplicación, abre **Publicaciones → Importar informes KDP**.
3. Pulsa **Cargar informe KDP**, selecciona hasta 20 CSV/XLSX simultáneamente o un ZIP y deja activada la detección automática.
4. Indica un periodo sólo como respaldo si los nombres no lo contienen.
5. Guarda y espera la notificación de resultado.
6. Consulta **Sesiones de importación** para revisar archivos completados, duplicados y fallidos, y abre sus lotes si necesitas reprocesarlos.
7. Regresa al panel para ver los gráficos actualizados.

Cada título nuevo aparece automáticamente en **Publicaciones → Catálogo detectado KDP** y se crea como obra/publicación provisional. `Vinculada` significa que las filas comerciales ya apuntan al catálogo editorial. La obra queda **Pendiente de completar (KDP)** hasta que se añadan idioma, clasificación y, cuando exista, manuscrito.

Si la agrupación automática no corresponde a la obra adecuada, utiliza las acciones del catálogo para revisar el vínculo. No se genera un idioma ni manuscrito artificial: deben completarse posteriormente desde la obra o edición publicada.

La acción **Reprocesar** elimina únicamente las filas derivadas de ese lote y vuelve a crearlas desde el archivo original privado. Es útil después de actualizar reglas de mapeo. No elimina el archivo original.

También puedes usar **Reprocesar sesión** para repetir todos los archivos de una carga. La aplicación valida la huella del archivo y conserva los datos anteriores de cualquier fichero que falle; no elimina obras ni vínculos confirmados por el usuario.

Consulte [IMPORTACION_AMAZON_KDP.md](IMPORTACION_AMAZON_KDP.md) para formatos, controles y resolución de incidencias.

## Regalías, pagos y promociones

Las regalías históricas manuales y las filas importadas KDP son fuentes distintas. Los widgets antiguos usan `royalty_entries`; el nuevo bloque KDP usa `kdp_report_rows`. Esta separación evita alterar datos históricos hasta que exista una conciliación explícita.

Una promoción agrupa periodo, precio, objetivo, costes y resultados diarios. El ROI mostrado es una ayuda analítica y depende de que costes e ingresos estén completos y expresados en una moneda comparable.

## Tareas, eventos y documentación

Las tareas se vinculan a una obra y tienen responsable, prioridad, estado y vencimiento. Los eventos registran presentaciones u otras actividades, ejemplares vendidos e ingresos. Las fuentes y usos documentan procedencia, derechos, fiabilidad y fragmentos utilizados en manuscritos.

## Funciones de IA

La aplicación conserva herramientas, tareas y prompts de IA. El servicio disponible puede sugerir etiquetas, mejorar descripciones y traducir texto cuando existe una clave configurada. Todo resultado debe revisarse. No se deben enviar datos bancarios, credenciales, información personal innecesaria ni manuscritos confidenciales sin valorar las condiciones del proveedor.
