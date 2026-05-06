<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitacoraController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $usuario = $request->input('usuario');
        $accion = $request->input('accion');
        $modulo = $request->input('modulo');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $buscar = $request->input('buscar');

        $query = Bitacora::with(['usuario', 'alumno'])
            ->orderByDesc('fecha_evento')
            ->orderByDesc('created_at');

        if ($usuario) {
            $query->where('usuario_id', $usuario);
        }

        if ($accion) {
            $query->where('accion', 'LIKE', "%{$accion}%");
        }

        if ($modulo) {
            $query->where('modulo', $modulo);
        }

        if ($fechaInicio) {
            $query->whereDate('fecha_evento', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha_evento', '<=', $fechaFin);
        }

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('accion', 'LIKE', "%{$buscar}%")
                    ->orWhere('modulo', 'LIKE', "%{$buscar}%")
                    ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                    ->orWhere('ip_address', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('usuario', function ($usuarioQuery) use ($buscar) {
                        $usuarioQuery->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('email', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        $bitacoras = $query->paginate(20)->withQueryString();
        $usuarios = Usuario::orderBy('nombre')->get();
        $modulos = Bitacora::query()
            ->whereNotNull('modulo')
            ->select('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        return view('bitacoras.index', compact('bitacoras', 'usuarios', 'modulos'));
    }

    public function exportPdf(Request $request)
    {
        $query = Bitacora::with(['usuario', 'alumno'])
            ->orderByDesc('fecha_evento')
            ->orderByDesc('created_at');

        if ($request->usuario) {
            $query->where('usuario_id', $request->usuario);
        }

        if ($request->accion) {
            $query->where('accion', 'LIKE', "%{$request->accion}%");
        }

        if ($request->modulo) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->fecha_inicio) {
            $query->whereDate('fecha_evento', '>=', $request->fecha_inicio);
        }

        if ($request->fecha_fin) {
            $query->whereDate('fecha_evento', '<=', $request->fecha_fin);
        }

        if ($request->buscar) {
            $query->where(function ($q) use ($request) {
                $buscar = $request->buscar;
                $q->where('accion', 'LIKE', "%{$buscar}%")
                    ->orWhere('modulo', 'LIKE', "%{$buscar}%")
                    ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                    ->orWhere('ip_address', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('usuario', function ($usuarioQuery) use ($buscar) {
                        $usuarioQuery->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('email', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        $bitacoras = $query->get();

        $pdf = Pdf::loadView('bitacoras.pdf', [
            'bitacoras' => $bitacoras,
            'filtros' => $request->only(['usuario', 'accion', 'modulo', 'fecha_inicio', 'fecha_fin', 'buscar']),
        ])->setPaper('letter', 'landscape');

        return $pdf->download('reporte_bitacora.pdf');
    }

    public function show(Bitacora $bitacora)
    {
        $bitacora->load(['usuario', 'alumno']);

        return view('bitacoras.show', compact('bitacora'));
    }

    public function destroy(Bitacora $bitacora)
    {
        $user = Auth::user();

        if (! $user || $user->rolClave() !== Rol::ADMIN) {
            return back()->with('error', 'Solo el administrador puede eliminar bitácoras.');
        }

        $descripcion = "Registro de bitácora eliminado. Acción: '{$bitacora->accion}', ID {$bitacora->id}.";

        $bitacora->delete();

        $this->bitacora('Eliminar Bitácora', $descripcion, 'Bitácoras');

        return redirect()->route('bitacoras.index')
            ->with('success', 'Registro de bitácora eliminado correctamente.');
    }
}
