# Fase Local 20 — Solicitudes de pago docente

## Objetivo

Rediseñar el módulo de solicitudes de pago a docentes para que represente un flujo institucional real entre Coordinación Académica, Coordinación Administrativa y Finanzas.

## Flujo implementado

1. Coordinación Académica registra la solicitud.
2. La solicitud queda en estatus `Pendiente`.
3. Coordinación Administrativa o Finanzas revisa la solicitud.
4. Si faltan datos, la marca como `Observada` y regresa a Académica.
5. Académica corrige y la vuelve a dejar en revisión.
6. Coordinación Administrativa o Finanzas la marca como `Autorizada`.
7. Coordinación Administrativa o Finanzas registra el pago y queda como `Pagada`.
8. Si la solicitud no procede antes del pago, se puede marcar como `Cancelada` con motivo obligatorio.

## Campos agregados

- Folio institucional.
- Origen del servicio: Calendario académico, Educación continua o Manual.
- Relación opcional con calendario principal.
- Relación opcional con educación continua.
- Concepto de pago.
- Programa/grupo.
- Materia o actividad.
- Periodo.
- Modalidad.
- Número de sesiones.
- Horas totales.
- Tarifa por hora.
- Fecha límite sugerida de pago.
- Prioridad.
- Usuario que autoriza.
- Fecha de autorización.
- Método de pago.
- Referencia bancaria o comprobante.
- Banco/cuenta.
- Archivo comprobante.
- Observaciones académicas.
- Observaciones administrativas.
- Motivo de observación.
- Motivo de cancelación.

## Roles

- Admin: acceso total.
- Académica: crea y corrige solicitudes pendientes u observadas.
- Coordinación Administrativa: revisa, observa, autoriza y paga.
- Finanzas: revisa, observa, autoriza y paga.
- Dirección: consulta.

## Archivos modificados

- app/Models/SolicitudPagoDocente.php
- app/Http/Controllers/SolicitudPagoDocenteController.php
- database/migrations/2026_01_08_204330_add_workflow_fields_to_solicitudes_pago_docentes_table.php
- database/seeders/SolicitudPagoDocenteSeeder.php
- resources/views/solicitudes_pago/index.blade.php
- resources/views/solicitudes_pago/create.blade.php
- resources/views/solicitudes_pago/edit.blade.php
- resources/views/solicitudes_pago/_form.blade.php
- resources/views/solicitudes_pago/show.blade.php
- resources/views/solicitudes_pago/pagar.blade.php
- resources/views/solicitudes_pago/observar.blade.php
- resources/views/solicitudes_pago/cancelar.blade.php
- routes/web.php
- app/Http/Controllers/DashboardController.php
- resources/views/layouts/app.blade.php

## Validación técnica

Se validó sintaxis PHP con `php -l` en modelo, controlador, migración, seeder y rutas.

## Pendiente recomendado

Crear recibo/orden de pago PDF para docentes y, después, reportes de egresos por docente, periodo, programa y origen.
