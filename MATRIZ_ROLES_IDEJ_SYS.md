# Matriz operativa de roles — IDEJ-SYS v6

Esta matriz aplica únicamente al panel administrativo interno. El Portal Alumno permanece separado y no fue modificado.

## Niveles

- **G — Gestionar:** consultar, crear y modificar dentro del alcance del área.
- **C — Consultar:** lectura sin modificar.
- **L — Limitado:** solo campos o acciones expresamente indicados.
- **— — Sin acceso:** menú oculto y acceso directo bloqueado.

## Matriz general

| Módulo / función | Admin | Sistemas | Dirección | CAdmin | Académica | Recepción | RRPP |
|---|---:|---:|---:|---:|---:|---:|---:|
| Dashboard | G | C | C | G | G | G | G |
| Usuarios internos | G | C | — | — | — | — | — |
| Roles y credenciales | G | — | — | — | — | — | — |
| Configuración institucional | G | — | — | — | — | — | — |
| Mantenimiento técnico | G | G | — | — | — | — | — |
| Respaldos | G | — | — | — | — | — | — |
| Bitácora | G | C | C | — | — | — | — |
| Alumnos | G | — | C | G | C | L | — |
| Documentos de alumnos | G | — | C sin descarga | G | G académicos | G carga inicial | — |
| Prospectos y seguimientos | G | — | C | G y convertir | — | — | G sin convertir |
| Oferta académica | G | — | C | C | G | C | C |
| Materias y catálogos académicos | G | — | C | C | G | — | — |
| Calendarios y sesiones | G | — | C | C | G | C | — |
| Horarios | G | — | C | C | G | C | — |
| Docentes académicos | G | — | C | C | G | — | — |
| Datos fiscales/bancarios docentes | G | — | C resumen | G | — | — | — |
| Educación Continua | G | L técnico | C | G | G | — | C general |
| Solicitudes de pago docente | G | — | C | G autorización/pago | G creación académica | — | — |
| Conceptos y cargos | G | — | C resumen | G | — | C cargos vigentes | — |
| Becas | G | — | C | G | C criterio académico | C aplicada | — |
| Convenios y parcialidades | G | — | C | G | — | C | — |
| Pagos de alumnos | G | — | C resumen | G | — | G cobro autorizado | — |
| Cancelaciones y ajustes | G | — | C | G | — | — | — |
| Caja propia | G | — | C resumen | G | — | G propia | — |
| Supervisión y conciliación de cajas | G | — | C | G | — | — | — |
| Reportes financieros/operativos | G | — | C | G | — | — | — |
| Reporte ejecutivo | G | — | C | C/G exportación | — | — | — |

## Reglas específicas

### Coordinación Administrativa

CAdmin concentra toda la administración financiera del IDEJ: conceptos, becas, cargos, pagos, cancelaciones, convenios, saldos a favor, cajas, conciliaciones, reportes y pagos docentes. Puede consultar la operación académica, pero no modificar materias, horarios, calendarios ni sesiones.

### Recepción

Recepción puede registrar y actualizar únicamente datos básicos de contacto del alumno. En altas nuevas, el grupo y los estados administrativo-financieros quedan pendientes de validación por CAdmin. Puede recibir documentos autorizados, registrar cobros y operar únicamente su propia caja. No puede crear cargos, modificar becas, cancelar pagos ni gestionar convenios.

### Relaciones Públicas

RRPP gestiona prospectos y seguimientos. Puede consultar la oferta académica y la información general de Educación Continua. No puede acceder al expediente del alumno, documentos, pagos, caja ni convenios. La conversión final de prospecto a alumno requiere validación de CAdmin o Admin.

### Académica

Académica administra programas, ciclos, grupos, materias, docentes, horarios, calendarios y sesiones. Puede consultar alumnos y el estado general de adeudo, pero no registrar pagos, operar caja ni consultar documentos fiscales o bancarios de docentes.

### Sistemas

Sistemas conserva consulta básica de usuarios y acceso técnico a mantenimiento. En Educación Continua únicamente puede actualizar aula, enlace y equipo de sesión. No puede consultar participantes, alumnos, documentos sensibles ni información financiera.

### Dirección

Dirección tiene consulta ejecutiva. No registra, modifica, cancela ni elimina operaciones. Puede consultar estados, cortes y resultados, y generar el PDF oficial de cortes cerrados; no puede descargar documentos privados ni comprobantes sensibles de movimientos.

## Retiro del rol Finanzas

El rol `Finanzas` deja de existir como área operativa. La migración `2026_07_28_000006_merge_finanzas_into_cadmin.php` reasigna las cuentas existentes a CAdmin, invalida sus sesiones previas y elimina el rol obsoleto sin borrar el historial de las operaciones realizadas.
