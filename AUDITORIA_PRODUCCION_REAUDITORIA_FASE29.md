# Reauditoría de producción IDEJ-SYS — Fase 29

## Alcance

Se revisó el sistema interno administrativo/operativo de IDEJ-SYS, excluyendo Portal Alumno.

La revisión se concentró en:

- seguridad de acceso;
- permisos;
- bitácora;
- pagos, caja y solicitudes docentes;
- documentos sensibles;
- respaldos;
- experiencia visual y responsividad;
- mensajes visibles para usuarios internos;
- preparación para producción.

## Veredicto

IDEJ-SYS ya tiene una base funcional seria para preproducción. Sin embargo, para producción real todavía debe mantenerse una etapa de pruebas controladas con datos ficticios y una lista de validación final antes de cargar datos reales.

Las fases previas resolvieron varios riesgos importantes:

- login interno con usuarios activos/inactivos;
- bitácora de accesos y acciones sensibles;
- confirmación de contraseña para operaciones críticas;
- protección básica de headers HTTP;
- comprobantes y documentos sensibles en disco privado;
- caja abierta única por usuario;
- bloqueo transaccional en pagos y solicitudes docentes;
- prevención de doble submit;
- centro de control operativo;
- notificaciones internas;
- reportes ejecutivos.

## Correcciones aplicadas en esta fase

### 1. Responsividad global

Se reforzó el layout principal para evitar scroll horizontal innecesario:

- `body` ahora oculta desbordamiento horizontal accidental.
- El contenedor principal usa `min-w-0`.
- El contenido interno usa `max-w-full`.
- Las tablas que no tengan contenedor responsivo se envuelven automáticamente en un contenedor con scroll horizontal controlado.
- El sidebar funciona mejor en móvil como panel lateral sobrepuesto.
- La campana de notificaciones usa ancho adaptable en pantallas pequeñas.

### 2. Permisos internos sin lenguaje técnico innecesario

Se limpió la vista de permisos para quitar referencias internas como rutas de configuración y claves técnicas. La pantalla ahora se presenta como consulta operativa de accesos por función y rol.

### 3. Protección contra dejar el sistema sin administrador activo

Se reforzó el módulo de usuarios para impedir:

- cambiar el rol del último administrador activo;
- desactivar el último administrador activo.

Esto evita que el sistema quede sin usuario con capacidad administrativa.

### 4. Backups de archivos corregidos

El respaldo de archivos ahora incluye tanto:

- archivos públicos (`storage/app/public`);
- archivos privados (`storage/app/private`).

Esto es importante porque los documentos de alumnos, comprobantes de pago y comprobantes docentes se movieron a disco privado.

### 5. Headers de seguridad reforzados

Se agregaron headers adicionales:

- `Cross-Origin-Resource-Policy`;
- `X-Permitted-Cross-Domain-Policies`;
- `Strict-Transport-Security` cuando la petición entra por HTTPS.

### 6. Textos visibles ajustados

Se corrigieron textos visibles que sonaban demasiado técnicos en:

- permisos internos;
- convenio sin cargos relacionados;
- error 404;
- detalle de bitácora;
- perfil.

## Riesgos aún pendientes antes de producción

### 1. Eliminaciones duras en catálogos y módulos operativos

Todavía existen controladores que ejecutan `delete()` en catálogos o módulos con posible historial:

- programas;
- grupos;
- materias;
- docentes;
- ciclos escolares;
- prospectos;
- convenios;
- parcialidades;
- calendarios;
- cursos de Educación Continua;
- sesiones;
- requisitos documentales.

Recomendación: migrar gradualmente a `activo`, `estatus`, `archivado_at` o `deleted_at` según el módulo.

### 2. No hay 2FA todavía

Para producción en web, los roles críticos deberían usar doble factor:

- Admin;
- Sistemas;
- Dirección;
- CAdmin;
- Finanzas.

### 3. No hay política formal de sesiones activas

Falta panel para:

- ver sesiones activas;
- cerrar otras sesiones;
- invalidar sesiones de usuarios desactivados;
- detectar sesiones sospechosas.

### 4. CSP completa pendiente

Ya existen headers base, pero una política CSP estricta requiere mover dependencias CDN a assets locales o configurarlas de forma controlada.

### 5. Pruebas automatizadas pendientes

El proyecto necesita pruebas mínimas para producción:

- login;
- permisos por rol;
- apertura/cierre de caja;
- pago de alumno;
- cancelación/ajuste;
- solicitud docente;
- descarga de documentos privados;
- notificaciones;
- reportes.

## Preguntas complejas para la siguiente auditoría

### Seguridad

1. ¿Qué pasa si un usuario desactivado mantiene una sesión abierta en otro navegador?
2. ¿Qué pasa si roban la cookie de sesión de un usuario de Finanzas?
3. ¿Qué pasa si se intenta subir un PDF malicioso como comprobante?
4. ¿Qué pasa si un usuario de Recepción intenta descargar comprobantes financieros usando una URL copiada?
5. ¿Qué pasa si alguien intenta abrir 200 solicitudes por minuto al login?
6. ¿Qué pasa si un usuario cambia su contraseña mientras otra sesión sigue abierta?
7. ¿Qué pasa si se filtra un backup descargado desde mantenimiento?
8. ¿Qué pasa si un usuario de Sistemas descarga documentos de alumnos sin razón operativa?

### Finanzas

1. ¿Qué pasa si se registra un pago y la generación del recibo falla después?
2. ¿Qué pasa si se cancela un pago que generó saldo a favor y ese saldo ya fue usado?
3. ¿Qué pasa si una caja se abre un viernes y se intenta cerrar el lunes?
4. ¿Qué pasa si una diferencia de corte queda sin explicación?
5. ¿Qué pasa si se registra transferencia sin comprobante o sin referencia?
6. ¿Qué pasa si se intenta modificar un concepto de pago que ya tiene cargos históricos?
7. ¿Qué pasa si una beca retroactiva afecta cargos parcialmente pagados?

### Académica y operación

1. ¿Qué pasa si una clase se cancela y nunca se reprograma?
2. ¿Qué pasa si el mismo docente está en Educación Continua y Calendario Académico al mismo tiempo?
3. ¿Qué pasa si un aula queda ocupada por dos sesiones simultáneas?
4. ¿Qué pasa si la liga virtual se repite en dos eventos?
5. ¿Qué pasa si una solicitud docente se genera por una sesión cancelada?
6. ¿Qué pasa si Dirección consulta reportes con calendarios incompletos?

### Producción

1. ¿Qué pasa si falla el scheduler durante dos días?
2. ¿Qué pasa si no se generan backups automáticos?
3. ¿Qué pasa si se llena el disco por comprobantes o documentos?
4. ¿Qué pasa si falla la base de datos en medio de un pago?
5. ¿Qué pasa si `APP_DEBUG` queda activo en producción?
6. ¿Qué pasa si se despliega sin correr migraciones?

## Módulos propuestos

### Alta prioridad

1. **Sesiones activas y seguridad de cuenta**
   - Ver dispositivos conectados.
   - Cerrar sesiones.
   - Invalidar sesiones al desactivar usuario.

2. **Doble factor para roles críticos**
   - 2FA obligatorio para Admin, Sistemas, Finanzas, CAdmin y Dirección.

3. **Monitor de producción**
   - Scheduler activo/inactivo.
   - Último backup.
   - Espacio en disco.
   - Últimos errores.
   - Cola de trabajos.

4. **Operaciones automáticas controladas**
   - Avisos de caja abierta fuera de horario.
   - Avisos de becas por vencer.
   - Avisos de convenios vencidos.
   - Avisos de solicitudes docentes vencidas.
   - Avisos de sesiones incompletas.

5. **Inactivación de catálogos**
   - Sustituir eliminaciones por inactivación/archivo.

6. **Pruebas automatizadas de producción**
   - Suite mínima para flujos críticos.

## Recomendación final

El proyecto está avanzando correctamente, pero no debe considerarse listo para datos reales hasta completar al menos:

1. validación manual de Fase 29;
2. prueba con roles reales;
3. prueba con datos demo durante varios días;
4. respaldo y restauración real;
5. checklist de producción;
6. política de usuarios y contraseñas;
7. plan de soporte ante errores.
