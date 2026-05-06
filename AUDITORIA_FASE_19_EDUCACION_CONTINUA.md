# Fase Local 19 — Educación Continua / Cursos Especiales

## Objetivo
Separar los cursos especiales del calendario académico principal del IDEJ.

El calendario académico principal queda reservado para licenciaturas, maestrías, doctorados y posdoctorados. Este módulo nuevo se usa para cursos como MASC, oratoria, masterclass, talleres, diplomados, conferencias y capacitaciones que se miden principalmente por horas.

## Funciones implementadas

- Catálogo de cursos de educación continua.
- Sesiones por curso con fecha, horario, duración calculada, modalidad, aula/liga y equipo requerido.
- Inscripción de participantes desde tres orígenes:
  - Alumno existente.
  - Prospecto existente.
  - Participante externo.
- Control de asistencia por sesión.
- Cálculo de horas asistidas por participante.
- Porcentaje de avance con base en horas totales requeridas.
- Próximas sesiones de educación continua en dashboard.
- Registro de movimientos en bitácora.

## Nuevas tablas

- cursos_educacion_continua
- curso_sesiones
- curso_inscritos
- curso_asistencias

## Archivos principales agregados

- app/Models/CursoEducacionContinua.php
- app/Models/CursoSesion.php
- app/Models/CursoInscrito.php
- app/Models/CursoAsistencia.php
- app/Http/Controllers/CursoEducacionContinuaController.php
- resources/views/educacion_continua/index.blade.php
- resources/views/educacion_continua/create.blade.php
- resources/views/educacion_continua/edit.blade.php
- resources/views/educacion_continua/show.blade.php
- resources/views/educacion_continua/asistencia.blade.php

## Archivos modificados

- routes/web.php
- app/Http/Controllers/DashboardController.php
- app/Models/Alumno.php
- app/Models/Prospecto.php
- app/Models/Usuario.php
- app/Models/Docente.php
- app/Providers/AuthServiceProvider.php
- app/Traits/RegistraBitacora.php
- resources/views/layouts/app.blade.php
- resources/views/dashboard.blade.php

## Notas operativas

Este módulo no bloquea fechas del calendario académico principal. Es independiente, pero muestra próximas sesiones en dashboard para que Académica y Sistemas puedan preparar aulas, cámaras, micrófonos, Zoom, grabación o streaming.

