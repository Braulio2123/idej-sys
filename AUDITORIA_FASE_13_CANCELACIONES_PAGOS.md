# Auditoría Fase Local 13 — Cancelaciones y reversos de pagos

## Objetivo

Implementar un flujo institucional para cancelar pagos sin eliminarlos físicamente, manteniendo trazabilidad financiera y auditoría.

## Decisión funcional

Los pagos ya no deben corregirse editando o eliminando registros. A partir de esta fase, un pago puede tener dos estados:

- Activo
- Cancelado

Un pago cancelado permanece en la base de datos, conserva su recibo, conserva sus relaciones con cargos/parcialidades y queda marcado con usuario, fecha y motivo de cancelación.

## Cambios principales

### Migración nueva

Se agregó:

- `database/migrations/2026_01_08_204315_add_cancelacion_fields_to_pagos_table.php`

Campos agregados a `pagos`:

- `estatus`
- `saldo_a_favor_generado`
- `cancelado_por_id`
- `fecha_cancelacion`
- `motivo_cancelacion`

### Modelo Pago

Se agregaron:

- Relación `canceladoPor()`
- Scope `activos()`
- Scope `cancelados()`
- Método `estaCancelado()`
- Método `estaActivo()`

### Controlador PagoController

Se agregaron los métodos:

- `confirmarCancelacion()`
- `cancelar()`

La cancelación realiza:

1. Validación de pertenencia del pago al alumno.
2. Validación de que el pago no esté cancelado previamente.
3. Validación de caja asociada.
4. Bloqueo si la caja ya está cerrada.
5. Reverso de montos aplicados a cargos.
6. Reverso de montos aplicados a parcialidades de convenio.
7. Descuento de saldo a favor generado.
8. Recalculo de convenios.
9. Recalculo del estado financiero del alumno.
10. Recalculo de totales del corte de caja.
11. Registro en bitácora.

## Regla de caja

Por ahora, solo se permite cancelar pagos que pertenecen a una caja abierta.

Si la caja ya está cerrada, el sistema bloquea la cancelación directa. Para cajas cerradas debe implementarse después un flujo de ajuste administrativo, para no alterar cortes históricos ya entregados.

## Recibos PDF

El recibo sigue disponible después de cancelar el pago, pero ahora se muestra con leyenda de cancelación:

- RECIBO CANCELADO
- Usuario que canceló
- Fecha de cancelación
- Motivo de cancelación

## Reportes y cortes

Los totales financieros de dashboard, reportes y cortes de caja ahora consideran únicamente pagos activos.

Los pagos cancelados siguen visibles en listados como evidencia histórica, pero su monto aparece tachado y no suma en totales operativos.

## Vistas actualizadas

Se actualizaron:

- `resources/views/alumnos/show.blade.php`
- `resources/views/alumnos/pagos_index.blade.php`
- `resources/views/cortes_caja/show.blade.php`
- `resources/views/pagos/recibo_pdf.blade.php`

Se agregó:

- `resources/views/pagos/cancelar.blade.php`

## Rutas nuevas

Se agregaron rutas protegidas para Admin, Coordinación Administrativa y Finanzas:

- `alumnos.pagos.cancelar.confirmar`
- `alumnos.pagos.cancelar`

Recepción puede registrar pagos, pero no cancelarlos directamente.

## Validación técnica

Se validó sintaxis PHP en:

- `app/`
- `routes/`
- `database/`

Resultado: sin errores de sintaxis.

## Pendiente recomendado

La siguiente mejora financiera debe ser un módulo de **ajustes administrativos para cajas cerradas**, donde una cancelación posterior no modifique el corte original, sino que genere un movimiento compensatorio en una caja o periodo posterior.
