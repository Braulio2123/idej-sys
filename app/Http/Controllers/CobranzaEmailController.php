<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Programa;
use App\Services\RecordatorioPagoEmailService;
use Illuminate\Http\Request;

class CobranzaEmailController extends Controller
{
    public function index(Request $request, RecordatorioPagoEmailService $service)
    {
        $limite = max(1, min((int) $request->input('limite', 50), 300));
        $filtros = [
            'programa_id' => $request->input('programa_id'),
            'grupo_id' => $request->input('grupo_id'),
            'solo_vencidos' => $request->boolean('solo_vencidos'),
        ];

        return view('cobranza.correos.index', [
            'alumnos' => collect(),
            'programas' => Programa::orderBy('nombre')->get(),
            'grupos' => Grupo::with('programa')->orderBy('nombre')->get(),
            'recordatoriosActivos' => false,
            'limite' => $limite,
            'filtros' => $filtros,
        ]);
    }

    public function enviar(Request $request, RecordatorioPagoEmailService $service)
    {
        return redirect()->route('cobranza.correos.index')
            ->with('info', 'La cobranza por correo quedó pendiente temporalmente. El sistema conserva la pantalla, pero no enviará recordatorios hasta reactivar esta fase.');
    }
}
