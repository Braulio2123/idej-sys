# IDEJ-SYS — Guía de despliegue en Railway

## Estado de esta versión

Esta versión está preparada para operar como **sistema web Laravel**. La API pública quedó deshabilitada porque el sistema se usará desde la web institucional.

## Servicios recomendados en Railway

Para producción básica se requieren:

1. **App Service**: Laravel web.
2. **MySQL Service**: base de datos.

Opcionalmente, si se quieren ejecutar recordatorios y moratorios automáticos, crear un servicio adicional:

3. **Cron Service**: Laravel Scheduler.

## Variables de entorno para App Service

Usar como base `.env.railway.example`.

Variables mínimas:

```env
APP_NAME="IDEJ-SYS"
APP_ENV=production
APP_KEY=base64:PEGAR_LLAVE_GENERADA
APP_DEBUG=false
APP_URL=https://TU-DOMINIO.up.railway.app

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

La llave `APP_KEY` se genera localmente con:

```bash
php artisan key:generate --show
```

## Configuración de Railway incluida

El archivo `railway.json` define:

- Builder: `RAILPACK`.
- Build command: `npm run build`.
- Pre-deploy command: `railway/init-app.sh`.
- Healthcheck: `/up`.
- Reinicio automático en caso de falla.

El script `railway/init-app.sh` ejecuta:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Cron opcional

Si se desea activar el scheduler en Railway, crear otro servicio desde el mismo repositorio y usar como Start Command:

```bash
chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh
```

Este servicio ejecutará `php artisan schedule:run` cada minuto. En IDEJ-SYS esto puede servir para:

- Enviar recordatorios de pago.
- Aplicar moratorios programados.

## API pública

La API queda deshabilitada. `bootstrap/app.php` ya no carga `routes/api.php`.

Motivo: el sistema se usará vía web y no debe exponerse CRUD público de usuarios, alumnos ni reportes.

Si en el futuro se retoma una app móvil, primero implementar:

- Autenticación por token.
- Middleware por rol.
- Form Requests.
- API Resources.
- Rate limiting.
- Auditoría de accesos.

## Checklist antes de producción

- Confirmar que `APP_DEBUG=false`.
- Confirmar que `.env` no está en GitHub.
- Confirmar que `storage/logs` no está en GitHub.
- Confirmar que `node_modules` y `vendor` no están en GitHub.
- Confirmar que `php artisan migrate --force` corre sin errores.
- Confirmar que `/up` responde correctamente.
- Entrar con usuario administrador y cambiar contraseñas de prueba.
- Crear usuarios reales por área.
- No usar credenciales `admin123`, `recepcion123`, etc. en producción.
