# AUDITORÍA Y CORRECCIONES FASE 35 — Cargos recurrentes y cobranza por correo

## Alcance

Se atendió la decisión operativa: **no usar SMS ni WhatsApp** en esta fase. La automatización de cobranza queda limitada a **correo electrónico institucional**.

Portal Alumno no fue modificado.

## Diagnóstico

La Fase 34 dejó preparada una estructura general para recordatorios por canal, pero para IDEJ en este momento conviene reducir el alcance para evitar costos externos, dependencias con proveedores, consentimiento de WhatsApp/SMS y complejidad de soporte. También faltaba una forma operativa de generar colegiaturas mensuales sin capturar manualmente cargos masivos cada mes.

Adicionalmente, en pruebas locales el comando podía responder que los recordatorios estaban desactivados porque dependen de dos condiciones:

1. Configuración institucional: `recordatorios_pago_activos`.
2. `.env`: `IDEJ_RECORDATORIOS_EMAIL=true` e `IDEJ_RECORDATORIOS_ADEUDO_ACTIVOS=true`.

## Correcciones realizadas

### 1. Recordatorios únicamente por correo

Se retiró la lógica operativa de SMS/WhatsApp del flujo de recordatorios. El comando `app:enviar-recordatorios` conserva la opción `--canal=email` por compatibilidad, pero si se intenta usar otro canal informa que esta fase solo permite correo.

Comando principal:

```bash
php artisan app:enviar-recordatorios --canal=email --limite=100
```

Prueba sin enviar:

```bash
php artisan app:enviar-recordatorios --canal=email --limite=10 --dry-run
```

También se agregó:

```bash
php artisan idej:activar-recordatorios-email
```

Este comando activa los recordatorios en Configuración institucional cuando localmente aparece el aviso de que están desactivados.

### 2. Campaña de cobranza por correo

Se agregó el módulo visual:

```text
/cobranza/correos
```

Permite:

- Ver alumnos candidatos a recordatorio.
- Filtrar por programa.
- Filtrar por grupo.
- Filtrar solo vencidos.
- Simular envío antes de enviar.
- Enviar correos reales cuando el correo esté configurado.

El envío real requiere confirmación de contraseña porque es una acción financiera sensible.

### 3. Plantilla institucional de correo

Se mejoró el correo de recordatorio con:

- Encabezado institucional.
- Nombre del alumno.
- Saldo pendiente aproximado.
- Primer vencimiento.
- Tabla de cargos pendientes.
- Mensaje claro para contactar Recepción/Finanzas.
- Nota de control administrativo.

### 4. Cargos recurrentes

Se agregó el módulo:

```text
/cargos/recurrentes
```

Permite crear planes para generar cargos periódicos, por ejemplo colegiaturas mensuales.

Campos principales:

- Nombre del plan.
- Concepto de pago.
- Alcance: grupo, programa o todos los alumnos activos.
- Monto especial opcional.
- Día de vencimiento.
- Frecuencia: mensual, bimestral, trimestral, semestral o anual.
- Fecha de inicio y fin.
- Estado activo/inactivo.
- Inclusión en recordatorios por correo.

Regla crítica: el sistema evita cargos duplicados por **plan + alumno + periodo**.

### 5. Generación automática por scheduler

Nuevo comando:

```bash
php artisan app:generar-cargos-recurrentes
```

Prueba sin crear cargos:

```bash
php artisan app:generar-cargos-recurrentes --dry-run
```

Ejecutar un plan específico:

```bash
php artisan app:generar-cargos-recurrentes --plan=1
```

Probar un periodo específico:

```bash
php artisan app:generar-cargos-recurrentes --fecha=2026-08-01 --dry-run
```

### 6. Scheduler

Se configuró el orden lógico:

1. Generar cargos recurrentes temprano.
2. Enviar recordatorios por correo después.
3. Aplicar moratorios.
4. Sincronizar notificaciones operativas.

En producción debe existir cron:

```bash
* * * * * cd /ruta/idej-sys && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Limpieza de interfaz de usuarios

Se ocultaron campos de SMS/WhatsApp en creación y edición de usuarios para no confundir al equipo. Los avisos automáticos quedan institucionalmente por correo.

## Migraciones nuevas

Sí hay migraciones nuevas:

```text
2026_07_07_000008_create_planes_cargos_recurrentes_table.php
2026_07_07_000009_create_cargo_recurrente_ejecuciones_table.php
2026_07_07_000010_add_recurring_fields_to_cargos_table.php
```

## Archivos principales modificados

```text
.env.example
bootstrap/app.php
config/idej_recordatorios.php
routes/console.php
routes/web.php
app/Console/Commands/ActivarRecordatoriosEmail.php
app/Console/Commands/EnviarRecordatoriosPago.php
app/Console/Commands/GenerarCargosRecurrentes.php
app/Http/Controllers/CobranzaEmailController.php
app/Http/Controllers/PlanCargoRecurrenteController.php
app/Http/Controllers/UsuarioController.php
app/Mail/RecordatorioPago.php
app/Models/Cargo.php
app/Models/CargoRecurrenteEjecucion.php
app/Models/ConfiguracionInstitucional.php
app/Models/PlanCargoRecurrente.php
app/Services/CargosRecurrentesService.php
app/Services/RecordatorioPagoEmailService.php
database/seeders/DatabaseSeeder.php
database/seeders/PlanCargoRecurrenteSeeder.php
resources/views/cargos/recurrentes/*
resources/views/cobranza/correos/index.blade.php
resources/views/emails/recordatorio-pago.blade.php
resources/views/layouts/app.blade.php
resources/views/usuarios/create.blade.php
resources/views/usuarios/edit.blade.php
```

Se eliminó del flujo interno:

```text
app/Services/CanalNotificacionExternaService.php
```

## Cómo probar estos cambios

### Base existente

```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate
php artisan idej:activar-recordatorios-email
php artisan route:list
php artisan serve
npm run dev
```

### Instalación limpia

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

### Configuración local para probar correos sin enviar correos reales

En `.env`:

```env
MAIL_MAILER=log
IDEJ_RECORDATORIOS_ADEUDO_ACTIVOS=true
IDEJ_RECORDATORIOS_EMAIL=true
IDEJ_RECORDATORIOS_DIAS_ANTES=3
IDEJ_RECORDATORIOS_DIAS_DESPUES=30
IDEJ_RECORDATORIOS_LIMITE_DIARIO=150
IDEJ_RECORDATORIOS_HORA=09:00
IDEJ_CARGOS_RECURRENTES_ACTIVOS=true
IDEJ_CARGOS_RECURRENTES_HORA=06:30
```

Después ejecuta:

```bash
php artisan optimize:clear
php artisan idej:activar-recordatorios-email
php artisan app:enviar-recordatorios --canal=email --limite=10 --dry-run
php artisan app:enviar-recordatorios --canal=email --limite=10
```

Revisa:

```text
storage/logs/laravel.log
```

### Probar cargos recurrentes

1. Entrar con Admin, CAdmin o Finanzas.
2. Ir a Finanzas → Cargos recurrentes.
3. Crear plan de colegiatura mensual para un grupo.
4. Presionar “Simular”.
5. Si el resultado es correcto, presionar “Generar ahora”.
6. Verificar en expediente del alumno que aparezca el cargo.
7. Ejecutar otra vez el mismo plan en el mismo periodo: debe omitir duplicados.

### Probar campaña de cobranza por correo

1. Ir a Finanzas → Cobranza por correo.
2. Confirmar que aparecen alumnos con cargos pendientes.
3. Mantener activado “Simular primero”.
4. Procesar correos.
5. Desactivar “Simular primero” solo cuando `MAIL_MAILER=log` o SMTP real esté correctamente configurado.
6. Revisar `storage/logs/laravel.log` si estás en local.

## Checklist manual

- Crear plan recurrente por grupo.
- Crear plan recurrente por programa.
- Simular generación.
- Generar cargos reales.
- Repetir generación en el mismo periodo y confirmar que no duplica.
- Confirmar que alumnos suspendidos/baja no reciban cargos recurrentes.
- Confirmar que las becas se aplican en conceptos becables.
- Confirmar que el alumno queda “Con Adeudo” si se genera cargo con saldo pendiente.
- Confirmar que cobranza por correo lista cargos vencidos/próximos.
- Confirmar que `--dry-run` no registra envíos.
- Confirmar que el envío real con `MAIL_MAILER=log` aparece en `storage/logs/laravel.log`.
- Confirmar que el comando rechaza canales diferentes a email.

## Advertencias de producción

- No activar SMTP real sin revisar plantilla, destinatarios y datos de alumnos.
- No correr `migrate:fresh` en producción.
- Configurar cron para scheduler.
- Empezar con `--dry-run` y límites bajos.
- Si se usará correo real, configurar SPF/DKIM/DMARC del dominio para evitar spam.
- Revisar consentimiento/aviso de privacidad para comunicaciones administrativas.

## Siguiente fase recomendada

Fase 36 — Conciliación y control de cobranza:

- Estado de cuenta por alumno en PDF.
- Historial de correos enviados por alumno.
- Confirmación manual de contacto realizado.
- Promesas de pago con fecha compromiso.
- Reporte de recuperación de cartera.
- Separar adeudo vencido, por vencer, en convenio y en aclaración.
