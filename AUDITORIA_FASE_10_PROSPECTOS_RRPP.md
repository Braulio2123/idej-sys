# Auditoría Fase Local 10 — Prospectos y Relaciones Públicas

## Objetivo

Agregar un módulo operativo para el flujo previo a la inscripción:

**Prospecto → Seguimiento → Inscripción → Alumno**

El objetivo es que Relaciones Públicas, Recepción y Dirección puedan controlar interesados, campañas, medios de contacto y conversiones sin depender de hojas de cálculo o mensajes sueltos.

## Archivos agregados

- `database/migrations/2026_01_08_204310_create_prospectos_table.php`
- `database/migrations/2026_01_08_204311_add_foreign_key_to_seguimientos_prospecto_id.php`
- `app/Models/Prospecto.php`
- `app/Http/Controllers/ProspectoController.php`
- `resources/views/prospectos/index.blade.php`
- `resources/views/prospectos/create.blade.php`
- `resources/views/prospectos/edit.blade.php`
- `resources/views/prospectos/show.blade.php`
- `resources/views/prospectos/_form.blade.php`

## Archivos modificados

- `routes/web.php`
- `app/Models/Alumno.php`
- `app/Models/Programa.php`
- `app/Models/Usuario.php`
- `app/Models/Seguimiento.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Traits/RegistraBitacora.php`
- `resources/views/dashboard.blade.php`
- `resources/views/layouts/app.blade.php`

## Funciones implementadas

### Prospectos

- Alta de prospectos.
- Edición de prospectos no convertidos.
- Consulta por filtros:
  - búsqueda general,
  - estatus,
  - programa,
  - medio de contacto,
  - asesor,
  - vencidos.
- Vista de detalle del prospecto.
- Priorización por urgencia.
- Control de fecha de próximo contacto.
- Estatus operativo:
  - Nuevo,
  - Contactado,
  - Interesado,
  - En seguimiento,
  - Inscrito,
  - Descartado.

### Seguimientos de prospectos

- Registro de seguimientos desde la ficha del prospecto.
- Uso de la tabla `seguimientos` ya existente.
- Relación formal con `prospecto_id`.
- Conserva usuario responsable, área, tipo, prioridad, asunto, descripción, resultado y próximo contacto.

### Conversión a alumno

- Conversión formal de prospecto a alumno.
- Se solicita matrícula obligatoria.
- Permite asignar grupo.
- Conserva correo/teléfono del prospecto.
- Vincula el prospecto con el alumno creado.
- Transfiere seguimientos previos al expediente del alumno mediante `alumno_id`.
- Bloquea edición/eliminación de prospectos ya convertidos.

### Dashboard

Se agregaron indicadores:

- Prospectos activos.
- Prospectos vencidos.
- Prospectos convertidos este mes.
- Próximos prospectos a contactar.

### Permisos

- Admin, Recepción, C. Administrativa y Relaciones Públicas pueden crear, editar, dar seguimiento y convertir prospectos.
- Dirección puede consultar prospectos y métricas, sin modificar.

## Comandos para aplicar en local

```bash
php artisan optimize:clear
php artisan migrate
```

Luego seguir usando:

```bash
php artisan serve
npm run dev
```

## Pruebas recomendadas

1. Crear prospecto nuevo.
2. Filtrar prospectos por estatus y medio.
3. Abrir ficha del prospecto.
4. Registrar seguimiento con próximo contacto.
5. Revisar que aparezca en dashboard si está dentro de los próximos 7 días.
6. Convertir el prospecto a alumno.
7. Confirmar que se redirija al expediente del alumno.
8. Confirmar que el prospecto quede en estatus `Inscrito`.
9. Confirmar que el prospecto convertido ya no permita edición.
10. Confirmar que Dirección pueda consultar pero no modificar.

## Siguiente recomendación

Después de Prospectos, el siguiente módulo de alto valor sería **Caja / Cortes de caja**, para formalizar pagos diarios, cancelaciones, recibos y reportes por usuario/turno.
