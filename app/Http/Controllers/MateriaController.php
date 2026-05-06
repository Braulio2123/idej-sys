<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Programa;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $query = Materia::with('programa');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->buscar.'%')
                    ->orWhere('clave', 'like', '%'.$request->buscar.'%');
            });
        }

        if ($request->filled('programa_id')) {
            $query->where('programa_id', $request->programa_id);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $materias = $query->orderBy('nombre')->paginate(12)->withQueryString();
        $programas = Programa::orderBy('nombre')->get();

        return view('materias.index', compact('materias', 'programas'));
    }

    public function create()
    {
        $programas = Programa::orderBy('nombre')->get();

        return view('materias.create', compact('programas'));
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);

        $materia = Materia::create($validated);

        $this->bitacora(
            'Crear Materia',
            "Se creó la materia {$materia->nombre} (ID {$materia->id}).",
            'Área Académica',
            $materia
        );

        return redirect()
            ->route('materias.index')
            ->with('success', 'Materia registrada correctamente.');
    }

    public function edit(Materia $materia)
    {
        $programas = Programa::orderBy('nombre')->get();

        return view('materias.edit', compact('materia', 'programas'));
    }

    public function update(Request $request, Materia $materia)
    {
        $validated = $this->validar($request);
        $materia->update($validated);

        $this->bitacora(
            'Actualizar Materia',
            "Se actualizó la materia {$materia->nombre} (ID {$materia->id}).",
            'Área Académica',
            $materia
        );

        return redirect()
            ->route('materias.index')
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(Materia $materia)
    {
        if ($materia->horarios()->exists()) {
            return back()->with('error', 'No se puede eliminar la materia porque tiene horarios/asignaciones relacionadas. Puedes marcarla como inactiva.');
        }

        $nombre = $materia->nombre;
        $id = $materia->id;
        $materia->delete();

        $this->bitacora(
            'Eliminar Materia',
            "Se eliminó la materia {$nombre} (ID {$id}).",
            'Área Académica'
        );

        return redirect()
            ->route('materias.index')
            ->with('success', 'Materia eliminada correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'programa_id' => 'nullable|exists:programas,id',
            'clave' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:255',
            'nivel' => 'nullable|string|max:80',
            'semestre_o_cuatrimestre' => 'nullable|integer|min:1|max:12',
            'creditos' => 'nullable|integer|min:0|max:99',
            'horas_teoricas' => 'required|integer|min:0|max:99',
            'horas_practicas' => 'required|integer|min:0|max:99',
            'estatus' => 'required|in:Activa,Inactiva',
            'descripcion' => 'nullable|string|max:2000',
        ]);
    }
}
