# Instrucciones rápidas para clonar y levantar este proyecto Laravel

> Resumen corto (como si lo escribiera yo): clona el repositorio, instala dependencias PHP y JS, configura `.env`, genera la key, configura la BD, corre migraciones y seeders, crea el enlace a `storage`, compila assets y arranca el servidor

---

## Requisitos

* PHP **>= 8.0** (ver `composer.json` del proyecto)
* Composer
* Node.js y npm (o pnpm/yarn si prefieres)
* MySQL / MariaDB / PostgreSQL (según tu `.env`)
* Git

---

## Pasos (rápido y directo)

1. Clonar el repo

```bash
git clone <URL_DEL_REPOSITORIO>
cd <NOMBRE_DEL_REPOSITORIO>
```

2. Instalar dependencias PHP

```bash
composer install --no-interaction --prefer-dist
```

3. Instalar dependencias JS

```bash
npm install
# o si usas pnpm: pnpm install
# o si usas yarn: yarn
```

4. Copiar el archivo de entorno y configurar variables

```bash
cp .env.example .env
```

* Edita `.env` y configura al menos:

  * `APP_NAME` (opcional)
  * `APP_URL` (ej. [http://localhost:8000](http://localhost:8000))
  * `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  * `MAIL_*` si usarás envío de correos

5. Generar la clave de aplicación

```bash
php artisan key:generate
```

6. Crear la base de datos (si no existe)

* Desde tu cliente (MySQL/MariaDB):

```sql
CREATE DATABASE nombre_de_la_bd;
```

* Asegúrate que `.env` tenga `DB_DATABASE=nombre_de_la_bd` y credenciales correctas

7. Migraciones y seeders

```bash
php artisan migrate --seed
```

* Si prefieres limpiar y volver a crear todo (dev):

```bash
php artisan migrate:fresh --seed
```

8. Enlace a storage (si el proyecto usa archivos públicos)

```bash
php artisan storage:link
```

9. Compilar assets front-end

```bash
npm run dev    # para desarrollo
npm run build  # para producción
# o: npm run prod
```

10. Arrancar servidor local

```bash
php artisan serve --host=0.0.0.0 --port=8000
# luego abre http://localhost:8000
```

11. Tareas útiles (opcionales)

* Colas:

```bash
php artisan queue:work
# o supervisa con: php artisan queue:listen
```

* Ejecutar seeders individuales:

```bash
php artisan db:seed --class=NombreDelSeeder
```

* Ejecutar tests:

```bash
php artisan test
# o: vendor/bin/phpunit
```

* Limpiar cachés si algo raro pasa:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:clear
php artisan cache:clear
```

---

## Problemas comunes y soluciones rápidas

* **Error de permisos (storage/logs, vendor, node_modules)**: dar permisos al usuario web o usar `chmod -R 775 storage bootstrap/cache`
* **`.env` no se carga**: verifica que el archivo se llama exactamente `.env` y que no hay un `.env.example` en uso
* **Migraciones fallan por versión de MySQL**: revisa `engine` y tipos en las migraciones o usa `mariadb`/`mysql` compatible
* **Dependencias de Composer tardan o fallan**: actualiza Composer a la última versión

---

## Nota sobre despliegue (breve)

Para producción además necesitarás:

* Configurar un servicio web (Nginx/Apache) apuntando a `public/`
* SSL (Let's Encrypt)
* Variables de entorno seguras
* Optimizar config/routes/views: `php artisan optimize` o `php artisan config:cache` `route:cache`
* Supervisar colas (Supervisor) y tareas programadas (cron)

---

## Fuentes / lecturas recomendadas

* [https://laravel.com/docs](https://laravel.com/docs)
* [https://laravel.com/docs/installation](https://laravel.com/docs/installation)
* [https://getcomposer.org/doc/](https://getcomposer.org/doc/)
* [https://nodejs.org/](https://nodejs.org/)

---

Si quieres, adapto esto directo al README original (añado sección `Installation` arriba) o lo dejo como guía separada

---

## Despliegue en Railway

La guía específica de producción está en:

```txt
RAILWAY_DEPLOYMENT.md
```

Esta versión está enfocada al uso web. La API pública fue deshabilitada para evitar exponer CRUD de usuarios, alumnos y reportes en producción.
