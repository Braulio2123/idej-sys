# Fase 27 - Notificaciones internas

## Objetivo

Agregar un sistema interno de notificaciones para IDEJ-SYS sin tocar Portal Alumno.

El objetivo es que los usuarios internos reciban avisos operativos visibles en el panel, especialmente sobre caja, solicitudes docentes y agenda operativa.

## Cambios principales

### 1. Tabla `notificaciones_internas`

Se agregó una tabla propia para registrar notificaciones por:

- Usuario específico.
- Rol interno.
- Notificación global.

Campos relevantes:

- `usuario_id`
- `rol_clave`
- `tipo`
- `modulo`
- `titulo`
- `mensaje`
- `url`
- `severidad`
- `referencia_tipo`
- `referencia_id`
- `hash`
- `metadata`
- `leida_at`
- `archivada_at`

### 2. Modelo `NotificacionInterna`

Se agregó el modelo:

`app/Models/NotificacionInterna.php`

Permite:

- Consultar notificaciones visibles para un usuario.
- Marcar como leída.
- Marcar como no leída.
- Archivar.
- Sincronizar notificaciones sin duplicarlas mediante `hash`.

### 3. Módulo web de notificaciones

Ruta principal:

`/notificaciones`

Controlador:

`app/Http/Controllers/NotificacionInternaController.php`

Vista:

`resources/views/notificaciones/index.blade.php`

Permite:

- Ver pendientes.
- Ver leídas.
- Ver todas.
- Filtrar por severidad.
- Marcar una como leída.
- Marcar una como no leída.
- Marcar todas como leídas.
- Archivar notificaciones.

### 4. Campana en layout

Se modificó:

`resources/views/layouts/app.blade.php`

Ahora el encabezado muestra una campana con contador de notificaciones pendientes.

### 5. Comando operativo

Se agregó:

`app/Console/Commands/SincronizarNotificacionesOperativas.php`

Comando:

`php artisan idej:notificaciones-operativas`

Genera avisos por:

- Cajas abiertas de días anteriores.
- Solicitudes docentes pendientes por más de 2 días.
- Solicitudes docentes observadas.
- Solicitudes docentes autorizadas vencidas sin pago.
- Sesiones próximas con aula/liga u horario incompleto.

### 6. Programación automática

Se agregó al scheduler para ejecutarse cada 30 minutos:

`idej:notificaciones-operativas`

En producción debe existir cron o scheduler activo para que esto corra automáticamente.

## Notas de producción

- El sistema no envía correos en esta fase; solo notificaciones internas.
- El comando puede ejecutarse manualmente para pruebas.
- La tabla usa `hash` único para evitar duplicados.
- Las notificaciones archivadas no se muestran en la lista normal.
- Las notificaciones se filtran según usuario, rol o notificación global.
