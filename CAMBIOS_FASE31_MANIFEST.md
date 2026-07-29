# Manifest de cambios Fase 31

## Archivos modificados

- config/app.php
- routes/web.php
- app/Http/Requests/Auth/LoginRequest.php
- app/Http/Controllers/Auth/PasswordResetLinkController.php
- app/Http/Controllers/Auth/NewPasswordController.php
- app/Http/Controllers/DocumentoAlumnoController.php
- app/Http/Controllers/CargoController.php
- app/Http/Controllers/ConvenioController.php
- app/Http/Controllers/CorteCajaController.php
- app/Http/Controllers/SolicitudPagoDocenteController.php
- app/Models/CorteCaja.php
- resources/views/auth/login.blade.php
- resources/views/auth/forgot-password.blade.php
- resources/views/auth/reset-password.blade.php
- resources/views/alumnos/documentos_index.blade.php
- resources/views/alumnos/edit.blade.php
- resources/views/alumnos/show.blade.php
- resources/views/cortes_caja/show.blade.php
- resources/views/cortes_caja/cierre.blade.php
- resources/views/solicitudes_pago/_form.blade.php
- resources/views/solicitudes_pago/pagar.blade.php
- resources/views/solicitudes_pago/show.blade.php

## Archivos nuevos

- lang/es/auth.php
- lang/es/passwords.php
- lang/es/validation.php
- app/Models/MovimientoCaja.php
- database/migrations/2026_07_07_000001_create_movimientos_caja_table.php
- resources/views/cortes_caja/pdf.blade.php
- resources/views/solicitudes_pago/acuse_pdf.blade.php
- AUDITORIA_CORRECCIONES_FASE31.md
- CAMBIOS_FASE31_MANIFEST.md

## Portal Alumno

No modificado.
