# Portal Alumno PWA - Christian

Este paquete agrega el modulo **Portal Alumno PWA** manteniendolo separado del sistema administrativo existente.

## URL del modulo

```txt
/portal-alumno/login
/portal-alumno
/portal-alumno/horario
/portal-alumno/materias
/portal-alumno/calificaciones
/portal-alumno/avisos
/portal-alumno/ubicacion
/portal-alumno/perfil
```

## Regla de separacion aplicada

El codigo nuevo del alumno vive en carpetas propias:

```txt
app/Http/Controllers/PortalAlumno/
app/Http/Middleware/PortalAlumno/
app/Models/PortalAlumno/
resources/views/portal_alumno/
routes/portal_alumno.php
public/portal-alumno/
```

No se modifica el CRUD administrativo de alumnos:

```txt
app/Http/Controllers/AlumnoController.php
app/Models/Alumno.php
resources/views/alumnos/
```

## Archivos globales modificados

Solo hay dos integraciones globales necesarias:

### 1. config/auth.php

Se agrego el guard `portal_alumno` y el provider `portal_alumnos` con comentarios claros.

### 2. routes/web.php

Se agrego unicamente esta carga documentada:

```php
require __DIR__.'/portal_alumno.php';
```

No se mezclaron rutas del portal dentro del bloque administrativo.

## Instalacion

Desde la raiz del proyecto Laravel:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=PortalAlumnoAvisoSeeder
```

El seeder de avisos es opcional, pero recomendado para ver contenido inicial.

## Acceso de prueba local

La migracion agrega acceso temporal a alumnos existentes:

```txt
Matricula: A001
Contrasena: alumno123
```

Tambien aplica para otros alumnos existentes, siempre que existan en la base de datos.

## Importante para produccion

La contrasena `alumno123` es temporal para pruebas. Antes de liberar, debe implementarse una pantalla o proceso para asignar/restablecer contrasenas reales por alumno.

## Validacion realizada

Se reviso sintaxis PHP con `php -l` en los archivos agregados y modificados. En el entorno de analisis no se pudo ejecutar `php artisan route:list` porque el `vendor` extraido del ZIP estaba incompleto, pero los archivos PHP no presentan errores de sintaxis.
