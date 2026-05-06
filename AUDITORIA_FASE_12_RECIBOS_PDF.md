# Auditoría Fase Local 12 - Recibos / tickets de pago en PDF

## Objetivo

Agregar recibos formales en PDF para pagos de alumnos, conectados al expediente, historial de pagos y cortes de caja.

## Cambios realizados

### Backend

- Se agregó la ruta `alumnos.pagos.recibo` para generar el PDF del recibo.
- Se agregó el método `recibo()` en `PagoController`.
- Se agregó generación automática de folio cuando el pago no trae folio manual.
- Se agregaron datos internos de control del recibo:
  - `recibo_uuid`
  - `recibo_emitido_at`
  - `recibo_version`
- Se mantuvo validación de pertenencia: el recibo solo se genera si el pago pertenece al alumno de la ruta.
- Se cargan relaciones necesarias para el PDF:
  - alumno
  - grupo
  - programa
  - usuario que recibió
  - corte de caja
  - cargos aplicados
  - parcialidades aplicadas

### Base de datos

Nueva migración:

```txt
2026_01_08_204314_add_recibo_fields_to_pagos_table.php
```

### Modelo

Se actualizó `App\Models\Pago` para permitir y castear los nuevos campos del recibo.

### Vistas

Nueva vista PDF:

```txt
resources/views/pagos/recibo_pdf.blade.php
```

Vistas actualizadas con botón de recibo:

```txt
resources/views/alumnos/show.blade.php
resources/views/alumnos/pagos_index.blade.php
resources/views/cortes_caja/show.blade.php
```

## Alcance funcional

El recibo PDF muestra:

- Institución
- Folio
- ID del pago
- Datos del alumno
- Matrícula
- Programa
- Grupo
- Fecha de pago
- Método de pago
- Referencia
- Usuario que recibió
- Corte de caja
- Monto total recibido
- Detalle de cargos cubiertos
- Detalle de parcialidades cubiertas
- Saldo a favor generado, si aplica
- Observaciones
- Líneas de firma
- UUID interno
- Leyenda de comprobante interno no fiscal

## Comandos para aplicar en local

```bash
php artisan optimize:clear
php artisan migrate
```

Después:

```bash
php artisan serve
npm run dev
```

## Pruebas recomendadas

1. Abrir caja.
2. Registrar un pago nuevo.
3. Abrir el expediente del alumno.
4. Descargar recibo PDF desde pagos recientes.
5. Abrir historial completo de pagos.
6. Descargar recibo PDF desde ahí.
7. Abrir el corte de caja.
8. Descargar recibo PDF desde el detalle de pagos del corte.
9. Probar un pago aplicado a cargo.
10. Probar un pago aplicado a parcialidad.
11. Probar un pago con excedente y validar saldo a favor generado en el recibo.

## Nota importante

Este recibo es un comprobante interno de control administrativo. No sustituye CFDI ni documento fiscal.
