# Auditoría y correcciones Fase 31 — Observaciones operativas IDEJ-SYS

## Alcance

Se corrigieron observaciones operativas detectadas en pruebas manuales del sistema interno IDEJ-SYS. No se modificó Portal Alumno.

## Puntos atendidos

1. **Login y mensajes técnicos**
   - Se corrigió el mensaje `auth.failed` por un mensaje institucional en español.
   - Se agregaron traducciones básicas de autenticación, recuperación de contraseña y validaciones en `lang/es`.
   - Las vistas de login, recuperación y restablecimiento de contraseña se ajustaron a lenguaje institucional.
   - La recuperación de contraseña queda funcional si el correo saliente está configurado; si no, Sistemas/Admin puede restablecer credenciales desde usuarios internos.

2. **Documentos sin checklist y mensajes técnicos**
   - Se corrigieron mensajes como `validation.required_without`.
   - Se agregaron mensajes personalizados para el expediente documental.
   - Se mantiene la opción de capturar documento manual o ligarlo a requisito de checklist.

3. **Documentos archivados**
   - Se agregó vista para consultar documentos archivados desde el expediente documental.
   - Los documentos archivados conservan evidencia y archivo privado.
   - Se permite descarga de documentos archivados mediante ruta protegida.

4. **Alumno suspendido o dado de baja**
   - Se agregó explicación institucional en edición y expediente.
   - La baja/suspensión no elimina el expediente ni bloquea pagos existentes.
   - Ahora bloquea nuevos cargos y nuevos convenios para alumnos no activos.
   - La eliminación física queda en una zona administrativa y se bloquea si hay historial.

5. **Corte de caja PDF**
   - Se reemplazó la impresión directa de página por PDF oficial de corte de caja.
   - El PDF solo se genera cuando la caja ya está cerrada.
   - Incluye resumen, pagos, entradas/salidas operativas y firmas.

6. **Entradas y salidas de caja**
   - Se creó el módulo de movimientos de caja para registrar entradas y salidas operativas.
   - Sirve para cambio, compras menores, agua, salidas autorizadas, entradas adicionales, etc.
   - Impacta el efectivo esperado y el total esperado al cerrar caja.
   - Los movimientos se pueden cancelar mientras la caja está abierta; después del cierre se conserva el histórico.

7. **Pago histórico y ajustes**
   - Se conserva la regla: si una caja ya cerró, no se modifica el corte histórico.
   - Cualquier corrección posterior debe documentarse como ajuste administrativo.

8. **Solicitud docente con autollenado**
   - Al seleccionar materia de calendario principal, el sistema autollenará docente, nivel, programa/grupo, periodo, materia/actividad, modalidad, número de sesiones, horas y fechas si están disponibles.
   - También se agregó autollenado para educación continua y sesiones de curso.
   - Se agregó normalización del lado servidor para que, aunque falle JavaScript, se completen datos desde el origen seleccionado cuando sea posible.

9. **Pago docente con campos según método**
   - Transferencia, cheque y tarjeta exigen referencia, banco/cuenta y comprobante.
   - Efectivo deja esos campos como opcionales.
   - Las etiquetas y ayudas cambian según el método elegido.

10. **Formato institucional de pago docente**
    - Se agregó PDF de formato/acuse de pago docente para solicitudes pagadas.
    - Incluye docente, servicio, monto, método, referencia, autorizaciones, procesado por y observaciones.

## Migraciones nuevas

Sí. Se agregó:

- `database/migrations/2026_07_07_000001_create_movimientos_caja_table.php`

## Archivos nuevos principales

- `app/Models/MovimientoCaja.php`
- `resources/views/cortes_caja/pdf.blade.php`
- `resources/views/solicitudes_pago/acuse_pdf.blade.php`
- `lang/es/auth.php`
- `lang/es/passwords.php`
- `lang/es/validation.php`

## Validación ejecutada

Se validó sintaxis PHP/Blade en sistema interno, excluyendo Portal Alumno.

`php artisan route:list` no se ejecutó dentro del contenedor porque el paquete limpio no incluye `vendor`. Debe ejecutarse localmente después de `composer install`.

## Advertencia de producción

Para bases existentes usar:

```bash
php artisan migrate
```

Para pruebas limpias locales puede usarse:

```bash
php artisan migrate:fresh --seed
```

