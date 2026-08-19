# Resultado: creación de obras y materialización de pagos KDP

Fecha: 19 de agosto de 2026.

## Catálogo detectado

Los informes siguen entrando primero en `kdp_report_rows` y `kdp_catalog_items`. En **Publicaciones → Catálogo detectado KDP**, un registro pendiente ofrece ahora:

- **Crear obra y edición**: solicita título, autor, idioma, tipo de obra, marketplace y formato.
- **Vincular a obra**: selecciona una obra y su idioma; el manuscrito final es opcional.
- **Ignorar**: descarta el elemento sin borrar la fila original del informe.

La creación se ejecuta en una transacción y añade registros en `works`, `work_languages`, `publications` y `kdp_metadata`. Después actualiza `kdp_catalog_items` y todas sus `kdp_report_rows` con las relaciones creadas.

Una publicación procedente del catálogo puede tener `manuscript_version_id = NULL` y estado `catalog_review`. Esto representa una edición comercial histórica cuyo archivo editorial todavía no se ha aportado; no se crea un manuscrito ficticio. El flujo normal de creación manual sigue exigiendo una versión final.

## Pagos

Las filas `payment` se materializan además en:

- `kdp_payments`: identidad del pago, fecha, estado, marketplace, moneda, importe, retención, tipo de cambio y último lote.
- `kdp_payment_allocations`: relación auditable con la fila fuente y, sólo cuando exista evidencia, con una publicación.

Por defecto un pago agregado sin ASIN queda `unallocated`. No se atribuye a una obra por aproximación. La clave `(user_id, payment_number, currency)` hace el proceso idempotente.

Los informes antiguos se pueden reconstruir con:

```bash
php artisan kdp:materialize-payments
```

## Estados del catálogo

- `pending`: pendiente de revisión.
- `linked`: obra y edición vinculadas.
- `ambiguous`: requiere resolver varias coincidencias.
- `incomplete`: faltan datos mínimos.
- `ignored`: descartado por decisión del usuario.

## Seguridad e integridad

- Un autor sólo puede promocionar sus propios elementos.
- Título, autor, idioma, marketplace y formato son obligatorios al crear.
- El marketplace debe pertenecer a Amazon KDP.
- Si se indica manuscrito, debe ser final y pertenecer a la obra y el idioma.
- Un ASIN ya asignado a otra obra bloquea la operación.
- Los archivos y filas originales no se modifican ni eliminan.
