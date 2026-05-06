#!/usr/bin/env sh
set -e

echo "[IDEJ-SYS] Iniciando queue worker de Laravel..."

php artisan queue:work --sleep=3 --tries=3 --max-time=3600
