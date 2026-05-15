# Auditoría Fase 22 - Endurecimiento inicial para producción

## Alcance

Esta fase aplica únicamente al sistema interno administrativo de IDEJ-SYS. No modifica Portal Alumno.

## Cambios aplicados

### 1. Vistas de error personalizadas

Se agregaron vistas institucionales para:

- `resources/views/errors/403.blade.php`
- `resources/views/errors/404.blade.php`
- `resources/views/errors/419.blade.php`
- `resources/views/errors/500.blade.php`
- `resources/views/errors/503.blade.php`
- `resources/views/errors/layout.blade.php`

Objetivo: evitar pantallas genéricas, mensajes técnicos expuestos y mejorar la experiencia cuando haya rutas inexistentes, sesión expirada, falta de permisos o fallas internas.

### 2. Sidebar persistente

Se actualizó `resources/views/layouts/app.blade.php` para guardar el estado del menú lateral en `localStorage` con la llave:

```js
idej_sidebar_open
```

Si el usuario cierra el menú y recarga la página, el menú permanece cerrado.

### 3. Perfil interno corregido

Se reemplazó la vista de perfil basada en `<x-app-layout>` por una vista integrada al layout real del sistema interno:

- `resources/views/profile/edit.blade.php`

El usuario puede:

- Modificar nombre.
- Modificar correo.
- Cambiar contraseña.
- Ver rol actual.
- Ver estado de cuenta.
- Ver último acceso registrado.
- Ver última IP.
- Ver fecha de cambio de contraseña.

La eliminación propia de cuenta queda bloqueada por política institucional.

### 4. Seguridad base por encabezados HTTP

Se agregó middleware:

- `app/Http/Middleware/SecurityHeaders.php`

Y se registró en el grupo `web` dentro de:

- `bootstrap/app.php`

Encabezados agregados:

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Cross-Origin-Opener-Policy: same-origin`

### 5. Usuarios internos desactivables, no eliminables

Se agregó migración:

- `database/migrations/2026_05_14_000001_add_security_fields_to_usuarios_table.php`

Campos agregados a `usuarios`:

- `activo`
- `ultimo_acceso_at`
- `ultimo_login_ip`
- `ultimo_user_agent`
- `password_changed_at`

Se actualizó:

- `app/Models/Usuario.php`
- `app/Http/Controllers/UsuarioController.php`
- `resources/views/usuarios/index.blade.php`

Ahora los usuarios no se eliminan físicamente. Se desactivan y se pueden reactivar. Esto conserva trazabilidad de pagos, cortes, becas, solicitudes y bitácora.

### 6. Login bloquea usuarios desactivados

Se actualizó:

- `app/Http/Requests/Auth/LoginRequest.php`

Si un usuario está desactivado, no puede iniciar sesión.

### 7. Registro de último acceso

Se actualizó:

- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

Al iniciar sesión se registra:

- Fecha/hora del último acceso.
- IP.
- User-Agent.

### 8. Cambio de contraseña registra fecha

Se actualizó:

- `app/Http/Controllers/Auth/PasswordController.php`

Al cambiar contraseña se registra `password_changed_at`.

### 9. Variables de sesión para producción

Se actualizaron ejemplos de ambiente:

- `.env.example`
- `.env.railway.example`

En producción se recomienda:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

## Pendientes siguientes recomendados

1. Proteger documentos/comprobantes en disco privado.
2. Bloquear transaccionalmente pagos, caja y solicitudes de pago docente.
3. Centralizar permisos por módulo.
4. Agregar doble factor para roles críticos.
5. Agregar notificaciones internas y actualización parcial en vivo.
6. Agregar panel de conflictos de agenda.
