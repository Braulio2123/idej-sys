# Cambios globales realizados para integrar Portal Alumno PWA

Este documento existe para que el equipo identifique rapidamente que partes globales fueron tocadas.

## config/auth.php

Se agrego un guard independiente:

```php
'portal_alumno' => [
    'driver' => 'session',
    'provider' => 'portal_alumnos',
],
```

Y un provider independiente:

```php
'portal_alumnos' => [
    'driver' => 'eloquent',
    'model' => App\Models\PortalAlumno\AlumnoPortal::class,
],
```

Esto evita usar el guard administrativo `web` para alumnos.

## routes/web.php

Solo se agrego la carga del archivo separado:

```php
require __DIR__.'/portal_alumno.php';
```

Las rutas reales del alumno estan en:

```txt
routes/portal_alumno.php
```

## bootstrap/app.php

No se modifico.

## Modulos administrativos

No se modificaron:

```txt
app/Http/Controllers/AlumnoController.php
app/Models/Alumno.php
resources/views/alumnos/
```
