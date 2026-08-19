# Resultado de la importación múltiple de informes KDP

Fecha de verificación: 19 de agosto de 2026.

## Funcionalidad entregada

- Selección conjunta de hasta 20 CSV/XLSX.
- Carga de ZIP de hasta 100 MB que contengan CSV, TXT o XLSX.
- Detección automática del tipo por cabeceras y hojas, con porcentaje de confianza.
- Detección del periodo desde nombres `AAAA-MM` o `MM-AAAA` y periodo manual de respaldo.
- Autorrelleno reactivo de **Mes del informe** cuando todos los nombres seleccionados indican el mismo periodo; los meses diferentes se guardan individualmente.
- Una `import_session` por operación y un `import_batch` independiente por archivo.
- Transacción, contadores, errores y posibilidad de reprocesado por archivo.
- Archivos duplicados omitidos sin cancelar los restantes.
- Informes no reconocidos conservados como `needs_review`.
- Resumen en **Publicaciones → Sesiones de importación** y filtro de lotes por sesión.
- Aislamiento de sesiones y lotes por autor.

## Seguridad de ZIP

La aplicación no extrae rutas del ZIP directamente. Lee sólo entradas admitidas, utiliza `basename`, genera un directorio privado único e ignora ejecutables. Limita cada entrada a 20 MB, el contenido total a 100 MB y el resultado a 20 informes.

## Compatibilidad

El servicio múltiple orquesta `KdpReportImportService`; no mantiene un segundo normalizador. Por ello conserva asociación ASIN/formato, catálogo detectado, huellas de filas, importes originales, monedas y gráficos existentes. Los lotes anteriores tienen `import_session_id` nulo y continúan siendo consultables.

## Verificación realizada

- Suite completa: 74 pruebas y 543 aserciones correctas.
- Casos específicos: detección, dos tipos en una sesión, duplicado, ZIP, informe desconocido y acceso indebido.
- Migración aplicada sin recrear tablas.
- Tras la migración siguen presentes 4 lotes y 317 filas KDP normalizadas que ya existían en la copia de seguridad previa.
- Copia de seguridad: `/private/tmp/kdp-author-manager-v5-before-bulk-import.sqlite`.
- Compilación Vite y comprobación Pint correctas.

## Límite operativo

Los archivos se aíslan pero se procesan secuencialmente dentro de la petición web. Los límites actuales evitan cargas descontroladas. Si las muestras reales justifican informes mayores, el siguiente incremento debe mover cada lote a una cola Laravel y conservar el mismo modelo `import_sessions`/`import_batches`.
