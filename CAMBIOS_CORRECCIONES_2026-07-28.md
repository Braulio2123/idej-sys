# Resumen de correcciones IDEJ-SYS

## Bloque 1 — operaciones y estados

- Se aisló la notificación posterior al pago para evitar error 500 después del `commit`.
- Se alinearon estados de calendarios y docentes.

## Bloque 2 — roles y autorización

- Sistemas ya no puede crear administradores ni modificar credenciales o roles.
- Recepción solo opera su propia caja.
- Se alinearon menús, rutas y permisos en módulos revisados.

## Bloque 3 — pagos, caja y concurrencia

- UUID, transacciones y bloqueos para pagos, movimientos, cargos y operaciones masivas.
- Conciliación separada por método de pago.
- Motivos obligatorios para cancelaciones y diferencias.

## Bloque 4 — integridad histórica

- Grupos, calendarios, convenios, prospectos y seguimientos usan archivo o cancelación en lugar de borrado destructivo.
- Se reforzaron llaves foráneas para conservar historial.

## Bloque 5 — documentos privados

- Almacenamiento privado centralizado, validación MIME, SHA-256 y descarga autorizada.
- Reemplazos atómicos y auditoría de referencias físicas.

## Bloque 6 — autenticación y sesiones

- Tokens de recuperación y sesiones vinculados a `auth_version`.
- Invalidación determinista al cambiar contraseña, correo, rol o estado.
- Política de contraseñas internas fortalecida.

## Bloque 7 — doble envío y botón Atrás

- Idempotencia general para rutas internas de escritura.
- Verificación del patrón POST → Redirect → GET.

## Bloque 8 — producción y entrega

- Configuración institucional limitada a Admin.
- Respaldos completos y vaciado de logs limitados a Admin y convertidos a POST.
- Encabezados de seguridad y no-cache para el panel interno.
- Validación de host y proxy inverso.
- Comando `idej:validar-produccion` y plantillas seguras de entorno.
- Paquete final construido mediante lista permitida, sin secretos ni dependencias instaladas.

## Corrección posterior a validación local — Seeder integral

- Se sustituyó la referencia obsoleta `CalendarioAcademico::ESTATUS_PLANEADO` en `DatosDemoIntegralSeeder`.
- El seeder ahora calcula el estado del calendario según `fecha_inicio`, `fecha_fin` y la fecha de ejecución, utilizando únicamente los estados vigentes: `Agendado`, `En curso` o `Finalizado`.
- Se revisaron globalmente las referencias `Clase::CONSTANTE` del código propio; no se localizaron otras constantes de proyecto inexistentes.

## Corrección posterior a ejecución completa de pruebas — Suite MySQL y permisos

- Se corrigió la lectura de permisos cuyas claves contienen puntos, como `caja.ver` y `configuracion.editar`. Laravel ya no interpreta esos puntos como niveles anidados; los roles autorizados recuperan correctamente sus permisos y los no autorizados permanecen bloqueados.
- Se actualizó `rolesParaPermiso()` con la misma lectura literal de claves.
- La suite de pruebas utiliza una base MySQL separada llamada `idej_sys_testing`, evitando depender de SQLite y reproduciendo mejor llaves foráneas, enums, transacciones y bloqueos del sistema real.
- Se corrigieron pruebas desactualizadas: la raíz redirige a `/dashboard`, DELETE `/profile` devuelve 405, las rutas temporales de idempotencia cargan sesión web y la exclusión del filtro masivo se valida mediante `excludedMiddleware()`.
- Se añadió una regresión unitaria para permisos con claves punteadas.


## Hotfix V4 — Idempotencia de invitados

- Se corrigió la huella de idempotencia de formularios invitados.
- Cuando existe `_idempotency_key` UUID, la identidad ya no depende del ID de sesión, porque este puede rotar entre solicitudes.
- El mismo envío repetido vuelve a detectarse de manera estable en formularios web y JSON.
- Los usuarios autenticados continúan aislados por su ID interno.
- Los clientes antiguos sin UUID conservan la sesión como parte de la huella de respaldo.

## Hotfix V5 — arranque HTTP y proxies confiables

- Se eliminó el uso prematuro de `config()` dentro de `bootstrap/app.php`.
- Se añadió `config/trustedproxy.php` para que `TrustProxies` lea `TRUSTED_PROXIES` durante la solicitud HTTP, cuando el repositorio de configuración ya está disponible.
- Se corrigió el error `Target class [config] does not exist` al iniciar con `php artisan serve` o un servidor web.
