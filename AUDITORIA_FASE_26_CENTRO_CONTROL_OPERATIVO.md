# Fase 26 - Centro de Control Operativo

## Objetivo

Agregar una pantalla de vigilancia operativa para detectar riesgos antes de que afecten clases, Educación Continua, Recepción, Sistemas, Dirección o pagos docentes.

Esta fase no modifica Portal Alumno y no agrega calificaciones ni asistencias académicas principales.

## Archivos agregados

- `app/Http/Controllers/CentroControlOperativoController.php`
- `resources/views/centro_control/index.blade.php`
- `AUDITORIA_FASE_26_CENTRO_CONTROL_OPERATIVO.md`

## Archivos modificados

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `config/idej_permisos.php`
- `app/Models/CalendarioMateria.php`
- `app/Models/CursoSesion.php`

## Ruta nueva

```txt
/centro-control-operativo
```

Nombre de ruta:

```txt
centro-control.index
```

Roles con acceso:

- Admin
- Sistemas
- Academica
- CAdmin
- Direccion
- Recepcion

## Qué detecta

1. Conflictos de docente en el mismo horario.
2. Conflictos de aula o liga en el mismo horario.
3. Sesiones con horario incompleto.
4. Sesiones sin docente o expositor.
5. Sesiones sin aula o liga.
6. Sesiones virtuales/mixtas sin liga clara.
7. Clases canceladas o suspendidas sin reposición vinculada.
8. Cursos de Educación Continua activos sin sesiones.
9. Solicitudes de pago docente pendientes, observadas o autorizadas vencidas.
10. Materias o sesiones con posible solicitud docente pendiente de generar.

## Notas operativas

El Centro de Control no bloquea operaciones todavía; su primera versión funciona como tablero preventivo.

En fases posteriores puede evolucionar a:

- validaciones obligatorias al guardar sesiones,
- generación automática de alertas/notificaciones,
- validación global de conflictos antes de aprobar calendarios,
- generación asistida de solicitudes de pago docente.

## Cómo probar

```bash
php artisan optimize:clear
php artisan route:list
```

Abrir:

```txt
http://127.0.0.1:8000/centro-control-operativo
```

Validar con Admin, Sistemas, Académica, CAdmin, Dirección o Recepción.
