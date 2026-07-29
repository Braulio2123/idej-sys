# Auditoría y correcciones Fase 36 — Estabilidad operativa IDEJ-SYS

## Alcance

Se tomó como base el ZIP `idej_sys1.0.zip` y el PDF de correcciones compartido por el usuario. En esta fase se deja pendiente el flujo de recordatorios/cobranza por correo y se corrigen errores operativos del sistema interno.

No se modificó Portal Alumno.

## Correcciones aplicadas

1. **Notificaciones internas con URL insegura o rota**
   - Se agregó `NotificacionInterna::urlSegura()` para evitar que una notificación redirija a rutas absolutas viejas, rutas con `/public/index.php` o instalaciones anteriores.
   - Las notificaciones nuevas generadas por comandos usan rutas relativas.
   - La campana y el listado usan URL segura.

2. **Cobranza por correo queda pendiente**
   - El menú ya no muestra “Cobranza por correo”.
   - La ruta se conserva, pero muestra que el módulo está pausado.
   - No intenta buscar candidatos ni enviar correos.
   - Se corrigió la causa técnica del `TypeError` en `RecordatorioPagoEmailService` quitando tipado incompatible en closures.

3. **Expediente documental del alumno**
   - El indicador del expediente ahora cuenta documentos entregados/en revisión/aceptados contra esperados.
   - Los aceptados siguen mostrándose aparte como validación final.
   - Se agrega explicación institucional para evitar confusión.

4. **Pagos en efectivo, cambio y saldo a favor**
   - Se agregan campos para efectivo recibido, cambio entregado, tratamiento de excedente y pago anticipado.
   - El sistema permite registrar anticipos o pagos futuros como saldo a favor sin seleccionar cargos.
   - Si se recibe más efectivo y se elige saldo a favor, el excedente se conserva como saldo a favor.
   - Si se elige cambio, el cambio queda registrado pero no aumenta ingresos.

5. **Caja, cortes y bitácora**
   - Se cambia lenguaje de bitácora de “caja #” a “corte de caja #”.
   - Los movimientos de caja muestran folio interno.
   - Se aclara que los movimientos cancelados permanecen como evidencia.
   - Se agrega aviso para evitar cuentas compartidas: cada operación queda vinculada al usuario que la realizó.

6. **Dashboard por rol**
   - Se agregan atajos principales visibles arriba del dashboard para reducir navegación y evitar depender de scroll.

7. **IVA en Educación Continua**
   - Los conceptos de Educación Continua aplican IVA 16% automáticamente por heurística de nombre del concepto.
   - Aplica en cargos individuales y cargos masivos.
   - Se registra en descripción y bitácora.

8. **Convenios**
   - Se agrega formato PDF institucional del convenio.
   - Las parcialidades vencidas se distinguen visualmente.
   - No se generan intereses automáticos porque falta regla institucional aprobada.

9. **Conversión de prospecto a alumno**
   - Se mejora el botón y mensaje de conversión.
   - Se abre el formulario automáticamente si hubo errores.
   - Se agregan mensajes claros para matrícula/correo duplicados.

10. **Solicitudes de pago docente**
   - Se corrige el error `Undefined variable $formatearFechaSolicitud` en `_form.blade.php`.
   - Se agregan conceptos por hora y por sesión.
   - El cálculo se valida del lado servidor: horas × tarifa o sesiones/clases × tarifa, según concepto.
   - Se evita que el monto capturado no coincida con los datos operativos.

## Migraciones nuevas

Sí hay migración nueva:

- `database/migrations/2026_07_07_000011_add_cash_and_advance_fields_to_pagos_table.php`

Agrega:

- `monto_recibido_efectivo`
- `cambio_entregado`
- `tratamiento_excedente`
- `es_pago_anticipado`

## Validación realizada

- `php -l` en archivos PHP del sistema interno: OK.
- `php artisan route:list`: OK, 251 rutas.
- `php artisan view:cache`: no pudo ejecutarse en el contenedor porque falta la extensión PHP `dom` (`DOMDocument`). Esto no es un error del proyecto; en Laragon debe estar activada.

## Cómo probar estos cambios

```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate
php artisan route:list
php artisan serve
npm run dev
```

Para instalación limpia:

```bash
php artisan migrate:fresh --seed
```

## Checklist manual

1. Abrir una notificación desde la campana y confirmar que no redirige a rutas antiguas ni muestra error técnico.
2. Entrar a `/cobranza/correos` y confirmar que aparece como módulo pendiente, sin fallar.
3. Ver ficha de alumno con documentos cargados y confirmar que el avance documental cuenta entregados/en revisión/aceptados.
4. Registrar pago en efectivo con monto recibido mayor y elegir “Entregar cambio”. Confirmar que no genera saldo a favor.
5. Registrar pago en efectivo con monto recibido mayor y elegir “Registrar excedente como saldo a favor”. Confirmar saldo a favor.
6. Registrar anticipo sin cargos seleccionados marcando “anticipo/saldo a favor”.
7. Abrir/cerrar caja y revisar que bitácora hable de “corte de caja”.
8. Registrar y cancelar movimiento de caja; debe verse el folio interno y conservar evidencia.
9. Entrar al dashboard con distintos roles y revisar atajos superiores.
10. Crear cargo de Educación Continua y revisar aplicación de IVA 16%.
11. Crear cargo masivo de Educación Continua y revisar monto con IVA.
12. Abrir convenio y generar formato PDF.
13. Revisar parcialidades vencidas en convenio.
14. Convertir prospecto a alumno y revisar mensajes claros si matrícula/correo están duplicados.
15. Crear solicitud de pago docente por hora, por clase o por sesión y revisar que monto coincida con tarifa.

## Advertencias de producción

- No usar `migrate:fresh` en producción.
- Antes de producir, respaldar base y storage privado.
- El IVA por Educación Continua se aplica por nombre del concepto; se recomienda crear un campo formal `aplica_iva` en una fase posterior si Finanzas quiere control manual por concepto.
- Los intereses/moratorios de convenios quedan pendientes hasta definir regla institucional.
- La cobranza por correo queda pausada temporalmente.
