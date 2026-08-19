# Seguridad, privacidad y roles

## Autorización

El acceso al panel exige un rol reconocido. Los recursos editoriales aplican políticas y ámbitos por autor. Las consultas de importación y gráficos filtran por `user_id`; los roles globales autorizados pueden consultar todos los autores.

Toda nueva consulta debe mantener este criterio tanto en la interfaz como en el servicio. Ocultar un enlace no sustituye la autorización del servidor.

## Archivos KDP

- se guardan en almacenamiento privado;
- se valida extensión/MIME y tamaño;
- se identifican por hash;
- no deben enviarse a proveedores de IA;
- pueden contener nombres, ventas e información económica y deben tratarse como confidenciales.

Antes de producción se recomienda añadir análisis antimalware, límites de cuota de almacenamiento y borrado/retención configurable.

## Credenciales

`.env`, claves API y credenciales de base de datos no se versionan. Las claves deben rotarse y utilizar privilegios mínimos. Las cuentas demo y la contraseña `password` deben eliminarse o cambiarse.

## IA

El proveedor recibe únicamente el contexto mínimo necesario y con consentimiento. No se envían credenciales Amazon, información bancaria ni manuscritos completos por defecto. Toda escritura generada debe requerir confirmación humana y registrar proveedor, modelo, finalidad y consumo.

## RGPD

Defina responsable, base legal, finalidad, retención y procedimiento para acceso, rectificación y supresión. Los archivos y filas importadas pertenecen al usuario que los carga. La supresión debe contemplar base de datos, originales privados, copias y proveedores externos utilizados.

## Riesgos conocidos

- cambios de estructura en informes Amazon;
- archivos manipulados o excesivamente grandes;
- suma incorrecta de monedas;
- doble contabilización entre hojas o fuentes;
- acceso transversal entre autores;
- exposición de contenido a proveedores IA.

Los controles actuales reducen duplicados, aíslan usuarios y separan monedas. Las pruebas deben ampliarse cada vez que se añada un nuevo informe o idioma.

