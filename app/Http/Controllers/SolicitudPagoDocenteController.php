<?php

namespace App\Http\Controllers;

use App\Models\SolicitudPagoDocente;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\RegistraBitacora;
use App\Models\Rol;

class SolicitudPagoDocenteController extends Controller
{
    use RegistraBitacora;

    /**
     * Mostrar listado según el rol del usuario.
     */
    public function index()
    {
        $rol = Auth::user()->rolClave();

        if ($rol === Rol::ACADEMICA) {
            $solicitudes = SolicitudPagoDocente::whereIn('estatus', ['Pendiente', 'Aprobada'])
                ->orderBy('fecha_solicitud', 'desc')
                ->paginate(15);

        } elseif ($rol === Rol::RECEPCION) {
            $solicitudes = SolicitudPagoDocente::where('creado_por_id', Auth::id())
                ->orderBy('fecha_solicitud', 'desc')
                ->paginate(15);

        } elseif (in_array($rol, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION], true)) {
            $solicitudes = SolicitudPagoDocente::orderBy('fecha_solicitud', 'desc')
                ->paginate(15);

        } else {
            $solicitudes = SolicitudPagoDocente::where('estatus', 'Aprobada')
                ->orderBy('fecha_solicitud', 'desc')
                ->paginate(15);
        }

        return view('solicitudes_pago.index', compact('solicitudes'));
    }

    /**
     * Formulario crear solicitud.
     */
    public function create()
    {
        // CORREGIDO: ordenar por columna REAL
        $docentes = Docente::orderBy('nombre_completo')->get();
        return view('solicitudes_pago.create', compact('docentes'));
    }

    /**
     * Guardar nueva solicitud.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'docente_id'       => 'required|exists:docentes,id',
            'nivel'            => 'required|string|max:50',
            'monto'            => 'required|numeric|min:1',
            'fecha_solicitud'  => 'required|date',
            'fecha_pago'       => 'nullable|date',
            'observaciones'    => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {

            $solicitud = SolicitudPagoDocente::create([
                'docente_id'       => $validated['docente_id'],
                'nivel'            => $validated['nivel'],
                'monto'            => $validated['monto'],
                'fecha_solicitud'  => $validated['fecha_solicitud'],
                'fecha_pago'       => $validated['fecha_pago'] ?? null,
                'estatus'          => 'Pendiente',
                'observaciones'    => $validated['observaciones'] ?? null,
                'creado_por_id'    => Auth::id(),
            ]);

            $this->bitacora(
                'Crear Solicitud de Pago Docente',
                "Solicitud #{$solicitud->id} creada para el docente {$solicitud->docente->nombre}."
            );
        });

        return redirect()->route('solicitudes_pago.index')
            ->with('success', 'Solicitud creada correctamente.');
    }

    /**
     * Mostrar solicitud.
     */
    public function show(SolicitudPagoDocente $solicitud_pago)
    {
        $solicitud_pago->load(['docente', 'creadoPor', 'procesadoPor']);

        return view('solicitudes_pago.show', [
            'solicitud' => $solicitud_pago
        ]);
    }

    /**
     * Editar solicitud (solo Admin).
     */
    public function edit(SolicitudPagoDocente $solicitud_pago)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }

        return view('solicitudes_pago.edit', [
            'solicitud' => $solicitud_pago,
        ]);
    }

    /**
     * Actualizar solicitud (solo Admin).
     */
    public function update(Request $request, SolicitudPagoDocente $solicitud_pago)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }

        $validated = $request->validate([
            'nivel'         => 'required|string|max:50',
            'monto'         => 'required|numeric|min:1',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated) {

            $solicitud_pago->update($validated);

            $this->bitacora(
                'Editar Solicitud de Pago Docente',
                "Solicitud #{$solicitud_pago->id} actualizada."
            );
        });

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud actualizada correctamente.');
    }

    /**
     * APROBAR solicitud (nuevo – corregido).
     */
    public function aprobar(SolicitudPagoDocente $solicitud_pago)
    {
        if (!in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::ACADEMICA], true)) {
            abort(403);
        }

        if ($solicitud_pago->estatus !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden aprobar solicitudes pendientes.');
        }

        DB::transaction(function () use ($solicitud_pago) {

            $solicitud_pago->update([
                'estatus' => 'Aprobada'
            ]);

            $this->bitacora(
                'Aprobar Solicitud de Pago Docente',
                "Solicitud #{$solicitud_pago->id} aprobada."
            );
        });

        return back()->with('success', 'Solicitud aprobada correctamente.');
    }

    /**
     * Mostrar formulario de pago (GET).
     */
    public function formPagar(SolicitudPagoDocente $solicitud_pago)
    {
        return view('solicitudes_pago.pagar', [
            'solicitud' => $solicitud_pago
        ]);
    }

    /**
     * Registrar pago (PUT).
     */
    public function pagar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        if (!in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true)) {
            abort(403);
        }

        if ($solicitud_pago->estatus !== 'Aprobada') {
            return back()->with('error', 'Solo se pueden pagar solicitudes aprobadas.');
        }

        $validated = $request->validate([
            'fecha_pago'   => 'required|date',
            'referencia'   => 'nullable|string|max:200',
            'observaciones'=> 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated) {

            $solicitud_pago->update([
                'fecha_pago'       => $validated['fecha_pago'],
                'procesado_por_id' => Auth::id(),
                'estatus'          => 'Pagada',
                'observaciones'    => $validated['observaciones'] ?? $solicitud_pago->observaciones,
            ]);

            $this->bitacora(
                'Pagar Solicitud de Pago Docente',
                "Solicitud #{$solicitud_pago->id} pagada."
            );
        });

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Eliminar solicitud (solo Admin).
     */
    public function destroy(SolicitudPagoDocente $solicitud_pago)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }

        DB::transaction(function () use ($solicitud_pago) {

            $id = $solicitud_pago->id;
            $solicitud_pago->delete();

            $this->bitacora(
                'Eliminar Solicitud de Pago Docente',
                "Solicitud #{$id} eliminada."
            );
        });

        return redirect()->route('solicitudes_pago.index')
            ->with('success', 'Solicitud eliminada correctamente.');
    }
}
