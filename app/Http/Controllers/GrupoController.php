<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcademico;
use App\Models\Grupo;
use App\Models\CicloEscolar;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Traits\RegistraBitacora;

class GrupoController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $mostrarArchivados = $request->boolean('archivados');

        $grupos = Grupo::with(['cicloEscolar', 'programa', 'archivadoPor'])
            ->when(! $mostrarArchivados, fn ($query) => $query->activos())
            ->when($mostrarArchivados, fn ($query) => $query->where('activo', false))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('grupos.index', compact('grupos', 'mostrarArchivados'));
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

        $validated['activo'] = true;
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
            'archivadoPor',
            'alumnos',
            'calendariosAcademicos.materiasCalendario.materia',
            'calendariosAcademicos.materiasCalendario.docente',
            'calendariosAcademicos.materiasCalendario.sesiones',
        ]);

        return view('grupos.show', compact('grupo'));
    }

    public function edit(Grupo $grupo)
    {
        if (! $grupo->activo) {
            return redirect()->route('grupos.show', $grupo)
                ->with('error', 'El grupo está archivado y su historial no puede modificarse.');
        }

        $ciclos = CicloEscolar::orderByDesc('created_at')->get();
        $programas = Programa::where('activo', true)->orWhere('id', $grupo->programa_id)->orderBy('nombre')->get();

        return view('grupos.edit', compact('grupo', 'ciclos', 'programas'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        if (! $grupo->activo) {
            return redirect()->route('grupos.show', $grupo)
                ->with('error', 'El grupo está archivado y su historial no puede modificarse.');
        }

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

    public function destroy(Request $request, Grupo $grupo)
    {
        if (! $grupo->activo) {
            return redirect()->route('grupos.index', ['archivados' => 1])
                ->with('info', 'El grupo ya estaba archivado.');
        }

        $validated = $request->validate([
            'motivo_archivo' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'motivo_archivo.required' => 'Explica por qué se archiva el grupo.',
            'motivo_archivo.min' => 'El motivo debe describir claramente la razón del archivo.',
        ]);

        $tieneCalendariosOperativos = CalendarioAcademico::where('grupo_id', $grupo->id)
            ->operativos()
            ->exists();

        if ($tieneCalendariosOperativos) {
            return redirect()->route('grupos.show', $grupo)
                ->with('error', 'El grupo tiene calendarios activos o en curso. Finalízalos o cancélalos con motivo antes de archivar el grupo.');
        }

        $grupo->update([
            'activo' => false,
            'archivado_at' => now(),
            'archivado_por_id' => auth()->id(),
            'motivo_archivo' => $validated['motivo_archivo'],
        ]);

        $this->bitacora(
            'Archivar Grupo',
            "Se archivó el grupo {$grupo->nombre} (ID {$grupo->id}) sin eliminar alumnos, calendarios ni sesiones. Motivo: {$validated['motivo_archivo']}",
            'Área Académica',
            $grupo
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo archivado correctamente. Su historial académico se conserva.');
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
