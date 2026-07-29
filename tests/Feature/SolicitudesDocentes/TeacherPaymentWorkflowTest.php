<?php

namespace Tests\Feature\SolicitudesDocentes;

use App\Models\Docente;
use App\Models\NotificacionInterna;
use App\Models\Rol;
use App\Models\SolicitudPagoDocente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['cache.default' => 'array', 'idej_operations.cache_store' => 'array']);
    }

    public function test_academica_registers_classes_and_cadmin_values_programs_and_pays(): void
    {
        [$academica, $cadmin, $docente] = $this->actors();

        $this->actingAs($academica)->post(route('solicitudes_pago.store'), [
            '_idempotency_key' => '31d6811c-c6ed-4e8e-b8fc-f54179319b51',
            'docente_id' => $docente->id,
            'tipo_clase' => SolicitudPagoDocente::TIPO_MAESTRIA,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'fechas_clase' => [today()->subDays(14)->toDateString(), today()->subDays(7)->toDateString()],
            'materia_actividad' => 'Seminario de investigación',
            'programa_grupo' => 'Maestría · Grupo 2-A',
            'periodo' => '2026 B',
            'modalidad' => 'Presencial',
            'horas_totales' => 8,
            'observaciones_academica' => 'Clases impartidas y verificadas por coordinación.',
            // Intento malicioso: estos campos no forman parte del formulario académico y deben ignorarse.
            'monto' => 999999,
            'tarifa_unitaria' => 999999,
            'fecha_tentativa_pago' => today()->addDay()->toDateString(),
        ])->assertRedirect();

        $solicitud = SolicitudPagoDocente::firstOrFail();
        $this->assertSame(SolicitudPagoDocente::ESTATUS_PENDIENTE, $solicitud->estatus);
        $this->assertSame('0.00', $solicitud->monto);
        $this->assertNull($solicitud->tarifa_unitaria);
        $this->assertNull($solicitud->fecha_tentativa_pago);
        $this->assertCount(2, $solicitud->fechas_clase);
        $this->assertDatabaseHas('notificaciones_internas', [
            'rol_clave' => Rol::CADMIN,
            'tipo' => 'solicitud_docente_nueva',
            'referencia_id' => $solicitud->id,
        ]);

        $tentativa = today()->addDays(10)->toDateString();
        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.valorar', $solicitud), [
                '_idempotency_key' => 'a75c4f0e-2099-471f-a223-4f936f0cb0a1',
                'esquema_pago' => SolicitudPagoDocente::ESQUEMA_SESION,
                'tarifa_unitaria' => 2500,
                // El servidor debe ignorar un total alterado y recalcular 2 × 2,500.
                'monto' => 4,
                'fecha_tentativa_pago' => $tentativa,
                'prioridad' => 'Normal',
                'observaciones_administracion' => 'Pago sujeto a disponibilidad administrativa.',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $solicitud->refresh();
        $this->assertSame(SolicitudPagoDocente::ESTATUS_AUTORIZADA, $solicitud->estatus);
        $this->assertSame('5000.00', $solicitud->monto);
        $this->assertSame($tentativa, $solicitud->fecha_tentativa_pago->toDateString());
        $this->assertSame($cadmin->id, $solicitud->valorado_por_id);
        $this->assertDatabaseHas('notificaciones_internas', [
            'rol_clave' => Rol::ACADEMICA,
            'tipo' => 'solicitud_docente_valorada',
            'referencia_id' => $solicitud->id,
        ]);

        $respuestaAcademica = $this->actingAs($academica)->get(route('solicitudes_pago.show', $solicitud));
        $respuestaAcademica->assertOk()->assertSee(today()->addDays(10)->format('d/m/Y'));
        $respuestaAcademica->assertDontSee('$5,000.00', false);
        $respuestaAcademica->assertDontSee('Tarifa unitaria');

        $nuevaTentativa = today()->addDays(14)->toDateString();
        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.tentativa', $solicitud), [
                '_idempotency_key' => '768f359d-1e84-4953-b771-196376e75efe',
                'fecha_tentativa_pago' => $nuevaTentativa,
                'observaciones_administracion' => 'Tentativa actualizada por disponibilidad administrativa.',
            ])->assertRedirect();

        $this->assertSame($nuevaTentativa, $solicitud->fresh()->fecha_tentativa_pago->toDateString());
        $this->assertDatabaseHas('notificaciones_internas', [
            'rol_clave' => Rol::ACADEMICA,
            'tipo' => 'solicitud_docente_tentativa_actualizada',
            'referencia_id' => $solicitud->id,
        ]);

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.pagar', $solicitud), [
                '_idempotency_key' => 'f3cf82a9-2e39-49e3-bf29-ea8b20cd5acb',
                'pago_operacion_uuid' => 'ff853086-c06f-4834-a2e5-7ce0e2247b25',
                'fecha_pago' => today()->toDateString(),
                'metodo_pago' => 'Efectivo',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $this->assertSame(SolicitudPagoDocente::ESTATUS_PAGADA, $solicitud->fresh()->estatus);
        $this->assertDatabaseHas('notificaciones_internas', [
            'rol_clave' => Rol::ACADEMICA,
            'tipo' => 'solicitud_docente_pagada',
            'referencia_id' => $solicitud->id,
        ]);
    }

    public function test_cadmin_can_reject_without_academica_assigning_amounts(): void
    {
        [$academica, $cadmin, $docente] = $this->actors();

        $solicitud = SolicitudPagoDocente::create([
            'folio' => 'SPD-TEST-000001',
            'docente_id' => $docente->id,
            'creado_por_id' => $academica->id,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'tipo_clase' => SolicitudPagoDocente::TIPO_DIPLOMADO,
            'nivel' => SolicitudPagoDocente::TIPO_DIPLOMADO,
            'fechas_clase' => [today()->subDay()->toDateString()],
            'materia_actividad' => 'Diplomado práctico',
            'numero_sesiones' => 1,
            'fecha_solicitud' => today(),
            'fecha_inicio_periodo' => today()->subDay(),
            'fecha_fin_periodo' => today()->subDay(),
            'monto' => 0,
            'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
        ]);

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.rechazar', $solicitud), [
                '_idempotency_key' => '9e2d9009-a01f-4a86-b5bf-6d69310e7adf',
                'motivo_rechazo' => 'La actividad no cumple con los criterios administrativos para pago.',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $solicitud->refresh();
        $this->assertSame(SolicitudPagoDocente::ESTATUS_RECHAZADA, $solicitud->estatus);
        $this->assertSame('0.00', $solicitud->monto);
        $this->assertSame($cadmin->id, $solicitud->rechazado_por_id);
        $this->assertDatabaseHas('notificaciones_internas', [
            'rol_clave' => Rol::ACADEMICA,
            'tipo' => 'solicitud_docente_rechazada',
            'referencia_id' => $solicitud->id,
        ]);
    }


    public function test_academica_cannot_value_or_assign_payment_data(): void
    {
        [$academica, , $docente] = $this->actors();

        $solicitud = SolicitudPagoDocente::create([
            'folio' => 'SPD-TEST-000002',
            'docente_id' => $docente->id,
            'creado_por_id' => $academica->id,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'tipo_clase' => SolicitudPagoDocente::TIPO_LICENCIATURA,
            'nivel' => SolicitudPagoDocente::TIPO_LICENCIATURA,
            'fechas_clase' => [today()->subDay()->toDateString()],
            'materia_actividad' => 'Clase de licenciatura',
            'numero_sesiones' => 1,
            'fecha_solicitud' => today(),
            'fecha_inicio_periodo' => today()->subDay(),
            'fecha_fin_periodo' => today()->subDay(),
            'monto' => 0,
            'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
        ]);

        $this->actingAs($academica)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.valorar', $solicitud), [
                '_idempotency_key' => '5815a678-2a55-4b48-a24a-5c98d73cf5c3',
                'esquema_pago' => SolicitudPagoDocente::ESQUEMA_FIJO,
                'monto' => 10000,
                'fecha_tentativa_pago' => today()->addWeek()->toDateString(),
                'prioridad' => 'Normal',
            ])->assertForbidden();

        $solicitud->refresh();
        $this->assertSame('0.00', $solicitud->monto);
        $this->assertNull($solicitud->fecha_tentativa_pago);
        $this->assertSame(SolicitudPagoDocente::ESTATUS_PENDIENTE, $solicitud->estatus);
    }


    public function test_cadmin_recalculates_one_session_at_450_even_if_browser_sends_four(): void
    {
        [$academica, $cadmin, $docente] = $this->actors();

        $solicitud = SolicitudPagoDocente::create([
            'folio' => 'SPD-TEST-CALC-001',
            'docente_id' => $docente->id,
            'creado_por_id' => $academica->id,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'tipo_clase' => SolicitudPagoDocente::TIPO_DOCTORADO,
            'nivel' => SolicitudPagoDocente::TIPO_DOCTORADO,
            'fechas_clase' => [today()->subDay()->toDateString()],
            'materia_actividad' => 'Sesión MASC',
            'numero_sesiones' => 1,
            'horas_totales' => 4.45,
            'fecha_solicitud' => today(),
            'fecha_inicio_periodo' => today()->subDay(),
            'fecha_fin_periodo' => today()->subDay(),
            'monto' => 0,
            'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
        ]);

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.valorar', $solicitud), [
                '_idempotency_key' => '5477bbcb-74cf-4da0-8d0a-59b92224286c',
                'esquema_pago' => SolicitudPagoDocente::ESQUEMA_SESION,
                'tarifa_unitaria' => 450,
                'monto' => 4,
                'fecha_tentativa_pago' => today()->addWeek()->toDateString(),
                'prioridad' => 'Normal',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $solicitud->refresh();
        $this->assertSame('450.00', $solicitud->monto);
        $this->assertSame('450.00', $solicitud->tarifa_unitaria);
        $this->assertSame(SolicitudPagoDocente::ESTATUS_AUTORIZADA, $solicitud->estatus);
    }

    public function test_hourly_payment_uses_reported_hours_and_rounds_to_cents(): void
    {
        [$academica, $cadmin, $docente] = $this->actors();

        $solicitud = SolicitudPagoDocente::create([
            'folio' => 'SPD-TEST-CALC-002',
            'docente_id' => $docente->id,
            'creado_por_id' => $academica->id,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'tipo_clase' => SolicitudPagoDocente::TIPO_DIPLOMADO,
            'nivel' => SolicitudPagoDocente::TIPO_DIPLOMADO,
            'fechas_clase' => [today()->subDay()->toDateString()],
            'materia_actividad' => 'Taller práctico',
            'numero_sesiones' => 1,
            'horas_totales' => 4.45,
            'fecha_solicitud' => today(),
            'fecha_inicio_periodo' => today()->subDay(),
            'fecha_fin_periodo' => today()->subDay(),
            'monto' => 0,
            'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
        ]);

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.valorar', $solicitud), [
                '_idempotency_key' => 'fcb47d87-2615-48a5-b8d1-2643c72066a8',
                'esquema_pago' => SolicitudPagoDocente::ESQUEMA_HORA,
                'tarifa_unitaria' => 450,
                'monto' => 4,
                'fecha_tentativa_pago' => today()->addWeek()->toDateString(),
                'prioridad' => 'Normal',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $solicitud->refresh();
        $this->assertSame('2002.50', $solicitud->monto);
        $this->assertSame('450.00', $solicitud->tarifa_hora);
    }

    public function test_fixed_payment_keeps_manual_total_and_clears_unit_rate(): void
    {
        [$academica, $cadmin, $docente] = $this->actors();

        $solicitud = SolicitudPagoDocente::create([
            'folio' => 'SPD-TEST-CALC-003',
            'docente_id' => $docente->id,
            'creado_por_id' => $academica->id,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'tipo_clase' => SolicitudPagoDocente::TIPO_CURSO,
            'nivel' => SolicitudPagoDocente::TIPO_CURSO,
            'fechas_clase' => [today()->subDay()->toDateString()],
            'materia_actividad' => 'Curso especializado',
            'numero_sesiones' => 1,
            'fecha_solicitud' => today(),
            'fecha_inicio_periodo' => today()->subDay(),
            'fecha_fin_periodo' => today()->subDay(),
            'monto' => 0,
            'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
        ]);

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.valorar', $solicitud), [
                '_idempotency_key' => '72bbdd82-5bfb-47b8-93b7-b1fa06ee82c9',
                'esquema_pago' => SolicitudPagoDocente::ESQUEMA_FIJO,
                'tarifa_unitaria' => 450,
                'monto' => 1750.25,
                'fecha_tentativa_pago' => today()->addWeek()->toDateString(),
                'prioridad' => 'Normal',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $solicitud->refresh();
        $this->assertSame('1750.25', $solicitud->monto);
        $this->assertNull($solicitud->tarifa_unitaria);
        $this->assertNull($solicitud->tarifa_hora);
    }


    public function test_cadmin_can_correct_an_authorized_but_unpaid_valuation(): void
    {
        [$academica, $cadmin, $docente] = $this->actors();

        $solicitud = SolicitudPagoDocente::create([
            'folio' => 'SPD-TEST-CALC-004',
            'docente_id' => $docente->id,
            'creado_por_id' => $academica->id,
            'valorado_por_id' => $cadmin->id,
            'autorizado_por_id' => $cadmin->id,
            'origen' => SolicitudPagoDocente::ORIGEN_MANUAL,
            'tipo_clase' => SolicitudPagoDocente::TIPO_DOCTORADO,
            'nivel' => SolicitudPagoDocente::TIPO_DOCTORADO,
            'fechas_clase' => [today()->subDay()->toDateString()],
            'materia_actividad' => 'Sesión MASC',
            'numero_sesiones' => 1,
            'horas_totales' => 4.45,
            'esquema_pago' => SolicitudPagoDocente::ESQUEMA_SESION,
            'tarifa_unitaria' => 450,
            'monto' => 4,
            'fecha_solicitud' => today(),
            'fecha_inicio_periodo' => today()->subDay(),
            'fecha_fin_periodo' => today()->subDay(),
            'fecha_tentativa_pago' => today()->addWeek(),
            'fecha_autorizacion' => now(),
            'fecha_valoracion' => now(),
            'prioridad' => 'Normal',
            'estatus' => SolicitudPagoDocente::ESTATUS_AUTORIZADA,
        ]);

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('solicitudes_pago.valorar.form', $solicitud))
            ->assertOk()
            ->assertSee('Corregir valoración');

        $this->actingAs($cadmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('solicitudes_pago.valorar', $solicitud), [
                '_idempotency_key' => '03059702-90c0-423c-a3f6-604c04d39a17',
                'esquema_pago' => SolicitudPagoDocente::ESQUEMA_SESION,
                'tarifa_unitaria' => 450,
                'monto' => 4,
                'fecha_tentativa_pago' => today()->addDays(9)->toDateString(),
                'prioridad' => 'Normal',
            ])->assertRedirect(route('solicitudes_pago.show', $solicitud));

        $this->assertSame('450.00', $solicitud->fresh()->monto);
        $this->assertDatabaseHas('notificaciones_internas', [
            'rol_clave' => Rol::ACADEMICA,
            'tipo' => 'solicitud_docente_valoracion_corregida',
            'referencia_id' => $solicitud->id,
        ]);
    }

    private function actors(): array
    {
        $rolAcademica = Rol::firstOrCreate(['clave' => Rol::ACADEMICA], ['nombre' => 'Coordinación Académica']);
        $rolCAdmin = Rol::firstOrCreate(['clave' => Rol::CADMIN], ['nombre' => 'Coordinación Administrativa']);

        $academica = Usuario::factory()->create(['rol_id' => $rolAcademica->id]);
        $cadmin = Usuario::factory()->create(['rol_id' => $rolCAdmin->id]);
        $docente = Docente::create([
            'nombre_completo' => 'Docente de Prueba',
            'email' => 'docente.prueba@idej.test',
            'area_especialidad' => 'Derecho',
            'creado_por_id' => $academica->id,
            'estatus' => Docente::ESTATUS_ACTIVO,
        ]);

        return [$academica, $cadmin, $docente];
    }
}
