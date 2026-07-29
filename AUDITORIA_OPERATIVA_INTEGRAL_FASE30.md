# Auditoría operativa integral IDEJ-SYS · Fase 30

## Alcance aplicado

Auditoría enfocada únicamente en el sistema interno administrativo/operativo de IDEJ-SYS. No se modificó ni se auditó funcionalmente Portal Alumno. Se respetó la regla de autenticación interna con `App\Models\Usuario`.

## Diagnóstico general

IDEJ-SYS ya tiene una base sólida de preproducción: autenticación interna por usuarios institucionales, roles definidos, bitácora, documentos privados, flujo financiero transaccional, caja obligatoria para pagos, cancelaciones, ajustes administrativos, convenios, becas, solicitudes de pago docente, calendario operativo, reportes y módulos de mantenimiento.

La auditoría detectó cuatro riesgos principales:

1. **Entrega técnica insegura del ZIP original**: incluía `.env`, `.git`, `vendor`, `node_modules`, caches, sesiones, logs y `public/storage`.
2. **Sesiones internas activas después de desactivar usuario o cambiar contraseña**: el usuario desactivado podía conservar una sesión abierta hasta que otro control lo bloqueara.
3. **Evidencia documental eliminable físicamente**: un documento archivado/eliminado podía borrar el archivo privado, lo cual es riesgoso para expedientes institucionales.
4. **Residuos de Breeze/Laravel con `UserFactory`**: existía una fábrica referida a `App\Models\User`, incompatible con la regla interna de `App\Models\Usuario`.

También se detectaron riesgos operativos que todavía requieren fase posterior: cruces entre Educación Continua y calendarios académicos, política para cajas abiertas de días anteriores, conciliación bancaria, pruebas automatizadas reales, monitor de backups/restauración, 2FA y control de descargas masivas.

## Correcciones aplicadas

### Seguridad de sesión interna

- Se agregó middleware interno `EnsureUsuarioInternoActivo`.
- Si un usuario interno queda inactivo, se cierra automáticamente su sesión en el guard `web`.
- Si la contraseña del usuario cambia y otra sesión conserva una contraseña anterior, esa sesión se cierra.
- Se limpia confirmación de contraseña al cambiar la contraseña.
- No se aplica al guard `portal_alumno`.

### Usuarios y último Admin

- Se reforzó la modificación/desactivación de usuarios con transacciones.
- Se bloquean filas de administradores activos antes de validar el último Admin.
- Se reduce el riesgo de carrera si dos usuarios intentan quitar/desactivar al último Admin al mismo tiempo.

### Documentos de alumnos

- Se fortaleció validación de archivos con extensión y MIME real.
- Ya no se elimina físicamente el archivo privado al archivar un documento.
- Si un documento ya está Aceptado o Rechazado, no se permite reemplazar su archivo; debe archivarse el registro y cargarse uno nuevo.
- El lenguaje visible se cambió de “Eliminar” a “Archivar” para reflejar mejor el proceso institucional.

### Alumnos

- Se bloqueó la eliminación física de alumnos con historial operativo, financiero, documental, académico, de seguimiento, becas, convenios, bitácora o cursos.
- Para baja institucional se debe cambiar estatus académico, no borrar el alumno.

### Archivos críticos

- Pagos, comprobantes de tarjeta, solicitudes de pago docente y logo institucional ahora validan también MIME real, no solo extensión.

### Factory de pruebas

- Se eliminó `database/factories/UserFactory.php`.
- Se agregó `database/factories/UsuarioFactory.php` para que pruebas y datos de desarrollo usen `App\Models\Usuario`.

### Responsividad

- Se agregaron contenedores `overflow-x-auto` a tablas operativas que podían provocar scroll horizontal global.

### Textos visibles

- Se retiró texto técnico visible en la vista de error 500 que mencionaba rutas internas de logs.

## Migraciones

No se agregaron migraciones nuevas.

## Archivos modificados

- `app/Http/Middleware/EnsureUsuarioInternoActivo.php` — nuevo archivo.
- `bootstrap/app.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/Auth/PasswordController.php`
- `app/Http/Controllers/UsuarioController.php`
- `app/Http/Controllers/AlumnoController.php`
- `app/Http/Controllers/DocumentoAlumnoController.php`
- `app/Http/Controllers/PagoController.php`
- `app/Http/Controllers/SolicitudPagoDocenteController.php`
- `app/Http/Controllers/ConfiguracionInstitucionalController.php`
- `database/factories/UsuarioFactory.php` — nuevo archivo.
- `database/factories/UserFactory.php` — eliminado.
- `resources/views/alumnos/documentos_index.blade.php`
- `resources/views/alumnos/convenios_index.blade.php`
- `resources/views/ciclos_escolares/index.blade.php`
- `resources/views/convenios/edit.blade.php`
- `resources/views/convenios/parcialidades/index.blade.php`
- `resources/views/educacion_continua/index.blade.php`
- `resources/views/errors/500.blade.php`
- `AUDITORIA_OPERATIVA_INTEGRAL_FASE30.md` — nuevo reporte.

## Validaciones ejecutadas

- Sintaxis PHP de archivos modificados: correcta.
- Sintaxis PHP del sistema interno en `app`, `bootstrap`, `config`, `database` y `routes`, excluyendo Portal Alumno: correcta.
- `php artisan route:list`: correcto, 237 líneas de salida.
- Búsqueda de referencias internas a `App\Models\User`: sin coincidencias relevantes después de la corrección.

No se pudo ejecutar PHPUnit en este contenedor porque faltan extensiones PHP de CLI: `dom`, `mbstring`, `xml`, `xmlwriter`. En Laragon con PHP 8.3 deben activarse esas extensiones para correr pruebas.

## Riesgos críticos

1. **Paquete original con secretos y dependencias**: `.env`, `.git`, `vendor`, `node_modules`, logs y sesiones no deben distribuirse.
2. **Sesiones internas después de desactivación/cambio de contraseña**: corregido con middleware.
3. **Último Admin en carrera concurrente**: mitigado con bloqueo transaccional.
4. **Evidencia documental eliminable físicamente**: corregido archivando sin borrar archivo.
5. **Archivos con extensión falsa**: mitigado parcialmente con validación MIME real.
6. **Backups/restauración sin prueba real**: pendiente crítico de producción.
7. **Cruces operativos entre calendario académico y Educación Continua**: pendiente.
8. **Caja abierta de días anteriores**: requiere política operativa y alerta/bloqueo.
9. **Pagos y solicitudes docentes requieren pruebas concurrentes reales**: el código ya usa transacciones en partes críticas, pero falta test automatizado.

## Riesgos medios

- Falta 2FA para roles críticos.
- Falta alertar descargas masivas de documentos.
- Falta conciliación bancaria básica.
- Falta política de cierre forzoso o supervisado para caja abierta antigua.
- Falta panel de documentos sensibles por alumno/usuario.
- Falta versionado de cambios en configuraciones institucionales.
- Falta retención formal de documentos rechazados/archivados.
- Falta validación cruzada calendario principal vs Educación Continua.
- Falta validación de monto de solicitud docente contra horas por tarifa.
- Falta manejo explícito de exportaciones CSV con caracteres especiales en todos los reportes.

## Riesgos menores

- Algunas vistas usan lógica PHP en Blade; no es visible al usuario, pero conviene extraer helpers/componentes más adelante.
- Algunas acciones usan nombres históricos como “Eliminar” en módulos no críticos; conviene migrarlas a “Archivar/Inactivar” donde haya historial.
- La responsividad se mejoró en tablas detectadas, pero falta revisión visual completa módulo por módulo.
- Algunos módulos administrativos muestran conceptos técnicos aceptables para Sistemas, pero deberían simplificarse si los usa personal no técnico.

## Auditoría por módulo

### 1. Autenticación interna con `App\Models\Usuario`

Flujo esperado: login interno por guard `web`, usuario institucional activo, rol interno, bitácora de acceso.

Qué pasaría si un usuario queda inactivo con sesión abierta: ahora el middleware cierra sesión al siguiente request.

Debe responder: cerrar sesión, limpiar confirmación de contraseña, registrar bitácora y mostrar mensaje institucional.

Validaciones/permisos: usar solo `Usuario`; no depender de `User`.

Bitácora: login, logout, intento fallido, cierre por inactividad administrativa, cierre por cambio de contraseña.

No permitir: login de usuario inactivo, acceso directo por URL si no tiene sesión/rol.

### 2. Roles y permisos internos

Flujo esperado: rutas protegidas por roles/middleware y matriz funcional.

Qué pasaría si un rol entra por URL directa: debe responder 403 institucional.

Riesgo: transición incompleta de permisos por rol a permisos funcionales.

Mejora: migrar más rutas a permisos funcionales y centralizar acciones críticas.

### 3. Usuarios activos/inactivos

Flujo esperado: desactivar en vez de eliminar; proteger último Admin.

Qué pasaría si dos administradores intentan desactivar al último Admin: se mitigó con transacción y bloqueo.

Debe requerir contraseña: cambio de rol, desactivación, reactivación y cambio de contraseña administrativo.

No eliminar físicamente: usuarios con bitácora, pagos, documentos, solicitudes o actividad.

### 4. Bitácora y auditoría

Flujo esperado: registrar acciones críticas, actor, módulo, descripción y relación cuando aplique.

Qué pasaría si una operación falla a media transacción: la bitácora no debe registrar éxito falso.

Riesgo: falta histórico de cambios por modelo para campos antes/después.

Mejora: auditoría diferencial en alumnos, pagos, becas, convenios y configuración.

### 5. Alumnos y expediente

Flujo esperado: expediente como fuente central de datos, pagos, cargos, documentos, convenios, becas y seguimientos.

Qué pasaría si se intenta borrar un alumno con historial: ahora se bloquea eliminación física.

Debe responder: sugerir baja/estatus académico, no destrucción del registro.

No eliminar físicamente: alumnos con historial operativo.

### 6. Prospectos y conversión a alumno

Flujo esperado: prospecto → seguimiento → interesado/inscrito → alumno.

Qué pasaría si se convierte dos veces: debe impedir alumno duplicado por prospecto y registrar relación origen.

Riesgo: duplicidad por correo/teléfono/nombre si no hay normalización.

Mejora: validación de duplicados flexibles y bitácora de conversión.

### 7. Seguimientos

Flujo esperado: historial de llamadas, WhatsApp, acuerdos y próximos contactos.

Qué pasaría si un usuario borra un seguimiento con acuerdo: debería archivarse, no eliminarse.

Mejora: inactivar/archivar seguimiento, registrar cambios y vencimientos.

### 8. Documentos de alumnos

Flujo esperado: carga privada, revisión, aceptación/rechazo, descarga protegida.

Qué pasaría si se sube extensión falsa: ahora se valida MIME además de extensión.

Qué pasaría si se elimina documento aceptado: ahora se archiva registro y se conserva archivo.

Qué pasaría si se accede por URL directa: debe bloquearse; archivos deben estar en disco privado.

Debe registrarse: carga, revisión, rechazo, aceptación, descarga, archivo.

### 9. Requisitos documentales

Flujo esperado: catálogo institucional por nivel/programa y checklist por alumno.

Qué pasaría si se elimina requisito usado: debe inactivarse, no eliminarse.

Mejora: proteger requisitos con alumnos/documentos asociados.

### 10. Cargos

Flujo esperado: adeudos por concepto, vencimiento, saldo pendiente y relación con beca/convenio/pago.

Qué pasaría si dos pagos aplican al mismo cargo: debe existir lock transaccional y recalcular saldo.

Qué pasaría si cargo está en convenio: no debe pagarse fuera del convenio salvo regla institucional explícita.

No eliminar físicamente: cargos con pagos, convenios o becas aplicadas.

### 11. Pagos

Flujo esperado: caja abierta → registrar pago → aplicar a cargos/parcialidades → generar recibo → actualizar saldos.

Qué pasaría si hay doble clic: `operacion_uuid` mitiga duplicidad.

Qué pasaría si dos usuarios pagan el mismo cargo: deben bloquearse cargos/parcialidades en transacción.

Qué pasaría si cae internet después de cobrar: el usuario debe consultar si el pago existe antes de repetir; error 419 ya advierte esto.

Debe requerir contraseña: registrar/cancelar/ajustar pagos según criticidad.

### 12. Caja y cortes

Flujo esperado: abrir caja, registrar pagos, cerrar, comparar montos y diferencia.

Qué pasaría si usuario tiene caja abierta anterior: debe alertar/bloquear operaciones hasta cierre supervisado.

Qué pasaría si corte tiene diferencia: debe quedar registrada y visible a Finanzas/Dirección.

No permitir: pagos sin caja abierta.

### 13. Cancelaciones de pagos

Flujo esperado: cancelar sin borrar pago; revertir adeudos si caja abierta; si caja cerrada, usar ajuste administrativo.

Qué pasaría si se cancela pago de caja cerrada: no debe alterar corte histórico sin ajuste.

Debe requerir contraseña y motivo obligatorio.

### 14. Ajustes administrativos

Flujo esperado: ajustes para correcciones con caja cerrada o control financiero.

Qué pasaría si Finanzas modifica histórico: debe quedar como ajuste, no edición directa.

Debe registrarse: motivo, usuario, caja/pago relacionado, monto y fecha.

### 15. Convenios

Flujo esperado: cargos pendientes → convenio → parcialidades → pagos → cumplido.

Qué pasaría si se paga cargo en convenio fuera del convenio: debe bloquearse o redirigir a parcialidades.

Qué pasaría si se elimina convenio con pagos: no debe permitirse.

No eliminar físicamente: convenios con cargos/parcialidades/pagos.

### 16. Becas

Flujo esperado: beca institucional con vigencia, autorización y aplicación a cargos becables.

Qué pasaría si beca aplica a cargos ya pagados: no debe alterar pagos cerrados; podría generar saldo a favor solo con regla explícita.

Qué pasaría si beca vencida sigue aplicándose: scheduler o validación en tiempo real debe actualizar estatus.

Debe requerir contraseña: autorización/cancelación.

### 17. Solicitudes de pago docente

Flujo esperado: Académica crea, Administración/Finanzas autoriza/observa, Finanzas paga.

Qué pasaría si dos usuarios pagan la misma solicitud: se usa lock y `pago_operacion_uuid`, pero falta prueba concurrente.

Qué pasaría si monto no coincide con horas x tarifa: debe advertir o bloquear salvo ajuste justificado.

Qué pasaría si solicitud ya pagada se edita: no debe permitirse.

### 18. Calendarios académicos

Flujo esperado: calendario por fechas exactas, sesiones, docente, aula/liga, cancelación y reprogramación.

Qué pasaría si docente/aula/liga se duplican: debe bloquear por fecha/hora.

Riesgo: falta cruce completo contra Educación Continua.

Qué pasaría si calendario con sesiones históricas se edita: no debe borrar/recrear historial operativo sin control.

### 19. Educación Continua

Flujo esperado: curso, sesiones, inscritos, asistencia y horas.

Qué pasaría si choca con calendario principal: debe detectarse en Centro de Control y validación de guardado.

Qué pasaría si curso no tiene sesiones: debe alertarse.

### 20. Agenda Operativa

Flujo esperado: vista unificada de clases principales y Educación Continua.

Qué pasaría si sesión virtual no tiene liga: debe marcar alerta.

Qué pasaría si sesión presencial no tiene aula: debe marcar alerta.

Mejora: convertir alertas en acciones resolutivas.

### 21. Centro de Control Operativo

Flujo esperado: detectar conflictos antes de afectar operación.

Qué pasaría si detecta conflicto y nadie lo atiende: debe generar notificación interna o tarea.

Mejora: severidad, responsable, estado de atención y bitácora de resolución.

### 22. Notificaciones internas

Flujo esperado: notificaciones por usuario, rol o globales.

Qué pasaría si hay muchas notificaciones: debe paginar y archivar sin borrar.

Qué pasaría si alerta crítica se marca leída: debe conservar historial.

### 23. Reportes ejecutivos

Flujo esperado: indicadores financieros, académicos y operativos para Dirección.

Qué pasaría si pagos cancelados se suman como activos: reporte debe excluir/categorizar cancelados.

Qué pasaría si hay ajustes administrativos: deben mostrarse como renglón propio.

Qué pasaría si periodo no tiene datos: debe mostrar ceros y mensaje claro, no error.

### 24. Configuración institucional

Flujo esperado: datos institucionales, recibos, logo, contacto y parámetros.

Qué pasaría si se sube logo malicioso: ahora se valida MIME.

Debe requerir contraseña: cambios de configuración sensible.

Mejora: historial de cambios antes/después.

### 25. Mantenimiento, respaldos y restauración

Flujo esperado: generar respaldo de BD y archivos privados, descargar con permiso y confirmar contraseña.

Qué pasaría si backup no incluye documentos privados: restauración queda inconsistente.

Qué pasaría si se restaura BD sin archivos: expedientes quedan rotos.

Mejora crítica: prueba mensual de restauración en ambiente aislado.

### 26. Seguridad general para producción

Revisar antes de producción:

- `APP_DEBUG=false`
- `APP_ENV=production`
- `SESSION_SECURE_COOKIE=true` con HTTPS
- `.env` fuera de repositorio/ZIP
- backups cifrados o protegidos
- logs no públicos
- permisos de storage correctos
- usuario DB con privilegios mínimos razonables
- headers de seguridad activos

### 27. Responsividad de vistas

Flujo esperado: laptop y pantallas medianas sin scroll horizontal global.

Corrección aplicada: tablas detectadas con riesgo de overflow recibieron contenedor responsivo.

Pendiente: revisión visual real en navegador para dashboards, reportes, calendario mensual y formularios largos.

### 28. Textos visibles al usuario final

Flujo esperado: lenguaje institucional claro.

Corrección aplicada: error 500 ya no muestra ruta técnica de logs; documentos usa “Archivar” en lugar de “Eliminar”.

Pendiente: revisar todos los mensajes históricos de módulos administrativos para migrar tecnicismos a lenguaje operativo.

## Cómo probar estos cambios

### Instalación limpia local

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan route:list
php artisan serve
npm run dev
```

### Configuración local recomendada

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=idej_sys
DB_USERNAME=root
DB_PASSWORD=
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
FILESYSTEM_DISK=local
```

### Pruebas técnicas

```bash
php -l app/Http/Middleware/EnsureUsuarioInternoActivo.php
php -l app/Http/Controllers/UsuarioController.php
php -l app/Http/Controllers/DocumentoAlumnoController.php
php -l app/Http/Controllers/PagoController.php
php -l app/Http/Controllers/SolicitudPagoDocenteController.php
php -l database/factories/UsuarioFactory.php
php artisan route:list
```

Si se activan extensiones PHP requeridas:

```bash
php artisan test
```

## Checklist de pruebas manuales

### Usuarios y seguridad

- Iniciar sesión con Admin.
- Crear usuario interno nuevo.
- Cambiar rol de un usuario.
- Intentar quitar rol Admin al último Admin activo: debe bloquear.
- Intentar desactivar último Admin activo: debe bloquear.
- Abrir sesión con usuario A en otra ventana, desactivar usuario A desde Admin y refrescar la otra ventana: debe cerrar sesión.
- Cambiar contraseña de usuario A desde Admin y probar sesión abierta anterior: debe cerrar sesión.

### Documentos

- Subir PDF válido a alumno.
- Subir JPG/PNG válido.
- Intentar subir archivo con extensión falsa: debe rechazar.
- Aceptar un documento.
- Intentar reemplazar archivo de documento aceptado: debe bloquear.
- Archivar documento: debe desaparecer del listado activo, pero conservar archivo privado.
- Intentar acceso directo por URL de archivo privado: debe bloquear.

### Alumnos

- Intentar eliminar alumno sin historial: debe permitir si procede.
- Intentar eliminar alumno con cargos/pagos/documentos/seguimientos: debe bloquear y sugerir cambio de estatus.

### Pagos/caja

- Intentar registrar pago sin caja abierta: debe bloquear.
- Abrir caja y registrar pago.
- Dar doble clic en registrar pago: no debe duplicar operación.
- Cancelar pago de caja abierta: debe revertir adeudo.
- Intentar cancelar pago de caja cerrada: debe pedir ajuste administrativo según flujo.
- Generar recibo PDF dos veces: debe mostrar el mismo pago, no crear otro.

### Solicitudes docentes

- Crear solicitud pendiente.
- Observar solicitud.
- Intentar pagar sin autorización: debe bloquear.
- Autorizar solicitud.
- Pagar con comprobante válido.
- Intentar pagar nuevamente: debe bloquear.
- Intentar editar pagada: debe bloquear o impedir cambios críticos.

### Académica / agenda

- Crear calendario con sesiones válidas.
- Intentar docente duplicado en mismo horario dentro del calendario: debe bloquear.
- Intentar aula duplicada en mismo horario: debe bloquear.
- Crear curso de Educación Continua y revisar agenda operativa.
- Revisar Centro de Control Operativo para sesiones sin docente/aula/liga.

### Reportes

- Revisar periodo con pagos activos.
- Revisar periodo con pagos cancelados.
- Revisar periodo con ajustes administrativos.
- Exportar CSV y abrirlo en Excel con acentos y caracteres especiales.

### Producción simulada

- Cambiar `.env` a valores de producción en ambiente de prueba.
- Verificar que `APP_DEBUG=false` no muestre stack trace.
- Generar backup.
- Verificar que backup incluya base y archivos privados.
- Restaurar en ambiente aislado y abrir expedientes con documentos.

## Advertencias de producción

- No desplegar el ZIP original porque contenía `.env`, `.git`, dependencias y archivos temporales.
- No activar `APP_DEBUG=true` en producción.
- No usar `SESSION_SECURE_COOKIE=false` en producción con HTTPS.
- No confiar en backup si no se probó restauración.
- No permitir edición directa de pagos históricos.
- No permitir eliminación física de documentos o alumnos con historial.
- No operar pagos si hay caja abierta antigua sin cierre supervisado.
- No correr producción sin scheduler/cron si se depende de tareas programadas.
- No guardar comprobantes o documentos sensibles en disco público.

## Recomendación de siguiente fase

Fase 31 recomendada: **blindaje financiero y operativo concurrente**.

Objetivos:

1. Pruebas automatizadas de pagos concurrentes, doble clic, caja abierta/cerrada, cancelaciones y ajustes.
2. Política de caja abierta de días anteriores.
3. Bloqueo de pago fuera de convenio para cargos en convenio.
4. Validación de solicitudes docentes: horas x tarifa vs monto.
5. Cruce académico completo entre calendario principal y Educación Continua.
6. Monitor de backups con prueba de restauración.
7. 2FA para Admin, Sistemas, Dirección, CAdmin y Finanzas.
