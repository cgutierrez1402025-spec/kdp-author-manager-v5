# Documentación de KDP Author Manager

## Documentos vigentes

- [`TEST_PLAN_AND_RESULTS.md`](TEST_PLAN_AND_RESULTS.md): cobertura y último resultado verificado.
- [`FORM_RELATIONSHIP_AUDIT.md`](FORM_RELATIONSHIP_AUDIT.md): asociaciones entre entidades y formularios Filament.
- [`ERROR_REVIEW_PLAN.md`](ERROR_REVIEW_PLAN.md): estrategia de revisión y regresión.
- [`ERROR_CORRECTION_REPORT.md`](ERROR_CORRECTION_REPORT.md): correcciones ejecutadas durante la estabilización.
- [`REPOSITORY_CLEANUP_PLAN.md`](REPOSITORY_CLEANUP_PLAN.md): auditoría y fases de mantenimiento del repositorio.
- [`PHASED_MODERNIZATION.md`](PHASED_MODERNIZATION.md): ejecución y criterios de entrega de la nueva versión.
- [`UI_MODERNIZATION.md`](UI_MODERNIZATION.md): sistema visual y mejoras de experiencia implementadas.

## Fuente de verdad

- El esquema se define en `database/migrations`.
- Los datos de demostración se definen en `database/seeders`.
- Los recursos visibles se registran en `app/Providers/Filament/AdminPanelProvider.php`.
- Las versiones de dependencias se fijan en `composer.lock` y `package-lock.json`.
- El resultado de pruebas vigente se registra en `TEST_PLAN_AND_RESULTS.md`.

Los documentos de prototipo o informes anteriores no deben utilizarse como instrucciones de instalación si contradicen el README raíz o el código actual.
