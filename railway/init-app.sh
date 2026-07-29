#!/usr/bin/env sh
set -eu

umask 027

echo "[IDEJ-SYS] Preparando despliegue Laravel..."

php artisan optimize:clear
php artisan migrate --force --no-interaction
php artisan idej:validar-produccion --strict --no-interaction
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[IDEJ-SYS] Despliegue preparado correctamente."
