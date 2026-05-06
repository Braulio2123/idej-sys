# Auditoría Fase 4 — Convenios

## Problema detectado

El módulo de convenios permitía seleccionar varios cargos para crear un convenio, pero el registro del convenio solo conservaba un cargo en `cargo_original_id`. Esto generaba inconsistencias:

- Al eliminar un convenio, solo se reactivaba un cargo.
- No había historial formal de todos los cargos incluidos.
- No era posible auditar con precisión qué adeudos originaron el convenio.
- La edición del convenio permitía cambiar total y número de parcialidades sin regenerar parcialidades, lo que podía dejar montos descuadrados.
- El cálculo de parcialidades podía dejar centavos fuera por redondeo.

## Cambios realizados

### Base de datos

- Se agregó la migración `2026_01_08_204259_create_cargo_convenio_table.php`.
- Se creó la tabla pivote `cargo_convenio` con:
  - `cargo_id`
  - `convenio_id`
  - `monto_original`
  - `monto_adeudo_original`
  - `estatus_original`
- Se agregó restricción única sobre `cargo_id` para impedir que un mismo cargo quede ligado a más de un convenio activo/histórico al mismo tiempo.
- Se agregó el estatus `En Convenio` al enum de `cargos.estatus`.

### Modelos

- `Convenio` ahora tiene relación formal `cargos()` mediante tabla pivote.
- `Cargo` ahora tiene relación `convenios()`.
- Se mantiene `cargoOriginal()` por compatibilidad con datos y vistas anteriores.

### Controlador de Convenios

- Se valida que todos los cargos seleccionados pertenezcan al alumno.
- Se valida que los cargos estén pendientes o parcialmente pagados.
- Se usa `lockForUpdate()` durante la creación para evitar cambios simultáneos en los cargos.
- Al crear un convenio:
  - Se registra cada cargo en `cargo_convenio`.
  - Se conserva el monto original, adeudo original y estatus anterior.
  - Los cargos pasan a estatus `En Convenio`.
  - Las parcialidades se calculan con ajuste de redondeo en la última parcialidad.
- Al eliminar un convenio sin pagos:
  - Se reactivan todos los cargos relacionados, no solo el primero.
  - Se restaura el adeudo original de cada cargo.
  - Se recalcula el estado financiero del alumno.
- La edición del convenio ahora solo permite modificar la descripción.

### Vistas

- La vista de creación muestra el total seleccionado en tiempo real.
- La vista de detalle muestra la tabla de cargos incluidos en el convenio.
- La vista de edición muestra los datos financieros como solo lectura y permite editar únicamente la descripción.

### Rutas

- Se eliminó `index` del resource nested `alumnos.convenios` para evitar conflicto con la ruta personalizada `AlumnoController@conveniosIndex`.

## Pruebas realizadas

- Se validó sintaxis PHP con `php -l` en controladores, modelos, migraciones y rutas.
- No se ejecutó `php artisan test` porque el paquete limpio no incluye `vendor`.

## Comando recomendado para probar localmente

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
```

Después correr:

```bash
php artisan serve
npm run dev
```

## Flujo recomendado de prueba

1. Entrar con un usuario administrador o recepción.
2. Abrir un alumno con adeudos.
3. Crear dos o más cargos pendientes.
4. Crear un convenio seleccionando varios cargos.
5. Confirmar que los cargos pasen a estatus `En Convenio`.
6. Confirmar que el detalle del convenio muestre todos los cargos incluidos.
7. Confirmar que las parcialidades sumen exactamente el total reestructurado.
8. Eliminar el convenio antes de registrar pagos.
9. Confirmar que todos los cargos vuelvan a su estatus y adeudo anterior.
10. Crear otro convenio, registrar un pago parcial y confirmar que ya no permita eliminarlo.
