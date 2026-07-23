# CAMBIOS FASE 35 — MANIFEST

## Resumen

Fase de cobranza automática limitada a correo electrónico y generación de cargos recurrentes.

## Migraciones agregadas

- database/migrations/2026_07_07_000008_create_planes_cargos_recurrentes_table.php
- database/migrations/2026_07_07_000009_create_cargo_recurrente_ejecuciones_table.php
- database/migrations/2026_07_07_000010_add_recurring_fields_to_cargos_table.php

## Archivos agregados

- app/Console/Commands/ActivarRecordatoriosEmail.php
- app/Console/Commands/GenerarCargosRecurrentes.php
- app/Http/Controllers/CobranzaEmailController.php
- app/Http/Controllers/PlanCargoRecurrenteController.php
- app/Models/CargoRecurrenteEjecucion.php
- app/Models/PlanCargoRecurrente.php
- app/Services/CargosRecurrentesService.php
- app/Services/RecordatorioPagoEmailService.php
- database/seeders/PlanCargoRecurrenteSeeder.php
- resources/views/cargos/recurrentes/_form.blade.php
- resources/views/cargos/recurrentes/create.blade.php
- resources/views/cargos/recurrentes/edit.blade.php
- resources/views/cargos/recurrentes/index.blade.php
- resources/views/cobranza/correos/index.blade.php
- AUDITORIA_CORRECCIONES_FASE35.md
- CAMBIOS_FASE35_MANIFEST.md

## Archivos modificados

- .env.example
- bootstrap/app.php
- config/idej_recordatorios.php
- routes/console.php
- routes/web.php
- app/Console/Commands/EnviarRecordatoriosPago.php
- app/Http/Controllers/UsuarioController.php
- app/Mail/RecordatorioPago.php
- app/Models/Cargo.php
- app/Models/ConfiguracionInstitucional.php
- database/seeders/DatabaseSeeder.php
- resources/views/cargos/masivo/index.blade.php
- resources/views/emails/recordatorio-pago.blade.php
- resources/views/layouts/app.blade.php
- resources/views/usuarios/create.blade.php
- resources/views/usuarios/edit.blade.php

## Archivos retirados del flujo

- app/Services/CanalNotificacionExternaService.php

Portal Alumno no fue modificado.
