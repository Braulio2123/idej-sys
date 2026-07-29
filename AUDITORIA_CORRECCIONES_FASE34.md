# AUDITORÍA Y CORRECCIONES FASE 34 — Recordatorios, notificaciones, rendimiento y dashboards

## Alcance

Se atendieron los puntos 30 a 34 solicitados para IDEJ-SYS interno:

30. Estrategia de recordatorios automáticos por correo, SMS o WhatsApp.  
31. Prueba y mejora de notificaciones internas sin recargar página.  
32. Optimización inicial de consultas frecuentes en base de datos.  
33. Dashboard útil para todos los roles internos de prueba.  
34. Guía para probar notificaciones en tiempo real y aclaración de dependencias extra.

Portal Alumno no fue modificado.

## Diagnóstico operativo

### Recordatorios automáticos

Ya existía un comando básico de recordatorios por correo, pero no tenía control fuerte contra duplicados diarios, ni estructura clara para SMS/WhatsApp, ni programación institucional mediante scheduler.

Se corrigió con:

- Tabla `recordatorios_enviados` para evitar duplicar mensajes al mismo alumno por canal y día.
- Comando mejorado `php artisan app:enviar-recordatorios` con opciones por canal.
- Configuración separada en `config/idej_recordatorios.php`.
- Variables nuevas en `.env.example`.
- Soporte seguro para correo, SMS y WhatsApp.
- Si SMS/WhatsApp no están configurados, el sistema no truena: registra el intento en log y lo reporta como canal no configurado.

### Notificaciones internas

La Fase 32 ya usaba consulta automática cada 30 segundos. Para operación real se bajó a 5 segundos configurables y se reforzó para que no se vea recarga ni parpadeo de página.

Se agregó:

- Endpoint JSON sin cache.
- Botón “Probar notificación”.
- Botón “Sincronizar alertas” para Admin/Sistemas.
- Actualización silenciosa de campana cada 5 segundos.
- Pulso visual breve cuando entra una notificación nueva.
- No se recarga la página completa.

### Rendimiento BD

El sistema tenía varios listados y dashboards con filtros por estatus, fecha, alumno, caja, solicitud, pago y notificaciones. Se agregaron índices para consultas frecuentes:

- Alumnos por estatus financiero/académico.
- Cargos por estatus/vencimiento/alumno/concepto.
- Pagos por fecha/método/alumno/usuario.
- Solicitudes docentes por estatus/fecha límite/creador.
- Feed de notificaciones internas por fecha/estado.

Esto no reemplaza una auditoría con `EXPLAIN`, pero sí reduce carga en los puntos operativos más consultados.

### Dashboards por rol

El dashboard anterior dependía de bloques por permisos y algunos roles veían secciones muy vacías. Se agregó un panel inicial por rol con:

- Tarjetas útiles por área.
- Acciones rápidas.
- Lectura rápida de alertas.

Roles cubiertos:

- Admin.
- Sistemas.
- Dirección.
- CAdmin.
- Académica.
- Recepción.
- RRPP.
- Finanzas.

## Archivos modificados

- `.env.example`
- `config/idej_recordatorios.php`
- `routes/web.php`
- `routes/console.php`
- `app/Console/Commands/EnviarRecordatoriosPago.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/NotificacionInternaController.php`
- `app/Http/Controllers/UsuarioController.php`
- `app/Models/RecordatorioEnviado.php`
- `app/Models/Usuario.php`
- `app/Services/CanalNotificacionExternaService.php`
- `resources/views/dashboard.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/notificaciones/index.blade.php`
- `resources/views/usuarios/create.blade.php`
- `resources/views/usuarios/edit.blade.php`
- `database/migrations/2026_07_07_000005_create_recordatorios_enviados_table.php`
- `database/migrations/2026_07_07_000006_add_notification_contact_fields_to_usuarios_table.php`
- `database/migrations/2026_07_07_000007_add_indexes_for_dashboard_and_reminders.php`

## Migraciones nuevas

Sí hay migraciones nuevas:

```bash
php artisan migrate
```

Nuevas migraciones:

- `2026_07_07_000005_create_recordatorios_enviados_table.php`
- `2026_07_07_000006_add_notification_contact_fields_to_usuarios_table.php`
- `2026_07_07_000007_add_indexes_for_dashboard_and_reminders.php`

## Cómo probar recordatorios

### Prueba sin enviar nada

```bash
php artisan app:enviar-recordatorios --canal=email --dry-run
```

Debe mostrar alumnos con adeudo que recibirían recordatorio.

### Prueba por correo local

En `.env` puedes usar:

```env
MAIL_MAILER=log
IDEJ_RECORDATORIOS_EMAIL=true
```

Ejecuta:

```bash
php artisan app:enviar-recordatorios --canal=email --limite=10
```

Revisa `storage/logs/laravel.log`.

### Prueba SMS/WhatsApp sin proveedor

```bash
php artisan app:enviar-recordatorios --canal=sms --limite=5
php artisan app:enviar-recordatorios --canal=whatsapp --limite=5
```

Si no están configurados, el comando debe advertir que el canal está desactivado o no configurado. No debe romper el sistema.

## Cómo probar notificaciones sin recargar

1. Entra con cualquier usuario interno.
2. Abre `/notificaciones`.
3. Presiona “Probar notificación”.
4. Abre otra pestaña con el dashboard o deja abierta la campana.
5. En máximo 5 segundos debe actualizarse el contador sin recargar la página.
6. La campana debe hacer un pulso breve si entra una notificación nueva.

Para generar alertas operativas reales:

```bash
php artisan idej:notificaciones-operativas
```

O desde la pantalla de notificaciones, con Admin/Sistemas, presiona “Sincronizar alertas”.

## Scheduler en local

Para probar programación automática sin configurar cron:

```bash
php artisan schedule:work
```

Déjalo abierto en una terminal.

## Scheduler en producción

Configurar cron del servidor:

```bash
* * * * * cd /ruta/idej-sys && php artisan schedule:run >> /dev/null 2>&1
```

## ¿Se requiere instalar algo extra?

Para la solución actual: no.

La campana usa consulta silenciosa cada 5 segundos. No recarga la página y no muestra loading visible.

Para tiempo real instantáneo por WebSocket sí se requiere instalar y configurar:

```bash
composer require laravel/reverb
php artisan reverb:install
npm install laravel-echo pusher-js
```

Además se requiere correr:

```bash
php artisan reverb:start
php artisan queue:work
```

Para SMS o WhatsApp real se requiere proveedor externo:

- SMS: Twilio, Vonage, Infobip u otro proveedor con API.
- WhatsApp: WhatsApp Cloud API de Meta o un BSP.

## Advertencias de producción

- No enviar SMS/WhatsApp hasta tener consentimiento y números correctos.
- No ejecutar recordatorios cada minuto: puede generar spam.
- Mantener registro de envíos para evidencia institucional.
- En producción se recomienda cola con `queue:work` para correos masivos.
- Para dashboards lentos, revisar consultas con `EXPLAIN` sobre datos reales.

## Recomendación siguiente fase

Fase 35: cargos recurrentes por grupo/programa, plantillas de recordatorio, consentimiento de contacto, historial de comunicaciones y panel de campañas de cobranza/seguimiento.
