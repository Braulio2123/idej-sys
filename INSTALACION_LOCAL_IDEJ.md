# Instalación local de IDEJ-SYS en Laragon

## Versiones recomendadas

- PHP 8.3.x
- Composer 2.10.x
- Node.js 22 LTS
- npm incluido con Node 22
- MySQL 8.x
- Git 2.40+

## Extensiones PHP necesarias

Asegúrate de tener activas en `php.ini`:

```ini
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=zip
```

Valida con:

```bash
php -m | findstr /I "openssl fileinfo pdo_mysql curl zip gd intl mbstring"
```

## Instalación

Desde la carpeta del proyecto:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Edita `.env` y confirma:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

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

Crea la base de datos en MySQL/Laragon:

```txt
idej_sys
```

Luego ejecuta:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan route:list
```

## Levantar el sistema

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Abrir:

```txt
http://127.0.0.1:8000
```

Usuario demo:

```txt
admin@idej.test
admin123
```

## Comandos de verificación

```bash
php artisan optimize:clear
php artisan route:list
php artisan about
npm run build
```

## Importante

No ejecutes `composer update` salvo que quieras actualizar dependencias de forma deliberada. Para instalar el proyecto, usa `composer install`.
