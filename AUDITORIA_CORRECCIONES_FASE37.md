# IDEJ-SYS — Auditoría y correcciones Fase 37

## Enfoque

Esta fase atiende correcciones operativas de Coordinación Académica antes de producción. No se trabajó Portal Alumno. No se retomaron correos, SMS ni WhatsApp; esos flujos quedan pendientes.

## Diagnóstico

El sistema técnicamente cargaba varios módulos, pero el flujo académico todavía tenía problemas institucionales:

- El término visible “Programas” no era correcto para Licenciatura, Maestría y Doctorado; debe verse como Educación Programática.
- Los grupos permitían capturar datos que no corresponden a Académica, como aula.
- Persistían referencias a cuatrimestres aunque IDEJ trabaja por semestres.
- La agenda operativa podía fallar al mezclar arreglos con colecciones Eloquent.
- Las solicitudes de pago docente obligaban a trabajar sesión por sesión en vez de permitir varias sesiones.
- Académica veía cálculo y montos de pago docente, aunque esa función corresponde a Coordinación Administrativa/Finanzas.
- Docentes no incluía documentos y datos bancarios útiles para pago.
- Días no laborales no distinguían suficientemente oficiales anuales vs institucionales.
- Requisitos documentales pedían orden manual.

## Correcciones aplicadas

### 1. Educación Programática

Se ajustaron textos visibles para que el módulo que antes aparecía como Programas se muestre como Educación Programática en vistas, menús, formularios, filtros y mensajes operativos.

Se conservan rutas/modelos/tablas técnicas `programas` para evitar romper dependencias internas.

### 2. Semestres

Se eliminó la referencia visible a cuatrimestres. La duración de Educación Programática se interpreta en semestres.

En grupos se valida que el semestre capturado no exceda la duración de la Educación Programática seleccionada.

### 3. Grupos

El formulario de grupos ahora guía al usuario si no hay Educación Programática registrada.

Se retiró aula del flujo de grupo, porque Sistemas debe asignar aulas/equipo posteriormente.

Se muestra horario institucional base: viernes 17:00-21:00 y sábado 08:00-13:00.

### 4. Ciclos escolares e inscripción

La conversión de prospecto a alumno valida periodo de inscripción cuando el grupo pertenece a Licenciatura, Maestría o Doctorado.

Si el periodo no está abierto, se bloquea la conversión con mensaje institucional.

### 5. Días no laborales

Se agregó comando anual:

```bash
php artisan idej:cargar-dias-no-laborales 2026
```

También se agregó botón en el módulo de días no laborales para cargar oficiales del año.

El sistema distingue día oficial por ley y día institucional. Al registrar/cargar días no laborales se detectan sesiones de Educación Programática y Educación Continua afectadas, generando notificaciones internas para las áreas correspondientes.

### 6. Educación Continua

Se agregó estatus operativo calculado por fechas:

- Planeado/Abierto antes de iniciar.
- En curso durante el periodo.
- Finalizado después de fecha fin.
- Cancelado se conserva manual.

El costo se controla por rol: Académica no debe ser responsable principal de capturarlo. Roles administrativos pueden capturarlo.

Aula/liga se restringe para asignación de Sistemas/Admin/CAdmin. Académica puede planear, pero no asignar aula como responsable principal.

La duplicidad de equipo se aclaró: equipo general del curso vs equipo específico por sesión.

### 7. Requisitos documentales

El campo orden ya no se captura manualmente. Se asigna automáticamente de 10 en 10.

Se actualizó el seeder con requisitos base:

- Acta de nacimiento
- CURP
- Identificación oficial
- Comprobante de domicilio
- Solicitud de inscripción
- Carta compromiso / Reglamento
- Certificado de Bachillerato
- Título profesional
- Cédula profesional
- Fotografía

### 8. Docentes

Se retiró domicilio del formulario operativo.

Se agregaron campos/documentos necesarios para pago docente:

- Banco
- Curriculum
- Título y cédula del último grado de estudios
- Constancia de situación fiscal

RFC queda limitado a 13 caracteres y se normaliza a mayúsculas.

### 9. Pagos que requieren seguimiento académico

Cuando se registra un pago asociado a constancias, credenciales, certificados parciales o titulación, el sistema genera notificación interna para Académica.

No se envía correo en esta fase.

### 10. Solicitudes de pago docente

Se actualizaron conceptos a:

- Educación continua - pago por horas
- Educación Programática - pago por nivel y materia

Se permite seleccionar varias sesiones de Educación Continua y varias sesiones de una materia de calendario.

Se autollenan datos de servicio:

- Docente
- Nivel
- Educación Programática/grupo
- Periodo
- Materia/actividad
- Modalidad
- Número de sesiones
- Horas totales
- Fechas de periodo

Académica no ve ni captura cálculo/monto/tarifa. CAdmin/Finanzas/Admin sí ven el bloque de cálculo.

### 11. Calendarios académicos

Se agregó estatus operativo automático:

- Agendado
- En curso
- Finalizado
- Cancelado manual

Se valida que las fechas del calendario respeten el periodo de clases del ciclo escolar.

### 12. Materias dentro del calendario

El formulario de añadir materia al calendario solo permite:

- Estatus: Confirmada, Impartida
- Tipo de sesión: Clase, Coloquio

Se retiró aula del flujo de Académica. Sistemas debe asignarla después.

### 13. Agenda Operativa

Se corrigió el error:

```text
Call to a member function getKey() on array
```

La causa era la mezcla de arrays dentro de colecciones Eloquent. Se convirtió la salida a colección base para poder unir eventos de calendario académico y Educación Continua sin romper.

## Migraciones nuevas

Sí hay migración nueva:

```text
database/migrations/2026_07_07_000012_fase37_coordinacion_academica.php
```

Agrega campos a docentes y solicitudes de pago docente.

## Validación realizada

```bash
php -l
php artisan route:list
```

Resultados:

- PHP lint: OK
- route:list: OK, 252 rutas

`php artisan view:cache` no pudo completarse en el contenedor por falta de extensión PHP `DOMDocument`, no por error de Blade.

## Portal Alumno

No se modificó Portal Alumno.

## Cómo probar estos cambios

Base existente:

```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate
php artisan route:list
php artisan serve
npm run dev
```

Instalación limpia:

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

## Checklist manual

1. Crear Educación Programática con duración en semestres.
2. Verificar que ya no aparezca Programas como nombre visible del módulo.
3. Crear grupo sin Educación Programática registrada y confirmar aviso/botón.
4. Crear grupo con Educación Programática y validar semestre máximo.
5. Confirmar que no se captura aula en grupos.
6. Confirmar horario institucional viernes/sábado.
7. Crear ciclo escolar y probar conversión de prospecto fuera de periodo de inscripción.
8. Cargar días no laborales oficiales del año.
9. Registrar día no laboral institucional sobre fecha con sesión y revisar notificación.
10. Crear requisito documental y confirmar orden 10, 20, 30.
11. Ejecutar seed y verificar requisitos base.
12. Crear docente con RFC en minúsculas y confirmar mayúsculas/máximo 13.
13. Subir documentos de docente.
14. Crear curso de Educación Continua y revisar costo según rol.
15. Crear sesiones de Educación Continua sin aula desde Académica.
16. Registrar pago de constancia/credencial/certificado/titulación y revisar notificación a Académica.
17. Crear solicitud docente desde calendario académico seleccionando varias sesiones.
18. Crear solicitud docente desde Educación Continua seleccionando varias sesiones.
19. Entrar como Académica y confirmar que no ve cálculo/monto.
20. Entrar como CAdmin/Finanzas/Admin y confirmar que sí ve cálculo/monto.
21. Crear calendario académico fuera del periodo de clases y confirmar bloqueo.
22. Añadir materia a calendario y confirmar solo Clase/Coloquio.
23. Entrar a /agenda-operativa y confirmar que no da error 500.

## Advertencias de producción

- No usar `migrate:fresh` en producción.
- Revisar tarifas docentes institucionales antes de automatizar pagos.
- Portal docente queda fuera de esta fase.
- Correos, SMS y WhatsApp quedan pendientes.
- Validar con datos reales la regla de inscripción por ciclo antes de producción.
