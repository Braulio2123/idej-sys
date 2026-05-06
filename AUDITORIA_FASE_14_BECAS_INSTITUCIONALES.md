# AUDITORÍA FASE LOCAL 14 — Becas institucionales

## Objetivo
Convertir el manejo de becas de IDEJ-SYS de un porcentaje simple dentro del alumno a un expediente financiero formal con vigencia, autorización, motivo, estatus y trazabilidad.

## Cambios implementados

### Nuevo módulo
- `Beca` como entidad institucional.
- Listado global de becas en Finanzas.
- Historial de becas por alumno.
- Registro de beca desde el expediente del alumno.
- Cancelación controlada de becas con motivo obligatorio.
- Sincronización de becas activas/programadas/vencidas.

### Nueva tabla `becas`
Campos principales:
- `alumno_id`
- `tipo`
- `porcentaje`
- `motivo`
- `observaciones`
- `fecha_inicio`
- `fecha_fin`
- `estatus`
- `autorizado_por_id`
- `registrado_por_id`
- `cancelado_por_id`
- `fecha_cancelacion`
- `motivo_cancelacion`

### Nuevos campos en `cargos`
- `beca_id`
- `beca_porcentaje_aplicado`
- `beca_monto_aplicado`

Esto permite que cada cargo conserve el descuento aplicado al momento de su creación, aunque la beca sea cancelada o vencida después.

## Reglas funcionales

1. Las becas ya no se capturan manualmente desde el formulario simple del alumno.
2. Las becas se registran desde el módulo institucional de becas.
3. Una beca vigente se aplica automáticamente a cargos becables.
4. Los conceptos no becables no reciben descuento aunque el alumno tenga beca.
5. Los cargos ya generados conservan su historial de descuento aplicado.
6. Cancelar una beca no reescribe cargos históricos.
7. Al registrar una beca con vigencia traslapada, se cancelan becas activas/programadas anteriores que se empalmen.
8. El campo `alumnos.beca_porcentaje` se mantiene solo como resumen compatible, sincronizado desde la beca vigente.

## Roles
Pueden gestionar becas:
- Administrador IDEJ
- Coordinación Administrativa IDEJ
- Finanzas IDEJ

Puede consultar Dirección desde dashboard/listados cuando corresponde.

## Archivos agregados
- `app/Models/Beca.php`
- `app/Http/Controllers/BecaController.php`
- `database/migrations/2026_01_08_204316_create_becas_table.php`
- `database/migrations/2026_01_08_204317_add_beca_fields_to_cargos_table.php`
- `resources/views/becas/index.blade.php`
- `resources/views/becas/create.blade.php`
- `resources/views/becas/cancelar.blade.php`
- `resources/views/alumnos/becas_index.blade.php`

## Archivos modificados
- `routes/web.php`
- `app/Models/Alumno.php`
- `app/Models/Cargo.php`
- `app/Models/Usuario.php`
- `app/Http/Controllers/AlumnoController.php`
- `app/Http/Controllers/CargoController.php`
- `app/Http/Controllers/CargoMasivoController.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/alumnos/show.blade.php`
- `resources/views/alumnos/create.blade.php`
- `resources/views/alumnos/edit.blade.php`
- `resources/views/alumnos/cargos_index.blade.php`
- `resources/views/cargos/create.blade.php`
- `resources/views/dashboard.blade.php`

## Pruebas recomendadas
1. Migrar base de datos con `php artisan migrate`.
2. Abrir expediente de un alumno.
3. Registrar beca activa del 25%.
4. Crear un cargo con concepto becable y verificar descuento.
5. Crear un cargo con concepto no becable y verificar que no descuente.
6. Registrar un cargo masivo con alumnos becados y no becados.
7. Cancelar una beca y verificar que cargos históricos no cambien.
8. Crear un nuevo cargo después de cancelar la beca y verificar que no aplique descuento.
9. Revisar dashboard y listado global de becas.

## Pendiente futuro
- Documento soporte de beca.
- Reporte PDF de becas por periodo.
- Flujo de autorización pendiente/aprobada.
- Aplicación retroactiva controlada para cargos pendientes, si Dirección lo solicita.
