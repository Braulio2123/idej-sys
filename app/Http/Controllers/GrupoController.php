<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\CicloEscolar;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Traits\RegistraBitacora;

class GrupoController extends Controller
{
    use RegistraBitacora;

    public function index()
    {
        $grupos = Grupo::with(['cicloEscolar', 'programa'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('grupos.index', compact('grupos'));
    }

    public function create()
    {
        $ciclos = CicloEscolar::orderByDesc('created_at')->get();
        $programas = Programa::where('activo', true)->orderBy('nombre')->get();

        return view('grupos.create', compact('ciclos', 'programas'));
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $programa = Programa::findOrFail($validated['programa_id']);
        $this->validarSemestreContraPrograma($programa, (int) $validated['semestre_o_cuatrimestre']);

        $validated['turno'] = 'Mixto';
        $validated['aula'] = null;

        $grupo = Grupo::create($validated);

        $this->bitacora(
            'Crear Grupo',
            "Se creó el grupo {$grupo->nombre} (ID {$grupo->id}) de Educación Programática {$grupo->programa->nombre}."
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo académico creado correctamente. El aula será asignada posteriormente por Sistemas.');
    }

    public function show(Grupo $grupo)
    {
        $grupo->load([
            'cicloEscolar',
            'programa',
            'alumnos',
            'calendariosAcademicos.materiasCalendario.materia',
            'calendariosAcademicos.materiasCalendario.docente',
            'calendariosAcademicos.materiasCalendario.sesiones',
        ]);

        return view('grupos.show', compact('grupo'));
    }

    public function edit(Grupo $grupo)
    {
        $ciclos = CicloEscolar::orderByDesc('created_at')->get();
        $programas = Programa::where('activo', true)->orWhere('id', $grupo->programa_id)->orderBy('nombre')->get();

        return view('grupos.edit', compact('grupo', 'ciclos', 'programas'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        $validated = $this->validar($request);
        $programa = Programa::findOrFail($validated['programa_id']);
        $this->validarSemestreContraPrograma($programa, (int) $validated['semestre_o_cuatrimestre']);

        $validated['turno'] = 'Mixto';
        $validated['aula'] = null;

        $grupo->update($validated);

        $this->bitacora(
            'Actualizar Grupo',
            "Actualización del grupo {$grupo->nombre} (ID {$grupo->id})."
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo académico actualizado correctamente.');
    }

    public function destroy(Grupo $grupo)
    {
        $id = $grupo->id;
        $nombre = $grupo->nombre;

        $grupo->delete();

        $this->bitacora(
            'Eliminar Grupo',
            "Se eliminó el grupo {$nombre} (ID {$id})."
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'ciclo_escolar_id' => 'required|exists:ciclos_escolares,id',
            'programa_id' => 'required|exists:programas,id',
            'semestre_o_cuatrimestre' => 'required|integer|min:1|max:20',
            'cupo_maximo' => 'required|integer|min:1|max:60',
        ], [
            'programa_id.required' => 'Selecciona la Educación Programática del grupo.',
            'semestre_o_cuatrimestre.required' => 'Selecciona el semestre del grupo.',
        ]);
    }

    private function validarSemestreContraPrograma(Programa $programa, int $semestre): void
    {
        $duracion = (int) ($programa->duracion_periodos ?? 0);

        if ($duracion > 0 && $semestre > $duracion) {
            throw ValidationException::withMessages([
                'semestre_o_cuatrimestre' => "La Educación Programática seleccionada tiene {$duracion} semestre(s). No puedes registrar semestre {$semestre}.",
            ]);
        }
    }
}
