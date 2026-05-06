# Auditoría Fase Local 15 — Ajustes administrativos para cajas cerradas

## Objetivo

Permitir correcciones posteriores al cierre de caja sin alterar el corte histórico original. Esta fase cubre el caso operativo en el que un pago fue capturado incorrectamente, pero la caja ya fue cerrada y entregada.

## Cambio funcional principal

Antes:

- Un pago solo podía cancelarse si su caja estaba abierta.
- Si la caja ya estaba cerrada, el sistema bloqueaba la cancelación y no existía un flujo posterior.

Ahora:

- Los pagos de cajas abiertas se cancelan con el flujo normal.
- Los pagos de cajas cerradas se cancelan mediante un ajuste administrativo.
- El pago se marca como cancelado.
- El adeudo del alumno se revierte.
- El recibo PDF continúa disponible como cancelado.
- El corte cerrado conserva sus importes originales.
- Se crea un registro en `ajustes_caja` con monto negativo para documentar la corrección.

## Archivos agregados

- `app/Models/AjusteCaja.php`
- `database/migrations/2026_01_08_204318_create_ajustes_caja_table.php`
- `resources/views/pagos/ajuste_cancelacion.blade.php`
- `AUDITORIA_FASE_15_AJUSTES_CAJA_CERRADA.md`

## Archivos modificados

- `app/Http/Controllers/PagoController.php`
- `app/Http/Controllers/CorteCajaController.php`
- `app/Models/CorteCaja.php`
- `app/Models/Pago.php`
- `app/Models/Alumno.php`
- `app/Models/Usuario.php`
- `app/Models/Cargo.php`
- `routes/web.php`
- `resources/views/alumnos/show.blade.php`
- `resources/views/alumnos/pagos_index.blade.php`
- `resources/views/cortes_caja/show.blade.php`
- `resources/views/pagos/cancelar.blade.php`

## Tabla nueva

`ajustes_caja` guarda:

- corte de caja afectado,
- pago relacionado,
- alumno relacionado,
- usuario que aplica el ajuste,
- tipo de ajuste,
- método de pago,
- monto de ajuste,
- motivo,
- observaciones,
- fecha de aplicación.

## Regla contable aplicada

El corte cerrado no se recalcula ni se sobrescribe. Si un pago de una caja cerrada se cancela, se genera un movimiento negativo en ajustes.

Ejemplo:

- Corte cerrado original: $1,500.00
- Pago cancelado posteriormente: $1,500.00
- Ajuste registrado: -$1,500.00
- Neto ajustado mostrado: $0.00

## Seguridad

Solo pueden aplicar ajustes:

- Administrador IDEJ
- Coordinación Administrativa IDEJ
- Finanzas IDEJ

Recepción puede registrar pagos, pero no cancelar pagos ni aplicar ajustes sobre cajas cerradas.

## Validaciones

El sistema valida:

- que el pago pertenezca al alumno,
- que el pago no esté ya cancelado,
- que tenga corte de caja asociado,
- que la caja esté cerrada,
- que exista motivo de ajuste,
- que el saldo a favor generado por el pago no haya sido usado antes de revertirlo.

## Observación técnica

Se corrigió además el `$fillable` de `app/Models/Cargo.php` para permitir guardar correctamente `beca_porcentaje_aplicado` y `beca_monto_aplicado` en cargos nuevos.
