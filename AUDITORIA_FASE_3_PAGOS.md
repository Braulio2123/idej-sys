# IDEJ-SYS — Fase 3: Corrección del módulo de pagos

## Objetivo

Alinear migración, modelo, controlador y vistas del módulo de pagos para que el registro de pagos sea consistente y no dependa de campos inexistentes en base de datos.

## Cambios realizados

### Base de datos

Se actualizó la migración `2026_01_08_204256_create_pagos_table.php` para incluir campos reales del formulario:

- `folio_recibo`
- `referencia_bancaria`
- `archivo_comprobante`
- `banco_emisor`
- `cuenta_origen`
- `numero_autorizacion`
- `clave_rastreo`
- `concepto_transferencia`
- `fecha_transferencia`
- `banco_destino`
- `observaciones`

Con esto se elimina la inconsistencia anterior donde el controlador intentaba guardar `boucher`, `referencia` y `archivo_comprobante`, pero la tabla solo tenía `referencia_bancaria`.

### Modelo Pago

Se actualizó `app/Models/Pago.php`:

- Nuevos campos en `$fillable`.
- Cast de `fecha_transferencia` a datetime.
- Relación con parcialidades de convenio.
- Accessor `referencia_principal` para mostrar folio/referencia/clave de rastreo/autorización según disponibilidad.

### Controlador PagoController

Se reescribió el flujo principal de registro de pagos:

- Valida método de pago, monto, fecha, folio, comprobantes y datos bancarios.
- Valida que los cargos seleccionados pertenezcan al alumno de la ruta.
- Valida que las parcialidades seleccionadas pertenezcan al alumno de la ruta.
- Evita aplicar pagos a cargos/parcialidades de otros alumnos manipulando IDs desde el formulario.
- Aplica pagos en orden de vencimiento.
- Actualiza adeudos y estatus de cargos.
- Actualiza adeudos y estatus de parcialidades.
- Si sobra dinero, lo registra como `saldo_a_favor` del alumno.
- Recalcula el estatus financiero del alumno.
- Finaliza convenios cuando todas sus parcialidades quedan pagadas.
- Guarda comprobantes en `storage/app/public/comprobantes/pagos`.

### Vista de registro de pago

Se actualizó `resources/views/pagos/create.blade.php`:

- Formulario más claro y dividido por secciones.
- Campos específicos para efectivo, transferencia y tarjeta.
- Se corrigieron nombres duplicados de campos.
- Se agregó resumen de adeudo seleccionado.
- Se mantiene soporte de archivos PDF/JPG/PNG.
- Se respeta el saldo a favor actual del alumno.

### Vistas de pagos del alumno

Se actualizaron:

- `resources/views/alumnos/show.blade.php`
- `resources/views/alumnos/pagos_index.blade.php`

Ahora muestran:

- Folio de recibo.
- Referencia principal.
- Comprobante enlazado si existe.
- Método de pago.
- Usuario que registró el pago.

### Modelos relacionados

Se actualizó `Alumno` para incluir campos que ya existían en migración pero no en `$fillable`:

- `apellido_paterno`
- `apellido_materno`
- `ciclo_escolar_id`
- `saldo_a_favor`

Se actualizó `Cargo` para incluir:

- `moratorio_aplicado`

Se agregó relación de pagos en `ParcialidadConvenio`.

## Validación realizada

Se validó sintaxis PHP en controladores, modelos, migraciones, seeders y rutas con `php -l`.

## Cómo probar

Como se modificó una migración existente, en ambiente local de desarrollo se recomienda ejecutar:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev
php artisan serve
```

Luego probar:

1. Entrar con usuario administrador o recepción.
2. Abrir un alumno con cargos pendientes.
3. Registrar un pago seleccionando cargos.
4. Probar pago por efectivo.
5. Probar pago por transferencia con comprobante.
6. Probar pago por tarjeta con comprobante.
7. Confirmar que el pago aparezca en la ficha del alumno.
8. Confirmar que el adeudo del cargo/parcialidad baje correctamente.
9. Confirmar que el excedente se guarde como saldo a favor.

## Pendientes después de esta fase

- Generar recibo/ticket PDF formal por pago.
- Crear vista detallada individual del pago.
- Crear política formal de comprobantes y descargas protegidas por rol.
- Revisar convenios con múltiples cargos, porque aún requieren tabla pivote formal.
- Homologar estatus de solicitudes de pago docente.
- Proteger la API móvil con Sanctum.
