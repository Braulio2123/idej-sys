#!/usr/bin/env sh
set -e

echo "[IDEJ-SYS] Iniciando scheduler de Laravel..."

while true; do
  php artisan schedule:run --no-interaction
  sleep 60
done
