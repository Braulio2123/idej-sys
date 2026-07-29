# IDEJ-SYS — Entrega corregida

Fecha de corte: 28 de julio de 2026.

Esta entrega integra los bloques críticos de corrección realizados sobre el sistema administrativo interno. No se modificó la lógica funcional del Portal Alumno.

## Requisitos mínimos

- PHP 8.3 recomendado.
- Extensiones PHP: `ctype`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `xmlwriter` y `zip`.
- MySQL 8 o MariaDB compatible.
- Composer 2.
- Node.js y npm compatibles con `package-lock.json`.

## Instalación local limpia

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm ci
npm run build
php artisan optimize:clear
php artisan test
```

En Linux/macOS sustituye `copy` por `cp`.

## Base exclusiva para pruebas automáticas

La suite está configurada para MySQL y utiliza `idej_sys_testing`. Debe crearse una vez antes de ejecutar PHPUnit:

```sql
CREATE DATABASE idej_sys_testing
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Nunca uses la base `idej_sys` como base de pruebas, porque `RefreshDatabase` elimina y reconstruye tablas. La aplicación normal continúa usando la base indicada en `.env`; PHPUnit utiliza la base aislada indicada en `phpunit.xml`.

```bash
php artisan optimize:clear
php artisan test
```

## Validación antes de producción

Crea el `.env` real a partir de `.env.production.example` o `.env.railway.example`. No publiques ninguno de esos archivos con credenciales completas.

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan idej:validar-produccion --strict
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan test
npm ci
npm run build
```

El comando `idej:validar-produccion --strict` devuelve error cuando detecta configuración crítica insegura, extensiones faltantes, correo de recuperación no operativo o migraciones pendientes.

## Controles operativos importantes

- `APP_DEBUG=false` en producción.
- `APP_URL` debe usar HTTPS y el dominio definitivo.
- `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true` y `SESSION_ENCRYPT=true`.
- Configurar SMTP real para recuperación de contraseña.
- Mantener en `false` los recordatorios externos y cargos recurrentes automáticos hasta autorización institucional.
- Ejecutar scheduler y worker como servicios separados cuando corresponda.
- Respaldar en conjunto base de datos y `storage/app/private`.
- Probar periódicamente una restauración completa; un respaldo no verificado no debe considerarse válido.

## Archivos excluidos deliberadamente del ZIP

- `.env` real.
- `.git`.
- `vendor`.
- `node_modules`.
- logs, sesiones y cachés.
- `public/storage`.
- respaldos o archivos privados cargados.
- paquetes ZIP y documentos de auditorías antiguas que no forman parte del runtime.

## Portal Alumno

Se conservaron sin cambios:

- `routes/portal_alumno.php`
- `app/Http/Controllers/PortalAlumno`
- `app/Models/PortalAlumno`
- `resources/views/portal_alumno`

## Revisión posterior al primer `migrate:fresh --seed`

La versión revisada corrige la referencia obsoleta `CalendarioAcademico::ESTATUS_PLANEADO` que impedía completar `DatosDemoIntegralSeeder`. Después de sustituir el paquete, debe ejecutarse nuevamente:

```bash
php artisan migrate:fresh --seed
```

Este comando elimina y reconstruye la base de datos; debe utilizarse únicamente en desarrollo o sobre una base temporal.


### Actualización V4

Se corrigió la detección de doble envío para usuarios invitados. La clave UUID de operación ya no queda invalidada por una rotación del ID de sesión.
