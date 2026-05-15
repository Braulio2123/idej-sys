# Fase 28 - Reportes Ejecutivos para Dirección

## Objetivo

Agregar una vista ejecutiva integral para Dirección y perfiles autorizados, orientada a consultar el estado general del IDEJ sin navegar por cada módulo operativo.

Esta fase no modifica datos operativos; únicamente consulta y resume información existente.

## Alcance

Se agregó el módulo:

- `Reportes Ejecutivos`
- Ruta: `/reportes-ejecutivos`
- Nombre de ruta: `reportes.ejecutivo`
- Exportación CSV: `/reportes-ejecutivos/export-csv`

## Roles con acceso

- Admin
- CAdmin
- Finanzas
- Direccion

## Indicadores incluidos

### Finanzas

- Ingresos del periodo.
- Cargos generados en el periodo.
- Adeudo total vigente.
- Adeudo vencido.
- Pagos cancelados.
- Cajas abiertas.
- Cortes con diferencia.
- Recuperación estimada del periodo.
- Adeudo por programa.
- Ingresos por método de pago.

### Alumnos y prospectos

- Alumnos totales.
- Alumnos nuevos del periodo.
- Alumnos con adeudo.
- Alumnos al corriente.
- Prospectos activos.
- Prospectos nuevos.
- Prospectos convertidos.
- Prospectos vencidos.
- Prospectos próximos a contactar.
- Conversión del periodo.

### Becas y convenios

- Becas activas.
- Becas próximas a vencer.
- Promedio de porcentaje de beca.
- Monto becado aplicado en cargos.
- Convenios activos.
- Monto pendiente en convenios.
- Parcialidades vencidas.
- Monto vencido de parcialidades.

### Solicitudes de pago docente

- Pendientes.
- Observadas.
- Autorizadas.
- Pagadas en periodo.
- Autorizadas vencidas sin pago.
- Monto por estatus.

### Operación académica

- Sesiones del día.
- Sesiones principales próximas 30 días.
- Sesiones de Educación Continua próximas 30 días.
- Sesiones incompletas.
- Cancelaciones sin reposición.
- Cursos activos.
- Cursos sin sesiones.
- Calendarios activos.

## Alertas ejecutivas

La vista genera alertas de seguimiento cuando detecta:

- Cajas abiertas.
- Adeudo vencido.
- Solicitudes docentes autorizadas vencidas sin pago.
- Solicitudes docentes observadas.
- Sesiones próximas incompletas.
- Cancelaciones sin reposición.
- Prospectos vencidos.
- Parcialidades de convenio vencidas.

## Archivos agregados

- `app/Http/Controllers/ReporteEjecutivoController.php`
- `resources/views/reportes/ejecutivo.blade.php`
- `AUDITORIA_FASE_28_REPORTES_EJECUTIVOS.md`

## Archivos modificados

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `config/idej_permisos.php`

## Nota técnica

El módulo es de solo lectura. No crea, actualiza ni elimina información institucional.

No se tocó Portal Alumno.
