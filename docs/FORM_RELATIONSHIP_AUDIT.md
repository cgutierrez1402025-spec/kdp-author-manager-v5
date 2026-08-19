# Auditoría de relaciones en formularios

Fecha: 17 de agosto de 2026

## Relaciones editoriales

| Formulario | Relaciones contempladas | Restricciones |
|---|---|---|
| Obras | Usuario y serie | El usuario se asigna automáticamente; las series se limitan por propietario. |
| Fuentes | Obra | La obra es obligatoria y se limita al propietario, salvo administradores. |
| Usos de fuente | Fuente, obra, manuscrito y capítulo | La fuente fija su obra; manuscritos y capítulos se filtran de forma dependiente. |
| Manuscritos | Obra, idioma, versión padre y edición | Idioma, padre y edición deben pertenecer a la obra seleccionada. |
| Publicaciones | Obra, idioma, manuscrito, plataforma y marketplace | Solo manuscritos finales; idioma de la obra y marketplace de la plataforma. |
| Libros en eventos | Evento, obra, edición e idioma | Edición e idioma se limitan a la obra seleccionada. |
| Anclajes de ilustración | Ilustración, manuscrito y capítulo | Manuscrito de la obra ilustrada y capítulo de la versión seleccionada. |

## Relaciones operativas

| Formulario | Relaciones contempladas | Restricciones |
|---|---|---|
| Tareas | Obra, asignado y creador | Obra obligatoria; creador asignado automáticamente. |
| Eventos | Usuario | Usuario asignado automáticamente. |
| Tareas IA | Obra y herramienta IA | Obra y herramientas limitadas al propietario. |
| Prompts | Obra, herramienta y tarea IA | La tarea se limita a la obra; herramienta limitada al propietario. |
| Promociones | Publicación, marketplace y periodo KDP Select | Marketplace de la plataforma y periodo de la publicación elegida. |
| Costes/resultados | Promoción | Promociones limitadas a obras accesibles por el usuario. |
| Metadatos/periodos KDP | Publicación | Publicaciones limitadas a obras accesibles por el usuario. |
| Marketplaces | Plataforma | Asociación explícita y obligatoria. |

`applied_version_id` en anclajes no se edita manualmente: lo gestiona el servicio que aplica una ilustración a una nueva versión del manuscrito.
