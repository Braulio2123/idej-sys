<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    use RegistraBitacora;

    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            $this->bitacora(
                'Confirmación de contraseña fallida',
                'El usuario intentó confirmar su contraseña para una operación sensible, pero la contraseña fue incorrecta.',
                'Seguridad',
                $request->user()
            );

            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        $this->bitacora(
            'Contraseña confirmada para operación sensible',
            'El usuario confirmó su contraseña para continuar con una operación protegida.',
            'Seguridad',
            $request->user()
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
