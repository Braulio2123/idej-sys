# Auditoría Fase Local 17 — Mantenimiento, respaldos y salud del sistema

## Objetivo

Agregar herramientas internas para el área de Sistemas que permitan revisar el estado técnico de IDEJ-SYS, ejecutar acciones seguras de mantenimiento y generar respaldos manuales sin entrar al servidor o terminal.

## Alcance implementado

### Nuevo módulo

- Menú: **Administración → Mantenimiento**
- Acceso: **Administrador IDEJ** y **Sistemas IDEJ**

### Funciones agregadas

- Diagnóstico de Laravel, PHP, ambiente, debug, URL y zona horaria.
- Verificación de conexión a base de datos.
- Conteo de tablas detectadas.
- Revisión de `storage`, `bootstrap/cache` y `public/storage`.
- Consulta de tamaño de `storage/app/public`.
- Consulta de tamaño y fecha de modificación de `storage/logs/laravel.log`.
- Consulta de migraciones mediante `php artisan migrate:status`.
- Limpieza de caché con `php artisan optimize:clear`.
- Creación/verificación de storage link con `php artisan storage:link`.
- Limpieza del log principal `storage/logs/laravel.log`.
- Descarga de respaldo SQL de la base de datos MySQL/MariaDB.
- Descarga de respaldo ZIP de archivos cargados en `storage/app/public`.
- Checklist básico previo a producción.

## Archivos agregados

- `app/Http/Controllers/MantenimientoController.php`
- `resources/views/sistema/mantenimiento.blade.php`
- `AUDITORIA_FASE_17_MANTENIMIENTO_SISTEMA.md`

## Archivos modificados

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Traits/RegistraBitacora.php`

## Decisiones técnicas

### 1. Respaldo SQL integrado sin depender de `mysqldump`

El respaldo de base de datos se genera desde PHP consultando:

- `SHOW TABLES`
- `SHOW CREATE TABLE`
- registros de cada tabla mediante Query Builder

Esto evita depender de que `mysqldump` esté disponible en el PATH de Windows/Laragon.

### 2. Respaldo de archivos solo de `storage/app/public`

No se respalda todo el proyecto ni `vendor`, `node_modules`, cachés o logs. El objetivo es respaldar archivos subidos por usuarios:

- comprobantes,
- documentos de alumnos,
- logos institucionales,
- adjuntos públicos.

### 3. Acciones registradas en bitácora

Las acciones de mantenimiento quedan auditadas:

- limpiar caché,
- crear storage link,
- limpiar logs,
- descargar backup de base de datos,
- descargar backup de archivos.

## Riesgos controlados

- El módulo está protegido por rol `Admin,Sistemas`.
- No permite eliminar datos operativos.
- Los respaldos se generan bajo demanda y se descargan inmediatamente.
- Los archivos temporales se eliminan después de enviarse al navegador.

## Pendientes futuros recomendados

- Programar respaldos automáticos diarios/semanales.
- Registrar historial persistente de respaldos generados.
- Subir respaldos a almacenamiento externo.
- Añadir verificación de integridad de respaldos.
- Crear pantalla de restauración solo para ambiente local, no producción.
