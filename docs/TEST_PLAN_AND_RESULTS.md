# Plan de pruebas y resultados

Fecha: 19 de agosto de 2026

## Cobertura ejecutada

1. Migraciones desde una base SQLite vacía y carga repetible de seeders.
2. Integridad referencial mediante `PRAGMA foreign_key_check`.
3. Autenticación, registro, recuperación de contraseña, roles y aislamiento por autor.
4. Dashboard con promociones, tareas, eventos, ingresos y periodos KDP Select reales.
5. Listado, creación y edición de los recursos Filament registrados.
6. Resolución de 105 relaciones Eloquent contra datos coherentes.
7. Obras, idiomas, versiones, capítulos y publicaciones.
8. Regalías, importación atómica, promociones y ROI.
9. Importación KDP CSV/XLSX, asociación por ASIN/formato, idempotencia, reprocesado y gráficos.
10. Servicio de IA con proveedor simulado y ausencia controlada de credenciales.
11. Sintaxis PHP, validez de Composer y compilación Vite de producción.
12. Audiolibros: trazabilidad de obra a regalía, acceso Filament, transición a publicación, QA y consentimiento de réplica de voz.

## Errores corregidos

- `BookEvent::eventBooks()` infería `book_event_id` en lugar de `event_id`.
- Se eliminó una relación inexistente entre eventos y visitas de distribución.
- `AiTask::prompts()` infería `ai_task_id` en lugar de `task_id`.
- `Permission::roles()` utilizaba `model_has_permissions` en vez de `role_has_permissions`.
- Las relaciones directas de usuarios en pivotes polimórficos no filtraban `model_type`.
- El seeder de movimientos físicos no utilizaba `print_run_id`.
- Los selectores de Promociones, Metadatos KDP y Periodos KDP Select podían generar etiquetas nulas o no textuales.
- Las pruebas de interfaz anteriores solo cubrían tablas vacías; ahora cargan y editan registros reales.

## Resultado final

- 67 pruebas superadas.
- 516 aserciones correctas.
- 21 listados y 21 formularios de edición probados con datos sembrados.
- 105 relaciones Eloquent resueltas correctamente.
- Migración limpia y seeders correctos.
- Ninguna violación de claves foráneas.
- Build de producción correcto.
- Informe KDP real reprocesado: 39 filas canónicas, 0 duplicadas y 0 errores.
- 14 títulos/ediciones ausentes detectados y conservados automáticamente en el catálogo KDP pendiente.
