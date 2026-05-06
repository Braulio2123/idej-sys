# Fase Local 16 — Configuración institucional del sistema

## Objetivo

Centralizar datos institucionales y parámetros operativos que antes estaban fijos en código o en vistas. Esta fase permite que IDEJ-SYS pueda adaptar recibos, encabezados, datos de contacto, logo, leyendas y reglas básicas sin modificar archivos fuente.

## Componentes agregados

- Tabla `configuraciones_institucionales`.
- Modelo `App\Models\ConfiguracionInstitucional`.
- Seeder `ConfiguracionInstitucionalSeeder`.
- Controlador `ConfiguracionInstitucionalController`.
- Vista `resources/views/configuracion/institucional.blade.php`.
- Helpers globales:
  - `configuracionInstitucional()`
  - `configInstitucional()`
  - `logoInstitucionalUrl()`
  - `logoInstitucionalPathPdf()`

## Datos configurables

### Identidad

- Nombre completo de la institución.
- Nombre corto.
- Razón social.
- RFC.
- Lema institucional.
- Logo personalizado.

### Contacto

- Domicilio.
- Colonia.
- Municipio.
- Estado.
- Código postal.
- Teléfonos.
- Correo de contacto.
- Sitio web.

### Recibos

- Prefijo de folio.
- Leyenda del recibo.
- Nota fiscal/administrativa.
- Texto de firmas.
- Mostrar u ocultar logo.

### Operación

- Moneda.
- Zona horaria.
- Porcentaje de moratorio.
- Días de gracia para moratorios.
- Activación/desactivación de recordatorios de pago.

## Integraciones realizadas

- Layout principal toma logo, nombre corto y lema desde configuración.
- Layout de login toma logo y nombre corto desde configuración.
- Recibos PDF toman institución, logo, dirección, contacto, leyendas y firmas desde configuración.
- Reporte PDF financiero toma el nombre institucional desde configuración.
- Reporte PDF de bitácora toma logo y nombre institucional desde configuración.
- Correos de recordatorio toman nombre institucional desde configuración.
- Folios nuevos de recibos usan el prefijo configurable.
- Comando de moratorios usa porcentaje y días de gracia configurables.
- Comando de recordatorios respeta si están activos o desactivados.

## Seguridad y permisos

El módulo queda disponible para:

- Administrador IDEJ.
- Sistemas IDEJ.

Ruta:

```txt
/configuracion/institucional
```

## Notas operativas

Después de aplicar esta fase se debe ejecutar:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=ConfiguracionInstitucionalSeeder
php artisan storage:link
```

Si ya se ejecuta `migrate:fresh --seed`, el seeder general también cargará la configuración base.

## Pendientes futuros recomendados

- Convertir parámetros por módulo a una pantalla separada si crecen demasiado.
- Agregar configuración de correo SMTP desde interfaz solo si se cifra correctamente.
- Agregar configuración de políticas de contraseñas.
- Agregar módulo de respaldos y mantenimiento.
