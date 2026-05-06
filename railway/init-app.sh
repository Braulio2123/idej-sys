#!/usr/bin/env sh
set -e

echo "[IDEJ-SYS] Preparando despliegue Laravel..."

php artisan optimize:clear
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[IDEJ-SYS] Despliegue preparado correctamente."
