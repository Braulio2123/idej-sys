# IDEJ-SYS — Auditoría y correcciones Fase 32

## Alcance

Esta fase atiende las observaciones 11 a 27 del documento de correcciones compartido por el usuario. El enfoque fue sistema interno administrativo/operativo. No se modificó Portal Alumno.

## Diagnóstico general

El sistema funcionaba en varios flujos, pero todavía tenía puntos operativos importantes por cerrar antes de producción: recuperación de contraseña dependiente del administrador, falta de política para no reutilizar contraseñas, notificaciones que no se actualizaban sin recargar, lenguaje técnico en mantenimiento, búsqueda limitada en alumnos/prospectos, ciclo escolar activo inconsistente, programas académicos demasiado simples, bitácora con hora poco precisa y error crítico al abrir nueva solicitud de pago docente.

## Correcciones aplicadas

### 1. Recuperación de contraseña y política de contraseñas

- Se mantiene el flujo de recuperación por correo del sistema interno con broker `usuarios` sobre `App\\Models\\Usuario`.
- Se agregó historial de contraseñas por usuario para impedir reutilizar contraseñas usadas en los últimos 6 meses.
- Admin/Sistemas ahora puede generar contraseña temporal para un usuario activo.
- La contraseña temporal vence a los 7 días.
- Al iniciar sesión con contraseña temporal, el usuario queda obligado a cambiarla antes de navegar otros módulos.
- Si la contraseña temporal venció, el sistema cierra la sesión y pide solicitar una nueva.

### 2. Notificaciones internas con actualización automática

- Se agregó endpoint JSON para resumen de notificaciones visibles por usuario/rol.
- La campana del layout se actualiza cada 30 segundos sin recargar la pantalla.
- El conteo, el texto de pendientes y la lista rápida de notificaciones se refrescan automáticamente.

### 3. Dashboard

- Se eliminó el bloque “Descargar IDEJ Mobile” porque actualmente no se requiere app móvil en el panel interno.

### 4. Mantenimiento del sistema

- Se reemplazaron textos técnicos visibles por lenguaje institucional entendible.
- “Crear/verificar storage link” ahora se presenta como “Reparar acceso a archivos públicos”.
- Se agregaron explicaciones para limpiar configuración, reparar acceso a archivos y vaciar registro técnico.
- Se aclaró que la bitácora institucional no se borra al vaciar el registro técnico principal.

### 5. Alumnos

- Se agregó búsqueda general por nombre, apellido, matrícula, correo, programa o grupo.
- Se mantuvieron filtros financieros, académicos, condición, programa y grupo.

### 6. Prospectos/RRPP

- Se amplió búsqueda por programa, asesor y medio de contacto, además de nombre, correo, teléfono y WhatsApp.
- Se dejó el flujo como operación de Recepción/RRPP con seguimiento y conversión, pero se recomienda fase posterior para embudo comercial más completo.

### 7. Títulos y navegación visual

- Se agregaron títulos de página a módulos que todavía mostraban “Panel de Control” en el encabezado aunque el usuario estuviera en otro módulo.
- Esto mejora la orientación del usuario cuando navega desde sidebar.

### 8. Ciclos escolares

- Se corrigió el checkbox de ciclo activo: al desmarcarlo ahora realmente se guarda como inactivo.
- Si se activa un ciclo escolar, los demás ciclos quedan inactivos automáticamente.
- Se agregó ayuda visual explicando que solo debe existir un ciclo activo.

### 9. Calendarios académicos

- Si el periodo se deja vacío y se selecciona ciclo escolar, el sistema toma el nombre del ciclo como periodo operativo.
- Se cambió el texto visible a “Periodo operativo” para evitar confusión con “Ciclo escolar”.
- Se agregó autollenado visual del periodo cuando cambia el ciclo.
- Los horarios sugeridos para materias ahora dependen del tipo de calendario: posgrado viernes-sábado, licenciatura sabatina, matutina, vespertina o personalizado.

### 10. Programas académicos

- El módulo dejó de ser solo nombre/nivel.
- Se agregaron campos operativos: clave, modalidad, duración por periodos, descripción y estado activo/inactivo.
- Si un programa ya tiene historial relacionado, al eliminarlo se inactiva en vez de borrarse físicamente.

### 11. Becas

- Se agregó selector de duración sugerida: 1, 3, 4, 6, 12 meses o indefinida.
- El sistema calcula fecha fin a partir de la fecha de inicio.
- Se mantiene la posibilidad de capturar fecha fin manualmente.

### 12. Cargos masivos

- Se añadió explicación operativa para que el usuario entienda que sirve para aplicar un cargo a varios alumnos de una sola vez.
- Se aclaró que la programación automática mensual y recordatorios por correo/SMS requieren una fase específica con scheduler.
- Se eliminó lenguaje poco institucional en encabezados.

### 13. Bitácora

- Se configuró zona horaria por `.env` con `APP_TIMEZONE=America/Mexico_City`.
- La bitácora muestra fecha y hora con segundos y zona horaria del sistema.
- El PDF de bitácora también usa la zona horaria configurada.

### 14. Solicitudes de pago docente

- Se corrigió el error crítico: `Unclosed '[' on line 236 does not match ')'` en `resources/views/solicitudes_pago/_form.blade.php`.
- Se reemplazó el armado complejo dentro de `@json()` por arreglos PHP preparados antes del script.
- Se corrigió el formateo de fechas para evitar errores al usar fechas como string, Carbon o null.

## Migraciones nuevas

Sí hay migraciones nuevas:

- `database/migrations/2026_07_07_000002_add_password_policy_to_usuarios_table.php`
- `database/migrations/2026_07_07_000003_create_usuario_password_histories_table.php`
- `database/migrations/2026_07_07_000004_add_operational_fields_to_programas_table.php`

## Validación realizada

- Sintaxis PHP validada en archivos modificados.
- Vistas Blade modificadas revisadas con `php -l`.
- No quedaron referencias a `App\\Models\\User` en el sistema interno.
- Se verificó que Portal Alumno no tuviera diferencias contra la base de Fase 31.
- No se ejecutó `php artisan route:list` en este entorno porque el ZIP limpio no incluye `vendor`. Debe ejecutarse después de `composer install`.

## Cómo probar estos cambios

### Instalación limpia local

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan route:list
php artisan serve
npm run dev
```

### Sobre base existente

```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate
php artisan storage:link
php artisan route:list
npm run dev
```

### Variables recomendadas

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=America/Mexico_City
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=idej_sys
DB_USERNAME=root
DB_PASSWORD=
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
FILESYSTEM_DISK=local
```

## Checklist manual

1. Abrir `/solicitudes_pago/create`. No debe aparecer error 500.
2. Crear solicitud docente desde calendario académico y verificar autollenado.
3. Login con usuario normal.
4. Desde Admin/Sistemas generar contraseña temporal para ese usuario.
5. Entrar con contraseña temporal: debe mandar a Mi perfil.
6. Intentar navegar a otro módulo sin cambiar contraseña: debe regresar a Mi perfil.
7. Cambiar contraseña por una nueva.
8. Intentar reutilizar una contraseña reciente: debe bloquear.
9. Recuperar contraseña por correo con SMTP configurado.
10. Confirmar que el dashboard ya no muestra “Descargar IDEJ Mobile”.
11. Revisar la campana de notificaciones; debe actualizar conteo/lista sin recargar después de máximo 30 segundos.
12. Entrar a Mantenimiento y confirmar que los botones tengan lenguaje entendible.
13. Buscar alumno por matrícula, nombre, programa y grupo.
14. Buscar prospecto por programa, asesor y medio de contacto.
15. Editar ciclo activo y desmarcarlo; debe quedar inactivo.
16. Activar otro ciclo; los demás deben quedar inactivos.
17. Crear calendario con ciclo escolar y periodo vacío; el periodo debe llenarse con el ciclo.
18. Crear/editar programa con clave, modalidad, duración y descripción.
19. Intentar eliminar programa con historial; debe inactivarse.
20. Crear beca usando duración sugerida y verificar fecha fin.
21. Revisar bitácora: debe mostrar fecha/hora con segundos.

## Advertencias de producción

- No usar `migrate:fresh` en producción.
- Configurar SMTP real si se quiere recuperación por correo.
- Usar `APP_ENV=production` y `APP_DEBUG=false`.
- Usar HTTPS y `SESSION_SECURE_COOKIE=true`.
- La contraseña temporal se muestra una sola vez; debe copiarse y entregarse por canal seguro.
- La actualización automática de notificaciones usa consulta periódica cada 30 segundos; si se requiere tiempo real instantáneo, implementar WebSockets en fase posterior.

## Siguiente fase recomendada

Fase 33: automatización operativa de cargos recurrentes y recordatorios.

Prioridades:

1. Programar cargos mensuales por grupo/programa/concepto.
2. Enviar recordatorios automáticos por correo.
3. Preparar integración futura SMS/WhatsApp institucional.
4. Autorizar cargos masivos antes de aplicarlos.
5. Generar folio y PDF de operación masiva.
6. Panel de seguimiento de cargos programados.
