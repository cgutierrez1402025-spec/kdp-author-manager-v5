# Documentación de KDP Author Manager

## Documentos vigentes

- [`MANUAL_DE_USUARIO.md`](MANUAL_DE_USUARIO.md): funcionamiento completo para autores y administradores.
- [`IMPORTACION_AMAZON_KDP.md`](IMPORTACION_AMAZON_KDP.md): formatos, normalización, duplicados y reprocesado.
- [`ARQUITECTURA_Y_MODELO_DE_DATOS.md`](ARQUITECTURA_Y_MODELO_DE_DATOS.md): componentes, dominios y fuentes del panel.
- [`OPERACION_DESPLIEGUE_Y_MANTENIMIENTO.md`](OPERACION_DESPLIEGUE_Y_MANTENIMIENTO.md): instalación, despliegue, almacenamiento y copias.
- [`SEGURIDAD_PRIVACIDAD_Y_ROLES.md`](SEGURIDAD_PRIVACIDAD_Y_ROLES.md): autorización, archivos, IA y RGPD.
- [`PRUEBAS_Y_RESOLUCION_DE_PROBLEMAS.md`](PRUEBAS_Y_RESOLUCION_DE_PROBLEMAS.md): validación y diagnóstico.
- [`INFORME_PANEL_DECISIONES_KDP_IA.md`](INFORME_PANEL_DECISIONES_KDP_IA.md): informe y hoja de ruta que originan la v5.
- [`EVALUACION_INFORMES_KDP_Y_MODELO_DE_DATOS.md`](EVALUACION_INFORMES_KDP_Y_MODELO_DE_DATOS.md): matriz de informes, duplicados, campos vacíos y decisión sobre nuevas tablas.
- [`RESULTADO_IMPORTACION_MULTIPLE_KDP.md`](RESULTADO_IMPORTACION_MULTIPLE_KDP.md): sesiones, detección automática, ZIP, seguridad y pruebas de la carga múltiple.
- [`INFORME_ESTABILIZACION_INTEGRAL_2026.md`](INFORME_ESTABILIZACION_INTEGRAL_2026.md): correcciones aplicadas a integridad, privacidad, operación y CI.
- [`PLAN_MEJORAS_DERIVADAS_MANUAL_BBDD.md`](PLAN_MEJORAS_DERIVADAS_MANUAL_BBDD.md): hoja de ruta contrastada con el manual profesional HTML aportado.
- [`AUDITORIA_PAGOS_KDP.md`](AUDITORIA_PAGOS_KDP.md): limitaciones de pagos y diseño de conciliación pago‑obra.
- [`INFORME_INTEGRACION_AUDIOLIBROS.md`](INFORME_INTEGRACION_AUDIOLIBROS.md): opciones KDP/ACX y cambios completos del dominio.
- [`ENUNCIADO_ESTUDIANTES_MODULO_AUDIOLIBROS.md`](ENUNCIADO_ESTUDIANTES_MODULO_AUDIOLIBROS.md): proyecto evaluable para diseñar e implementar el módulo.
- [`PLAN_IMPLEMENTACION_AUDIOLIBROS.md`](PLAN_IMPLEMENTACION_AUDIOLIBROS.md): fases, dependencias y puertas de calidad.
- [`RESULTADO_IMPLEMENTACION_AUDIOLIBROS.md`](RESULTADO_IMPLEMENTACION_AUDIOLIBROS.md): alcance ejecutado, esquema, reglas y comprobaciones de la versión.
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
