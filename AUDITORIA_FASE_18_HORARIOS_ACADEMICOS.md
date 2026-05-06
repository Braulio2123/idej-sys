# Auditoría Fase Local 18 — Horarios académicos / asignación docente-grupo

## Objetivo
Convertir el área académica en un módulo más operativo agregando un flujo formal de materias y horarios, para que Coordinación Académica pueda controlar qué docente imparte qué materia, a qué grupo, en qué día, hora, aula y modalidad.

## Módulos agregados

### Materias
Se agregó un catálogo académico con los campos:

- programa asociado opcional,
- clave,
- nombre,
- nivel,
- semestre/cuatrimestre,
- créditos,
- horas teóricas,
- horas prácticas,
- estatus,
- descripción.

### Horarios académicos
Se agregó una tabla de asignaciones con:

- grupo,
- materia,
- docente,
- día de la semana,
- hora inicio,
- hora fin,
- aula,
- modalidad,
- vigencia,
- estatus,
- observaciones.

## Reglas implementadas

1. La hora de fin debe ser posterior a la hora de inicio.
2. Un grupo no puede tener dos clases activas traslapadas el mismo día.
3. Un docente no puede tener dos clases activas traslapadas el mismo día.
4. Un aula no puede tener dos clases activas traslapadas el mismo día.
5. Los horarios suspendidos o finalizados no bloquean nuevos horarios.
6. Una materia no se puede eliminar si ya tiene horarios relacionados.

## Archivos agregados

- `app/Models/Materia.php`
- `app/Models/HorarioAcademico.php`
- `app/Http/Controllers/MateriaController.php`
- `app/Http/Controllers/HorarioAcademicoController.php`
- `database/migrations/2026_01_08_204320_create_materias_table.php`
- `database/migrations/2026_01_08_204321_create_horarios_academicos_table.php`
- `database/seeders/MateriaSeeder.php`
- `database/seeders/HorarioAcademicoSeeder.php`
- `resources/views/materias/index.blade.php`
- `resources/views/materias/create.blade.php`
- `resources/views/materias/edit.blade.php`
- `resources/views/materias/_form.blade.php`
- `resources/views/horarios_academicos/index.blade.php`
- `resources/views/horarios_academicos/create.blade.php`
- `resources/views/horarios_academicos/edit.blade.php`
- `resources/views/horarios_academicos/show.blade.php`
- `resources/views/horarios_academicos/_form.blade.php`

## Archivos modificados

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/grupos/index.blade.php`
- `resources/views/grupos/show.blade.php`
- `resources/views/docentes/show.blade.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/GrupoController.php`
- `app/Http/Controllers/DocenteController.php`
- `app/Models/Grupo.php`
- `app/Models/Programa.php`
- `app/Models/Docente.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Traits/RegistraBitacora.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/DocenteSeeder.php`

## Menú agregado

En `Área Académica` se agregaron:

- Materias
- Horarios

## Dashboard
Se agregaron indicadores académicos:

- grupos registrados,
- materias activas,
- horarios activos,
- clases del día,
- agenda académica de hoy.

## Validación técnica
Se validó sintaxis PHP en controladores, modelos, rutas, migraciones y seeders relacionados.

## Próximos pasos recomendados

1. Crear módulo de asistencias por clase/sesión.
2. Crear calendario semanal visual por grupo y docente.
3. Permitir exportar horario en PDF.
4. Vincular horarios con solicitudes de pago docente.
5. Crear incidencias académicas por alumno o por grupo.
