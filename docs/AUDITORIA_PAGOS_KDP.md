# Auditoría de pagos KDP y relación con obras

**Fecha:** 19 de agosto de 2026

## Resultado

La tabla `royalty_payments` existe, pero no permite responder de forma fiable “qué obras componen este pago”. Contiene plataforma, marketplace, periodo, importes esperado/recibido, retención, moneda, fechas, estado y justificante. No contiene usuario, número de pago KDP, método, tipo de cambio, moneda de destino, fuente del ingreso ni relaciones con regalías/publicaciones. Tampoco dispone actualmente de recurso Filament.

La base local contiene un único registro demostrativo de 245,80 EUR; no procede de un Payment Report importado. En `kdp_report_rows` no hay todavía filas reales de tipo pago.

## Qué aporta Amazon

El Payment Report contiene número de pago, marketplace, estado, fecha, método, ganancias netas, tipo de cambio e importe pagado. La descarga añade periodo de ventas, regalía devengada, retención y fuente —por ejemplo, ventas de ebook o regalías de suscripción—. No incluye ASIN, título ni obra. Fuente: [Payment Report de KDP](https://kdp.amazon.com/en_US/help/topic/G201436840).

El informe Prior Months' Royalties sí contiene ASIN/título, marketplace, moneda, formato, unidades, KENP, tipo de transacción y regalía. Por ello, la relación pago‑obra se obtiene conciliando ambos informes, no leyendo directamente el Payment Report.

## Conciliación posible

Para un pago se buscan transacciones definitivas de regalías con:

1. mismo usuario/cuenta KDP;
2. mismo periodo de ventas;
3. mismo marketplace;
4. misma moneda de devengo;
5. fuente compatible —venta de libro, KENP/suscripción, bonus o ajuste—;
6. suma igual a la regalía devengada, tolerando redondeos documentados.

Después se crean asignaciones por publicación/obra con el importe bruto de regalía. La retención y diferencias pueden:

- distribuirse proporcionalmente, marcando el resultado como calculado; o
- conservarse solo a nivel de pago, opción preferida cuando Amazon no aporta reparto por obra.

El importe recibido en otra moneda se deriva aplicando el FX del pago al conjunto. No debe atribuirse un tipo diferente a cada obra salvo que el informe lo documente.

## Tablas recomendadas

### `kdp_payments`

- `user_id`, `import_batch_id`;
- `payment_number`, `source`, `marketplace`;
- `sales_period_start/end`, `payment_date`, `status`, `payment_method`;
- `accrued_royalty`, `accrual_currency`;
- `tax_withholding`, `fx_rate`;
- `payment_amount`, `payment_currency`;
- `reconciliation_status`, `reconciled_at`;
- `raw_data`, timestamps.

Clave candidata: `(user_id, payment_number, source, marketplace)`. Debe validarse con muestras reales porque una transferencia puede contener varias líneas fuente.

### `kdp_payment_allocations`

- `kdp_payment_id`;
- `publication_id` y `work_id`, opcionales si aún no existe catálogo canónico;
- `kdp_catalog_item_id`;
- `accrued_amount`, `allocated_tax`, `allocated_payment_amount`;
- moneda original y moneda pagada;
- `allocation_method` (`exact_report_match`, `proportional`, `manual`);
- `confidence`, `notes`, timestamps.

Una restricción debe impedir dos asignaciones equivalentes para el mismo pago/publicación/fuente. La suma asignada se compara con la regalía devengada; cualquier diferencia queda como ajuste no asignado.

### `kdp_royalty_transactions`

Es necesaria antes de una conciliación robusta. Debe almacenar el detalle definitivo de Prior Months' Royalties. `royalty_entries` agrega por publicación/mes y pierde marketplace, fuente y moneda, por lo que no basta para emparejar pagos.

## Cambios en tablas existentes

- Mantener `royalty_payments` para pagos manuales o de otras plataformas, añadiendo `user_id` y un campo de procedencia.
- Relacionar opcionalmente `royalty_payments.kdp_payment_id` con el pago importado conciliado.
- No añadir `publication_id` directamente a `royalty_payments`: un pago contiene muchas obras.
- No generar asignaciones cuando solo existe Payment Report; deben esperar a las regalías definitivas.

## Formularios y vistas

1. Listado de pagos: número, periodo, marketplace, moneda, devengado, retención, pagado, estado y conciliación.
2. Detalle: datos originales, diferencias, obras asignadas y suma de control.
3. Acción “Conciliar”: propone coincidencias y requiere confirmación.
4. Acción “Asignar manualmente”: solo para diferencias o títulos no vinculados.
5. Alertas: pago fallido, no conciliado, suma fuera de tolerancia y pago sin informe mensual.

## Conclusión

Sí es posible mostrar qué obras corresponden a un pago, pero como una relación muchos‑a‑muchos calculada a partir de Payment Report + Prior Months' Royalties. Con el esquema actual no es fiable. Se requieren `kdp_payments`, `kdp_royalty_transactions` y `kdp_payment_allocations`.

