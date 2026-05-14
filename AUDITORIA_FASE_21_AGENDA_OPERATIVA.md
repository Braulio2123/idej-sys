# Fase Local 21 — Agenda operativa para Académica, Sistemas y Recepción

## Objetivo

Unificar en una sola vista las próximas actividades académicas del IDEJ:

1. Calendarios académicos principales: licenciaturas, maestrías, doctorados y posdoctorados.
2. Educación continua: MASC, oratoria, masterclass, talleres, diplomados y cursos especiales.

La vista está pensada para Coordinación Académica, Sistemas, Recepción, Dirección y Coordinación Administrativa.

## Funcionalidad agregada

- Nuevo controlador `AgendaOperativaController`.
- Nueva ruta `agenda-operativa.index`.
- Nueva vista `resources/views/agenda_operativa/index.blade.php`.
- Nuevo acceso en el sidebar: `Agenda Operativa`.
- Filtros por rango, tipo de actividad, modalidad, búsqueda y actividades que requieren equipo.
- Agrupación de eventos por fecha.
- Resumen de equipo requerido para Sistemas.
- Enlaces directos al calendario académico o curso de educación continua correspondiente.

## Reglas operativas

- Las sesiones canceladas o suspendidas de calendarios principales no se muestran.
- Las sesiones canceladas de educación continua no se muestran.
- Los calendarios/cursos finalizados o cancelados no se consideran como operativos.
- Las clases principales con modalidad virtual o mixta sugieren preparación técnica básica: Zoom, cámara y micrófono.
- Educación continua usa el equipo requerido capturado en el curso y/o sesión.

## Archivos agregados

- `app/Http/Controllers/AgendaOperativaController.php`
- `resources/views/agenda_operativa/index.blade.php`

## Archivos modificados

- `routes/web.php`
- `resources/views/layouts/app.blade.php`

## Pruebas sugeridas

1. Crear un calendario académico con sesiones próximas.
2. Crear un curso de educación continua con sesiones próximas.
3. Entrar a Agenda Operativa.
4. Filtrar por hoy, semana y próximos 15 días.
5. Filtrar por clases principales.
6. Filtrar por educación continua.
7. Filtrar por modalidad virtual o mixta.
8. Activar “solo actividades que requieren preparación técnica”.
9. Verificar que el enlace “Abrir detalle” lleve al calendario o curso correcto.
