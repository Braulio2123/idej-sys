# IDEJ-SYS — Auditoría y correcciones Fase 33

## Alcance

Se atienden las observaciones 28 y 29 reportadas en pruebas manuales del sistema interno IDEJ-SYS:

1. Al cancelar la confirmación de **Generar contraseña temporal**, el botón quedaba en estado “Procesando...” hasta recargar la página.
2. Después de iniciar sesión con contraseña temporal y cambiarla, el sistema permanecía en la pantalla de perfil/cambio de contraseña; además, usuarios no administradores podían intentar modificar su correo electrónico desde **Mi perfil**.

No se modificó Portal Alumno.

## Diagnóstico operativo

### Observación 28 — botón “Procesando...” al cancelar

El problema no era de backend. El formulario no se enviaba, pero el listener global de formularios deshabilitaba los botones antes de que el `confirm()` del formulario terminara. Si el usuario presionaba **Cancelar**, el navegador detenía el envío, pero el botón ya había cambiado a “Procesando...”.

### Observación 29 — flujo de contraseña temporal y correo en perfil

El flujo de seguridad ya obligaba a cambiar contraseña temporal, pero al actualizarla regresaba a la misma pantalla de perfil. Esto generaba confusión porque el formulario de cambio de contraseña seguía visible como parte normal de “Mi perfil”.

Además, el correo institucional no debe ser editable por usuarios operativos. Por política institucional, el correo de acceso debe controlarse desde **Gestión de Usuarios** y solamente por Admin/Sistemas.

## Correcciones realizadas

### 1. Confirmaciones de formularios críticas

Se cambió la lógica global de envío de formularios:

- El sistema ahora valida primero si el formulario fue cancelado.
- Se agregó soporte a `data-confirm` antes de deshabilitar botones.
- Si el usuario presiona **Cancelar**, el botón ya no queda en “Procesando...”.
- Se actualizó Gestión de Usuarios para usar `data-confirm` en acciones críticas.

### 2. Eliminación de estados técnicos visibles

Se evitó que el layout principal muestre estados internos como:

- `password-updated`
- `profile-updated`
- `verification-link-sent`

Estos estados se manejan dentro de sus vistas correspondientes con mensajes institucionales.

### 3. Cambio forzado de contraseña temporal

Cuando el usuario cambia una contraseña temporal correctamente:

- `must_change_password` se limpia.
- La vigencia temporal se elimina.
- La sesión se actualiza con la nueva marca de cambio de contraseña.
- El usuario es enviado al dashboard con mensaje institucional.

Esto evita que parezca que debe volver a cambiarla inmediatamente.

### 4. Bloqueo de edición de datos durante contraseña temporal

Si el usuario aún tiene contraseña temporal pendiente:

- No puede actualizar nombre/correo desde perfil.
- La pantalla indica que primero debe cambiar su contraseña temporal.
- El middleware sigue bloqueando navegación a módulos hasta que cumpla el cambio.

### 5. Correo institucional protegido

Desde **Mi perfil**:

- Usuarios Admin/Sistemas pueden modificar su correo.
- Usuarios operativos no pueden modificar correo.
- El campo se muestra en modo solo lectura.
- El backend ignora cualquier intento de manipulación del correo si el usuario no tiene permiso.

La edición de correos institucionales para usuarios operativos debe hacerse desde **Gestión de Usuarios** por Admin/Sistemas.

## Archivos modificados

- `resources/views/layouts/app.blade.php`
- `resources/views/usuarios/index.blade.php`
- `resources/views/profile/edit.blade.php`
- `app/Http/Controllers/Auth/PasswordController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Requests/ProfileUpdateRequest.php`

## Migraciones nuevas

No hay migraciones nuevas en esta fase.

## Validación técnica

Se validó sintaxis PHP en:

- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/Auth/PasswordController.php`
- `app/Http/Requests/ProfileUpdateRequest.php`
- `app/Http/Middleware/EnsureUsuarioInternoActivo.php`
- `app/Models/Usuario.php`

Resultado: `PHP_LINT_OK`.

## Cómo probar estos cambios

### Prueba 1 — cancelar contraseña temporal

1. Inicia sesión como Admin o Sistemas.
2. Entra a **Usuarios**.
3. Presiona **Contraseña temporal** en cualquier usuario activo.
4. Cuando aparezca la ventana de confirmación, presiona **Cancelar**.
5. El botón debe quedarse normal; no debe decir “Procesando...”.

### Prueba 2 — aceptar contraseña temporal

1. En **Usuarios**, presiona **Contraseña temporal**.
2. Acepta la confirmación.
3. El botón debe mostrar “Procesando...” solamente mientras se envía el formulario.
4. El sistema debe mostrar la contraseña temporal una sola vez.

### Prueba 3 — inicio con contraseña temporal

1. Cierra sesión.
2. Inicia sesión con el usuario al que generaste contraseña temporal.
3. El sistema debe enviarte a **Mi perfil**.
4. Debe bloquear navegación a otros módulos hasta cambiar contraseña.
5. Cambia la contraseña temporal por una nueva.
6. El sistema debe enviarte al dashboard con mensaje de éxito.
7. Ya no debe aparecer el aviso de cambio forzado.

### Prueba 4 — correo de usuario operativo

1. Inicia sesión con un usuario que no sea Admin ni Sistemas.
2. Entra a **Mi perfil**.
3. El correo debe aparecer en solo lectura.
4. No debe poder modificarse desde la interfaz.

### Prueba 5 — manipulación manual del correo

1. Con usuario no Admin/Sistemas, intenta manipular el formulario desde el navegador agregando `email` al request.
2. El backend no debe actualizar el correo.

### Prueba 6 — correo de Admin/Sistemas

1. Inicia sesión como Admin o Sistemas.
2. Entra a **Mi perfil**.
3. El correo sí debe poder modificarse.

## Advertencias de producción

- No usar `migrate:fresh` en producción.
- La recuperación por correo requiere SMTP real configurado en `.env`.
- La política institucional recomendada es que correos y roles se administren desde **Gestión de Usuarios**, no desde perfiles operativos.
- Mantener `APP_DEBUG=false` en producción.

## Recomendación siguiente

La siguiente fase puede enfocarse en: cierre de sesiones en otros dispositivos al cambiar contraseña, confirmación de contraseña para acciones de usuarios críticos y trazabilidad más detallada de cambios de correo/rol.
