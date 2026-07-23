<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use RegistraBitacora;

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'puedeModificarCorreo' => $this->puedeModificarCorreo($request->user()),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (method_exists($user, 'requiereCambioPassword') && $user->requiereCambioPassword()) {
            return Redirect::route('profile.edit')
                ->with('warning', 'Primero debes cambiar la contraseña temporal. Después podrás actualizar otros datos permitidos.');
        }

        $datos = $request->validated();

        if (! $this->puedeModificarCorreo($user)) {
            unset($datos['email']);
        }

        $user->fill($datos);
        $user->save();

        $this->bitacora(
            'Actualizar Perfil',
            "El usuario {$user->nombre} actualizó su información personal."
        );

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * En IDEJ-SYS los usuarios no deben eliminar su propia cuenta.
     * La baja de usuarios corresponde al módulo administrativo Usuarios.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort(403, 'La eliminación de cuentas solo puede realizarla un administrador desde el módulo Usuarios.');
    }

    private function puedeModificarCorreo($usuario): bool
    {
        return $usuario
            && method_exists($usuario, 'esAdmin')
            && method_exists($usuario, 'esSistemas')
            && ($usuario->esAdmin() || $usuario->esSistemas());
    }
}
