# Fase Local 7 — Expediente del Alumno y Seguimientos

## Objetivo

Convertir la ficha del alumno en un expediente institucional más útil y agregar un módulo operativo de seguimientos para registrar contactos, acuerdos, observaciones y pendientes relacionados con el alumno.

## Cambios realizados

### 1. Nuevo módulo: Seguimientos

Se agregó la tabla `seguimientos` con los siguientes campos principales:

- `alumno_id`: relación con el alumno.
- `prospecto_id`: campo reservado para el futuro módulo de prospectos.
- `usuario_id`: usuario que registró el seguimiento.
- `area`: área o rol institucional del usuario.
- `tipo`: Llamada, WhatsApp, Correo, Visita, Documento, Acuerdo de pago, Académico o General.
- `prioridad`: Baja, Normal, Alta o Urgente.
- `estatus`: Abierto, En proceso, Cerrado o Cancelado.
- `asunto`.
- `descripcion`.
- `resultado`.
- `fecha_contacto`.
- `fecha_proximo_contacto`.
- `fecha_cierre`.

### 2. Nuevo modelo `Seguimiento`

Se agregó `app/Models/Seguimiento.php` con:

- Constantes de tipos.
- Constantes de prioridades.
- Constantes de estatus.
- Relaciones con `Alumno` y `Usuario`.
- Scopes para seguimientos abiertos, vencidos y próximos.

### 3. Nuevo controlador `SeguimientoController`

Se agregó `app/Http/Controllers/SeguimientoController.php` con operaciones:

- Listar seguimientos por alumno.
- Crear seguimiento desde el expediente.
- Actualizar seguimiento.
- Eliminar seguimiento.

Todas las operaciones registran bitácora.

### 4. Expediente del alumno rediseñado

Se reemplazó la vista `resources/views/alumnos/show.blade.php` para incluir:

- Encabezado ejecutivo del expediente.
- Datos personales.
- Datos académicos.
- Datos financieros.
- Indicadores de adeudo, pagos, saldo a favor y seguimientos.
- Formulario rápido para registrar seguimiento.
- Historial reciente de seguimientos.
- Cargos recientes.
- Pagos recientes.
- Convenios recientes.

### 5. Vista completa de seguimientos

Se agregó:

`resources/views/alumnos/seguimientos_index.blade.php`

Incluye:

- Filtros por estatus, tipo y prioridad.
- Listado completo paginado.
- Alta rápida de seguimiento.
- Edición en línea.
- Eliminación controlada.

### 6. Dashboard operativo

El dashboard ahora muestra:

- Seguimientos abiertos.
- Seguimientos vencidos.
- Alumnos con adeudo.
- Próximos seguimientos de los siguientes 7 días.

Esto ayuda a Recepción, Relaciones Públicas, Académica, Finanzas y Dirección a detectar pendientes operativos.

## Archivos modificados o agregados

- `database/migrations/2026_01_08_204306_create_seguimientos_table.php`
- `app/Models/Seguimiento.php`
- `app/Models/Alumno.php`
- `app/Models/Usuario.php`
- `app/Http/Controllers/SeguimientoController.php`
- `app/Http/Controllers/AlumnoController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Traits/RegistraBitacora.php`
- `routes/web.php`
- `resources/views/alumnos/show.blade.php`
- `resources/views/alumnos/seguimientos_index.blade.php`
- `resources/views/dashboard.blade.php`

## Permisos aplicados

### Consulta de seguimientos

- Admin
- Recepción
- Coordinación Administrativa
- Finanzas
- Relaciones Públicas
- Coordinación Académica
- Dirección

### Crear, editar y eliminar seguimientos

- Admin
- Recepción
- Coordinación Administrativa
- Finanzas
- Relaciones Públicas
- Coordinación Académica

Dirección puede consultar, pero no modificar.

## Comandos recomendados para probar

Si quieres conservar datos locales:

```bash
php artisan optimize:clear
php artisan migrate
```

Si estás trabajando con datos de prueba y quieres base limpia:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
```

Luego:

```bash
php artisan serve
```

Y en otra terminal:

```bash
npm run dev
```

## Pruebas manuales sugeridas

1. Entrar a un alumno.
2. Registrar un seguimiento tipo WhatsApp.
3. Registrar un seguimiento con próximo contacto.
4. Revisar que aparezca en el expediente.
5. Abrir el historial completo de seguimientos.
6. Filtrar por estatus, tipo y prioridad.
7. Editar seguimiento y cambiarlo a Cerrado.
8. Confirmar que se registre fecha de cierre.
9. Eliminar un seguimiento de prueba.
10. Revisar dashboard para próximos seguimientos.

## Pendientes recomendados

- Agregar módulo global de agenda de seguimientos por área.
- Conectar seguimientos con el futuro módulo de prospectos.
- Agregar notificaciones o alertas para seguimientos vencidos.
- Agregar exportación de historial del expediente.
- Agregar documentos del alumno como siguiente módulo institucional.
