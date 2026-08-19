# Pruebas y resolución de problemas

## Comandos de calidad

```bash
composer validate --strict
composer format:check
composer analyse
php artisan test
npm run build
```

Las pruebas de importación cubren CSV, XLSX, asociación por ASIN/formato e idempotencia. La suite completa comprueba relaciones, autorización, panel, datos demo, promociones, regalías y flujos editoriales.

## El archivo ya fue importado

El SHA-256 coincide con un lote existente. Abra la lista y utilice **Reprocesar**. Si el archivo representa otro periodo pero sus bytes son idénticos, revise que haya descargado el filtro correcto; no se debe alterar manualmente solo para eludir el control.

## El lote termina con errores

Consulte el contador y el mensaje guardado en `import_errors`. Causas habituales:

- cabecera no reconocida o idioma no soportado;
- fila informativa sin ASIN;
- fecha o número con formato desconocido;
- estructura cambiada por Amazon.

El estado `completed` con errores significa que las filas válidas sí se conservaron.

## No aparecen gráficos

1. confirme que el lote está `completed` y tiene filas importadas;
2. compruebe que inició sesión con el autor que cargó el archivo o con un rol global;
3. use **Reprocesar** si la importación es anterior a una mejora del parser;
4. ejecute `php artisan optimize:clear`;
5. revise `storage/logs/laravel.log`.

## ASIN sin publicación vinculada

La fila puede graficarse por título aunque no esté vinculada. Cree/corrija la publicación con ASIN y formato, y reprocese el lote.

## Totales diferentes a KDP

Verifique filtros, periodo, hoja y naturaleza de la cifra. KDP distingue pedidos, unidades netas, KENP, regalías estimadas y consolidadas. Compare siempre la misma moneda. No sume las hojas `Ventas combinadas` y `Regalías de eBooks`, pues contienen datos solapados; el parser actual elige una fuente canónica.

## La aplicación no inicia

- compruebe `APP_KEY`;
- valide conexión y existencia de SQLite;
- ejecute `composer install` y `npm run build`;
- ejecute `php artisan migrate`;
- verifique permisos de `storage` y `bootstrap/cache`.

## Añadir soporte para otra cabecera

Añada el nombre normalizado a `KdpReportImportService::ALIASES`, cree un archivo de prueba mínimo y compruebe que no duplica una hoja ya soportada. Conserve el dato original y no cambie silenciosamente el significado de una métrica existente.

