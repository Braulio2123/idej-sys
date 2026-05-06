# Fase Local 9 — Catálogo de requisitos documentales

## Objetivo
Convertir el expediente documental del alumno en un proceso institucional controlado mediante un catálogo de documentos requeridos por alcance:

- General para todos los alumnos.
- Por nivel académico.
- Por programa específico.

## Cambios realizados

### Nuevas tablas

- `requisitos_documentales`
- Nueva columna en `documentos_alumnos`: `requisito_documental_id`

### Nuevo módulo

- Catálogo de requisitos documentales.
- Alta, edición, desactivación/eliminación.
- Filtros por programa, nivel, estatus y búsqueda.
- Menú en Área Académica.

### Expediente documental

- Botón `Generar checklist` por alumno.
- Generación automática de documentos pendientes.
- No duplica documentos ya existentes.
- Relación de documentos del alumno con el requisito del catálogo.
- Soporte para documentos manuales sin requisito.

### Programas

- Se habilitó el campo `nivel` en validaciones y vistas.
- El catálogo puede aplicar por programa o por nivel.

## Archivos nuevos principales

- `app/Models/RequisitoDocumental.php`
- `app/Http/Controllers/RequisitoDocumentalController.php`
- `database/migrations/2026_01_08_204308_create_requisitos_documentales_table.php`
- `database/migrations/2026_01_08_204309_add_requisito_documental_id_to_documentos_alumnos_table.php`
- `database/seeders/RequisitoDocumentalSeeder.php`
- `resources/views/requisitos_documentales/index.blade.php`
- `resources/views/requisitos_documentales/create.blade.php`
- `resources/views/requisitos_documentales/edit.blade.php`
- `resources/views/requisitos_documentales/_form.blade.php`

## Archivos modificados principales

- `routes/web.php`
- `app/Http/Controllers/DocumentoAlumnoController.php`
- `app/Http/Controllers/AlumnoController.php`
- `app/Http/Controllers/ProgramaController.php`
- `app/Models/DocumentoAlumno.php`
- `app/Models/Programa.php`
- `app/Models/Alumno.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/ProgramaSeeder.php`
- `resources/views/alumnos/documentos_index.blade.php`
- `resources/views/alumnos/show.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/programas/index.blade.php`
- `resources/views/programas/_form.blade.php`

## Prueba recomendada

1. Ejecutar `php artisan optimize:clear`.
2. Ejecutar `php artisan migrate`.
3. Opcional, ejecutar `php artisan db:seed --class=RequisitoDocumentalSeeder`.
4. Entrar como Admin o Académica.
5. Abrir Área Académica → Requisitos Documentales.
6. Crear o revisar requisitos.
7. Abrir un alumno.
8. Presionar `Generar checklist`.
9. Revisar Expediente Documental.
10. Subir archivos, cambiar estatus y verificar que no se dupliquen requisitos.

## Nota operativa
Si los programas existentes no tienen nivel académico capturado, el sistema seguirá aplicando requisitos generales y requisitos por programa. Para usar requisitos por nivel, editar cada programa y llenar el campo `nivel` con valores como Licenciatura, Maestría o Doctorado.
