<?php

namespace Tests\Unit;

use App\Models\CorteCaja;
use App\Models\Rol;
use App\Models\Usuario;
use Tests\TestCase;

class AuthorizationBoundariesTest extends TestCase
{
    public function test_sistemas_cannot_manage_credentials_of_admin_or_sistemas(): void
    {
        $sistemas = $this->usuarioConRol(10, Rol::SISTEMAS);
        $admin = $this->usuarioConRol(11, Rol::ADMIN);
        $otroSistemas = $this->usuarioConRol(12, Rol::SISTEMAS);
        $recepcion = $this->usuarioConRol(13, Rol::RECEPCION);

        $this->assertFalse($sistemas->puedeGestionarCredencialesDe($admin));
        $this->assertFalse($sistemas->puedeGestionarCredencialesDe($otroSistemas));
        $this->assertFalse($sistemas->puedeGestionarCredencialesDe($recepcion));
    }

    public function test_admin_can_manage_credentials_of_any_internal_user(): void
    {
        $admin = $this->usuarioConRol(1, Rol::ADMIN);
        $sistemas = $this->usuarioConRol(2, Rol::SISTEMAS);

        $this->assertTrue($admin->puedeGestionarCredencialesDe($sistemas));
    }

    public function test_recepcion_can_only_operate_its_own_cash_register(): void
    {
        $recepcion = $this->usuarioConRol(20, Rol::RECEPCION);
        $propia = $this->cajaDeUsuario(100, 20);
        $ajena = $this->cajaDeUsuario(101, 21);

        $this->assertTrue($recepcion->puedeOperarCaja($propia));
        $this->assertFalse($recepcion->puedeOperarCaja($ajena));
        $this->assertFalse($recepcion->puedeSupervisarCajas());
    }

    public function test_cadmin_and_admin_can_supervise_other_users_cash_registers(): void
    {
        $cajaAjena = $this->cajaDeUsuario(200, 99);
        $id = 1000;

        foreach ([Rol::CADMIN, Rol::ADMIN] as $clave) {
            $usuario = $this->usuarioConRol($id++, $clave);

            $this->assertTrue($usuario->puedeSupervisarCajas());
            $this->assertTrue($usuario->puedeOperarCaja($cajaAjena));
        }
    }


    public function test_permissions_with_dots_are_resolved_as_literal_keys(): void
    {
        $cadmin = $this->usuarioConRol(30, Rol::CADMIN);
        $sistemas = $this->usuarioConRol(31, Rol::SISTEMAS);

        $this->assertTrue($cadmin->tienePermiso('caja.ver'));
        $this->assertFalse($sistemas->tienePermiso('caja.ver'));
        $this->assertSame([Rol::ADMIN], rolesParaPermiso('configuracion.editar'));
    }


    public function test_finanzas_role_is_retired_and_cadmin_absorbs_financial_permissions(): void
    {
        $this->assertNotContains('Finanzas', config('idej_permisos.roles', []));
        $this->assertContains(Rol::CADMIN, rolesParaPermiso('pagos.cancelar'));
        $this->assertContains(Rol::CADMIN, rolesParaPermiso('cargos.masivos'));
        $this->assertContains(Rol::CADMIN, rolesParaPermiso('solicitudes_pago.pagar'));
    }


    public function test_operational_role_matrix_matches_the_real_idej_areas(): void
    {
        $cadmin = $this->usuarioConRol(40, Rol::CADMIN);
        $direccion = $this->usuarioConRol(41, Rol::DIRECCION);
        $academica = $this->usuarioConRol(42, Rol::ACADEMICA);
        $recepcion = $this->usuarioConRol(43, Rol::RECEPCION);
        $rrpp = $this->usuarioConRol(44, Rol::RRPP);
        $sistemas = $this->usuarioConRol(45, Rol::SISTEMAS);

        // Coordinación Administrativa absorbe la operación financiera, pero
        // solo consulta la operación académica detallada.
        $this->assertTrue($cadmin->tienePermiso('pagos.cancelar'));
        $this->assertTrue($cadmin->tienePermiso('cargos.masivos'));
        $this->assertTrue($cadmin->tienePermiso('convenios.gestionar'));
        $this->assertTrue($cadmin->tienePermiso('prospectos.convertir'));
        $this->assertTrue($cadmin->tienePermiso('academica.ver'));
        $this->assertFalse($cadmin->tienePermiso('calendarios.gestionar'));
        $this->assertFalse($cadmin->tienePermiso('catalogos_academicos.gestionar'));

        // Dirección es consulta ejecutiva, nunca operación.
        $this->assertTrue($direccion->tienePermiso('reportes.ejecutivos'));
        $this->assertTrue($direccion->tienePermiso('academica.ver'));
        $this->assertTrue($direccion->tienePermiso('caja.ver'));
        $this->assertTrue($direccion->tienePermiso('caja.pdf'));
        $this->assertFalse($direccion->tienePermiso('caja.operar'));
        $this->assertFalse($direccion->tienePermiso('caja.comprobante'));
        $this->assertFalse($direccion->tienePermiso('pagos.registrar'));
        $this->assertFalse($direccion->tienePermiso('documentos.descargar'));

        // Académica administra catálogos y calendarios, sin caja ni pagos.
        $this->assertTrue($academica->tienePermiso('catalogos_academicos.gestionar'));
        $this->assertTrue($academica->tienePermiso('calendarios.gestionar'));
        $this->assertFalse($academica->tienePermiso('caja.operar'));
        $this->assertFalse($academica->tienePermiso('pagos.registrar'));

        // Recepción cobra únicamente dentro de su caja y consulta convenios.
        $this->assertTrue($recepcion->tienePermiso('pagos.registrar'));
        $this->assertTrue($recepcion->tienePermiso('caja.operar'));
        $this->assertTrue($recepcion->tienePermiso('convenios.ver'));
        $this->assertTrue($recepcion->tienePermiso('calendarios.ver'));
        $this->assertTrue($recepcion->tienePermiso('horarios.ver'));
        $this->assertFalse($recepcion->tienePermiso('pagos.cancelar'));
        $this->assertFalse($recepcion->tienePermiso('cargos.masivos'));
        $this->assertFalse($recepcion->tienePermiso('convenios.gestionar'));

        // RRPP gestiona prospectos y consulta la oferta; no ve expedientes.
        $this->assertTrue($rrpp->tienePermiso('prospectos.gestionar'));
        $this->assertFalse($rrpp->tienePermiso('prospectos.convertir'));
        $this->assertTrue($rrpp->tienePermiso('oferta_academica.ver'));
        $this->assertTrue($rrpp->tienePermiso('educacion_continua.ver'));
        $this->assertFalse($rrpp->tienePermiso('alumnos.ver'));
        $this->assertFalse($rrpp->tienePermiso('documentos.ver'));
        $this->assertFalse($rrpp->tienePermiso('pagos.registrar'));

        // Sistemas conserva soporte técnico sin entrar a operación institucional.
        $this->assertTrue($sistemas->tienePermiso('mantenimiento.ver'));
        $this->assertFalse($sistemas->tienePermiso('alumnos.ver'));
        $this->assertFalse($sistemas->tienePermiso('caja.ver'));
        $this->assertFalse($sistemas->tienePermiso('documentos.ver'));
    }


    public function test_recepcion_student_updates_are_limited_to_contact_information(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/AlumnoController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('$esRecepcion = $request->user()?->rolClave() === Rol::RECEPCION', $controller);
        $this->assertStringContainsString('if (! $esRecepcion)', $controller);
        $this->assertStringContainsString("'grupo_id' => null", $controller);
    }

    private function usuarioConRol(int $id, string $clave): Usuario
    {
        $usuario = new Usuario();
        $usuario->forceFill(['id' => $id]);

        $rol = new Rol();
        $rol->forceFill(['clave' => $clave, 'nombre' => $clave]);
        $usuario->setRelation('rol', $rol);

        return $usuario;
    }

    private function cajaDeUsuario(int $id, int $usuarioId): CorteCaja
    {
        $caja = new CorteCaja();
        $caja->forceFill([
            'id' => $id,
            'usuario_id' => $usuarioId,
        ]);

        return $caja;
    }
}
