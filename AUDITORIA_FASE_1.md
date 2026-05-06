# IDEJ-SYS — Saneamiento Fase 1

Esta versión fue preparada como paquete limpio de desarrollo para Laragon/Laravel.

## Cambios aplicados

- Se eliminó el archivo `.env` real del paquete.
- Se corrigió `.env example` a `.env.example`.
- Se limpió `.env.example` para no incluir `APP_KEY` real ni credenciales SMTP.
- Se eliminó `.git/` del entregable.
- Se eliminó `node_modules/` del entregable.
- Se excluyeron artefactos generados:
  - `public/build/`
  - `storage/logs/*.log`
  - `storage/framework/views/*.php`
  - `database/database.sqlite`
  - `database/migrations.rar`
  - `estructura.txt`
  - `IDEJSys`
- Se actualizó `.gitignore` para evitar volver a subir archivos de entorno, logs, builds, dependencias y temporales.
- Se corrigió `resources/views/layouts/app.blade.php`:
  - Se quitó Alpine por CDN porque ya se carga desde Vite en `resources/js/app.js`.
  - Se agregó `@stack('styles')`.
  - Se agregó `@stack('scripts')`, necesario para vistas como pagos.
- Se quitó el script `postinstall` de `package.json` para que `npm install` no ejecute `npm run build` automáticamente.

## Instalación recomendada en Laragon

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

Si usas dominio de Laragon, configura en `.env`:

```env
APP_URL=http://idej-sys.test
DB_DATABASE=idej_sys
DB_USERNAME=root
DB_PASSWORD=
```

## Siguiente fase recomendada

Fase 2: corregir autenticación y modelo de usuario.

Pendiente crítico:

- Unificar todo el sistema para usar `App\Models\Usuario`.
- Eliminar o adaptar referencias a `App\Models\User`.
- Desactivar registro público si el sistema será interno.
- Corregir recuperación de contraseña y perfil para que coincidan con la tabla `usuarios`.
