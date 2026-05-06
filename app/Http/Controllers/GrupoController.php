<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\CicloEscolar;
use App\Models\Programa;
use Illuminate\Http\Request;
use App\Traits\RegistraBitacora;

class GrupoController extends Controller
{
    use RegistraBitacora;

    /**
     * LISTADO DE GRUPOS (solo lectura)
     */
    public function index()
    {
        $grupos = Grupo::with(['cicloEscolar', 'programa'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('grupos.index', compact('grupos'));
    }

    /**
     * FORMULARIO PARA CREAR GRUPO (solo vista)
     */
    public function create()
    {
        $ciclos = CicloEscolar::orderByDesc('created_at')->get();
        $programas = Programa::orderBy('nombre')->get();

        return view('grupos.create', compact('ciclos', 'programas'));
    }

    /**
     * GUARDAR NUEVO GRUPO (BITÁCORA)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ciclo_escolar_id' => 'required|exists:ciclos_escolares,id',
            'programa_id' => 'required|exists:programas,id',
            'semestre_o_cuatrimestre' => 'required|integer|min:1|max:12',
            'turno' => 'required|in:Matutino,Vespertino,Sabatino,Mixto',
            'aula' => 'nullable|string|max:50',
            'cupo_maximo' => 'required|integer|min:1|max:60',
        ]);

        $grupo = Grupo::create($validated);

        // 🔥 BITÁCORA
        $this->bitacora(
            'Crear Grupo',
            "Se creó el grupo {$grupo->nombre} (ID {$grupo->id}) del programa {$grupo->programa->nombre}."
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo académico creado correctamente.');
    }

    /**
     * MOSTRAR GRUPO (solo lectura)
     */
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

    /**
     * FORMULARIO EDITAR GRUPO (solo vista)
     */
    public function edit(Grupo $grupo)
    {
        $ciclos = CicloEscolar::orderByDesc('created_at')->get();
        $programas = Programa::orderBy('nombre')->get();

        return view('grupos.edit', compact('grupo', 'ciclos', 'programas'));
    }

    /**
     * ACTUALIZAR GRUPO (BITÁCORA)
     */
    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ciclo_escolar_id' => 'required|exists:ciclos_escolares,id',
            'programa_id' => 'required|exists:programas,id',
            'semestre_o_cuatrimestre' => 'required|integer|min:1|max:12',
            'turno' => 'required|in:Matutino,Vespertino,Sabatino,Mixto',
            'aula' => 'nullable|string|max:50',
            'cupo_maximo' => 'required|integer|min:1|max:60',
        ]);

        $grupo->update($validated);

        // 🔥 BITÁCORA
        $this->bitacora(
            'Actualizar Grupo',
            "Actualización del grupo {$grupo->nombre} (ID {$grupo->id})."
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo académico actualizado correctamente.');
    }

    /**
     * ELIMINAR GRUPO (BITÁCORA)
     */
    public function destroy(Grupo $grupo)
    {
        $id = $grupo->id;
        $nombre = $grupo->nombre;

        $grupo->delete();

        // 🔥 BITÁCORA
        $this->bitacora(
            'Eliminar Grupo',
            "Se eliminó el grupo {$nombre} (ID {$id})."
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo eliminado correctamente.');
    }
}
