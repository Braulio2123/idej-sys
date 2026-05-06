# Auditoría Fase 6 — Preparación para producción en Railway

## Decisión aplicada

La API ya no es prioridad funcional y se conserva solo como antecedente académico. Para producción, se deshabilitó la carga de rutas API desde `bootstrap/app.php`.

## Cambios realizados

- `bootstrap/app.php`: se eliminó la carga de `routes/api.php`.
- `routes/api.php`: se dejó como archivo documental sin rutas públicas.
- `routes/web.php`: se cambió la ruta `/` de closure a `Route::redirect` para permitir `route:cache`.
- `railway.json`: configuración de despliegue con Railpack, build de Vite, pre-deploy y healthcheck.
- `railway/init-app.sh`: script de preparación de Laravel para producción.
- `railway/run-cron.sh`: script opcional para scheduler.
- `railway/run-worker.sh`: script opcional para queue worker.
- `.env.railway.example`: plantilla de variables para Railway.
- `RAILWAY_DEPLOYMENT.md`: guía de despliegue.

## Riesgos reducidos

- Ya no quedan endpoints API públicos para usuarios, alumnos ni reportes.
- El despliegue ejecuta migraciones con `--force`.
- Se cachean configuración, rutas y vistas.
- Railway podrá revisar salud de la app mediante `/up`.

## Pendiente crítico antes de producción real

- Cambiar credenciales seed de prueba.
- Definir usuarios reales por área.
- Revisar persistencia de archivos subidos: Railway usa filesystem efímero; para comprobantes importantes conviene usar volumen o almacenamiento externo.
- Validar que el dominio final quede en `APP_URL`.
- Validar correo real si se usarán recordatorios.
