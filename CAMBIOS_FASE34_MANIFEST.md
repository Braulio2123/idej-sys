# Manifest Fase 34

## Objetivo

Implementar base operativa para recordatorios automáticos, mejorar notificaciones internas sin recarga visible, optimizar consultas frecuentes y dar contenido útil al dashboard de cada rol.

## Migraciones nuevas

- `database/migrations/2026_07_07_000005_create_recordatorios_enviados_table.php`
- `database/migrations/2026_07_07_000006_add_notification_contact_fields_to_usuarios_table.php`
- `database/migrations/2026_07_07_000007_add_indexes_for_dashboard_and_reminders.php`

## Archivos clave

- `app/Console/Commands/EnviarRecordatoriosPago.php`
- `app/Services/CanalNotificacionExternaService.php`
- `app/Models/RecordatorioEnviado.php`
- `app/Http/Controllers/NotificacionInternaController.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`
- `resources/views/notificaciones/index.blade.php`
- `resources/views/layouts/app.blade.php`
- `routes/console.php`
- `routes/web.php`
- `config/idej_recordatorios.php`

## Portal Alumno

No modificado.
