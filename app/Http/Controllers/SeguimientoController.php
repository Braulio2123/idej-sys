<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Seguimiento;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SeguimientoController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request, Alumno $alumno)
    {
        $estatus = $request->get('estatus');
        $tipo = $request->get('tipo');
        $prioridad = $request->get('prioridad');

        $seguimientos = $alumno->seguimientos()
            ->with('usuario')
            ->when($estatus, fn ($query) => $query->where('estatus', $estatus))
            ->when($tipo, fn ($query) => $query->where('tipo', $tipo))
            ->when($prioridad, fn ($query) => $query->where('prioridad', $prioridad))
            ->orderByRaw('CASE WHEN fecha_proximo_contacto IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_proximo_contacto')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->appends($request->query());

        return view('alumnos.seguimientos_index', [
            'alumno' => $alumno->load('grupo.programa'),
            'seguimientos' => $seguimientos,
            'tipos' => Seguimiento::tipos(),
            'prioridades' => Seguimiento::prioridades(),
            'estatusDisponibles' => Seguimiento::estatusDisponibles(),
            'filtros' => compact('estatus', 'tipo', 'prioridad'),
        ]);
    }

    public function store(Request $request, Alumno $alumno)
    {
        $validated = $this->validarSeguimiento($request);

        $seguimiento = $alumno->seguimientos()->create([
            ...$validated,
            'usuario_id' => Auth::id(),
            'area' => Auth::user()?->rol?->nombre,
            'fecha_contacto' => $validated['fecha_contacto'] ?? now(),
        ]);

        $this->bitacora(
            'Crear Seguimiento',
            "Se registró seguimiento para {$alumno->nombre_completo}: {$seguimiento->tipo} - {$seguimiento->asunto}.",
            'Seguimientos',
            $seguimiento,
            $alumno->id
        );

        return back()->with('success', 'Seguimiento registrado correctamente.');
    }

    public function update(Request $request, Alumno $alumno, Seguimiento $seguimiento)
    {
        $this->verificarPertenencia($alumno, $seguimiento);

        $validated = $this->validarSeguimiento($request, true);

        if (($validated['estatus'] ?? $seguimiento->estatus) === Seguimiento::ESTATUS_CERRADO && ! $seguimiento->fecha_cierre) {
            $validated['fecha_cierre'] = now();
        }

        if (($validated['estatus'] ?? null) !== Seguimiento::ESTATUS_CERRADO) {
            $validated['fecha_cierre'] = null;
        }

        $seguimiento->update($validated);

        $this->bitacora(
            'Actualizar Seguimiento',
            "Se actualizó seguimiento #{$seguimiento->id} de {$alumno->nombre_completo}.",
            'Seguimientos',
            $seguimiento,
            $alumno->id
        );

        return back()->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy(Alumno $alumno, Seguimiento $seguimiento)
    {
        $this->verificarPertenencia($alumno, $seguimiento);

        $id = $seguimiento->id;
        $asunto = $seguimiento->asunto;
        $seguimiento->delete();

        $this->bitacora(
            'Eliminar Seguimiento',
            "Se eliminó seguimiento #{$id} ({$asunto}) de {$alumno->nombre_completo}.",
            'Seguimientos',
            null,
            $alumno->id
        );

        return back()->with('success', 'Seguimiento eliminado correctamente.');
    }

    private function validarSeguimiento(Request $request, bool $actualizacion = false): array
    {
        return $request->validate([
            'tipo' => ['required', 'string', Rule::in(Seguimiento::tipos())],
            'prioridad' => ['required', 'string', Rule::in(Seguimiento::prioridades())],
            'estatus' => ['required', 'string', Rule::in(Seguimiento::estatusDisponibles())],
            'asunto' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'resultado' => ['nullable', 'string', 'max:5000'],
            'fecha_contacto' => ['nullable', 'date'],
            'fecha_proximo_contacto' => ['nullable', 'date'],
        ]);
    }

    private function verificarPertenencia(Alumno $alumno, Seguimiento $seguimiento): void
    {
        abort_unless((int) $seguimiento->alumno_id === (int) $alumno->id, 404);
    }
}
