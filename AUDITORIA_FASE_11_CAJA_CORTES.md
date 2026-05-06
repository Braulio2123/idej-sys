# Auditoría Fase Local 11 — Caja y Cortes de Caja

## Objetivo

Convertir el registro de pagos en un proceso operativo de caja, evitando pagos sueltos sin control diario.

## Cambios realizados

- Se agregó la tabla `cortes_caja`.
- Se agregó `corte_caja_id` a la tabla `pagos`.
- Se creó el modelo `CorteCaja`.
- Se creó el controlador `CorteCajaController`.
- Se agregaron vistas para:
  - listado de cortes,
  - apertura de caja,
  - detalle de caja,
  - cierre de caja.
- Se modificó `PagoController` para exigir caja abierta antes de registrar pagos.
- Se vinculó cada pago nuevo con la caja abierta del usuario autenticado.
- Se agregaron indicadores de caja al dashboard.
- Se agregó acceso en menú lateral: Finanzas → Cortes de Caja.
- Se permitió operar caja a Admin, Recepción, Coordinación Administrativa y Finanzas.

## Flujo operativo nuevo

1. El usuario abre caja.
2. El usuario registra pagos de alumnos.
3. Cada pago queda ligado automáticamente a la caja abierta.
4. Al finalizar el turno/día, el usuario cierra caja.
5. El sistema calcula:
   - efectivo cobrado,
   - transferencias,
   - tarjeta,
   - total de ingresos,
   - cantidad de pagos,
   - diferencias entre sistema y monto reportado.

## Regla importante

Si el usuario no tiene una caja abierta, no podrá registrar pagos.

## Próximos pendientes recomendados

- Agregar cancelación formal de pagos con motivo y usuario autorizador.
- Crear recibo PDF de pago.
- Crear reporte PDF de corte de caja.
- Agregar validación administrativa de transferencias.
- Agregar corte consolidado del día para Dirección/Finanzas.
