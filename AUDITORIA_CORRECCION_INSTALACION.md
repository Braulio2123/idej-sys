# Auditoría y corrección de instalación - IDEJ-SYS

Fecha: 2026-07-03

## Alcance

Se revisó el ZIP `idej_sys1.0.zip` con el objetivo principal de dejar el proyecto instalable y ejecutable en entorno local con Laragon, sin intervenir funcionalmente en Portal Alumno.

## Correcciones aplicadas

1. Se corrigió el modelo `App\Models\Rol` para incluir las constantes usadas por `config/idej_permisos.php`:
   - `ADMIN`
   - `SISTEMAS`
   - `DIRECCION`
   - `CADMIN`
   - `ACADEMICA`
   - `RECEPCION`
   - `RRPP`
   - `FINANZAS`

2. Se eliminó el archivo incorrecto `app/Http/Controllers/AuditoriaController.php`, porque contenía una clase de modelo bajo el namespace `App\Models`, lo que provocaba advertencia PSR-4 en Composer.

3. Se corrigió `app/Models/Auditoria.php` para que el modelo real de auditorías quede en su ubicación correcta.

4. Se consolidó la versión interna del sistema con las correcciones ya trabajadas de producción:
   - perfil interno corregido;
   - seguridad base;
   - permisos internos;
   - centro de control operativo;
   - notificaciones internas;
   - reportes ejecutivos;
   - responsividad global;
   - respaldo de archivos privados;
   - protección contra dejar el sistema sin Admin activo.

5. Se preservó la parte de Portal Alumno desde el ZIP entregado:
   - `routes/portal_alumno.php`;
   - `app/Http/Controllers/PortalAlumno/`;
   - `app/Models/PortalAlumno/`;
   - `resources/views/portal_alumno/`;
   - migraciones propias de Portal Alumno.

6. Se corrigió `.env.example` para instalación local más estable:
   - `CACHE_STORE=file`;
   - `SESSION_DRIVER=file`;
   - `FILESYSTEM_DISK=local`.

7. Se removieron archivos que no deben entregarse en un ZIP limpio:
   - `.env` real;
   - `.git`;
   - `vendor`;
   - `node_modules`;
   - logs;
   - artefactos temporales.

## Validaciones realizadas en entorno de auditoría

- Lint PHP: 380 archivos revisados, 0 errores de sintaxis.
- Rutas Laravel: `php artisan route:list --no-ansi` validó 233 rutas.
- Frontend: `npm install` y `npm run build` ejecutaron correctamente con Node 22 en el entorno de auditoría.

## Notas

El ZIP queda preparado para instalarse con:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
npm run dev
```

Para entorno local se recomienda PHP 8.3.x, Composer 2.10, Node 22 LTS y MySQL 8.x.
