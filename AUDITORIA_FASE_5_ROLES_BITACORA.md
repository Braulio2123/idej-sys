# Auditoría Fase 5 — Roles, permisos y bitácora

## Objetivo
Formalizar la base de control de acceso y convertir la bitácora en una auditoría útil para IDEJ-SYS.

## Cambios aplicados

### Roles
Se agregaron claves internas para evitar comparar permisos por nombre largo del rol.

Roles operativos definidos:

- `Admin` — Administrador IDEJ
- `Sistemas` — Sistemas IDEJ
- `Direccion` — Dirección IDEJ
- `CAdmin` — Coordinación Administrativa IDEJ
- `Academica` — Coordinación Académica IDEJ
- `Recepcion` — Recepción IDEJ
- `RRPP` — Relaciones Públicas IDEJ
- `Finanzas` — Finanzas IDEJ

### Middleware de roles
El middleware `rol` ahora evalúa la columna `clave` del rol y no depende de un mapeo rígido por nombre.

Ejemplo:

```php
Route::middleware('rol:Admin,CAdmin,Finanzas')->group(function () {
    // rutas protegidas
});
```

### Gates
Se agregaron Gates para permisos comunes:

- `es-admin`
- `es-sistemas`
- `es-direccion`
- `es-cadmin`
- `es-recepcion`
- `es-academica`
- `es-finanzas`
- `puede-ver-alumnos`
- `puede-ver-finanzas`
- `puede-administrar-usuarios`
- `puede-ver-bitacora`

### Modelo Usuario
Se agregaron métodos utilitarios:

- `rolClave()`
- `tieneRol()`
- `esAdmin()`
- `esSistemas()`

### Bitácora
La tabla de bitácoras ahora incluye campos de auditoría real:

- `accion`
- `modulo`
- `modelo_type`
- `modelo_id`
- `ip_address`
- `user_agent`
- `url`
- `metodo_http`
- `fecha_evento`

Se conserva `tipo` como campo heredado para compatibilidad, pero el sistema ya no lo usa como fuente principal.

### Trait RegistraBitacora
El trait ahora guarda:

- Usuario que ejecutó la acción
- Acción real
- Módulo inferido
- Descripción
- IP
- User Agent
- URL
- Método HTTP
- Fecha del evento

### Rutas
Se reorganizaron rutas por permisos:

- Usuarios: `Admin`, `Sistemas`
- Alumnos consulta: `Admin`, `Recepcion`, `CAdmin`, `Finanzas`, `RRPP`, `Direccion`
- Alumnos modificación: `Admin`, `Recepcion`, `CAdmin`
- Cargos/pagos/convenios: `Admin`, `Recepcion`, `CAdmin`, `Finanzas`
- Conceptos/cargos masivos: `Admin`, `CAdmin`, `Finanzas`
- Reportes: `Admin`, `CAdmin`, `Finanzas`, `Direccion`
- Académica/docentes/grupos/programas: `Admin`, `CAdmin`, `Academica`
- Bitácora: `Admin`, `Sistemas`, `Direccion`

### Vistas
Se ajustó el menú lateral para que muestre módulos según rol.

También se corrigieron vistas que seguían usando campos inconsistentes como `usuario->name`, `bitacora->user` o `bitacora->accion` sin respaldo de base de datos.

## Recomendación de prueba
Para una validación limpia ejecutar:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
npm run dev
```

## Usuarios de prueba

- `admin@idej.test` / `admin123`
- `sistemas@idej.test` / `sistemas123`
- `direccion@idej.test` / `direccion123`
- `cadmin@idej.test` / `cadmin123`
- `academica@idej.test` / `academica123`
- `recepcion@idej.test` / `recepcion123`
- `rrpp@idej.test` / `rrpp123`
- `finanzas@idej.test` / `finanzas123`

## Pendiente después de esta fase
La API pública sigue siendo el riesgo principal. La siguiente fase debe implementar autenticación con Sanctum y proteger rutas móviles/API.
