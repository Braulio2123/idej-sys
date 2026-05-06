<?php

namespace App\Http\Controllers;

use App\Models\CicloEscolar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\RegistraBitacora;

class CicloEscolarController extends Controller
{
    use RegistraBitacora;

    /**
     * Listado de ciclos.
     * NO APLICA BITÁCORA
     */
    public function index()
    {
        $ciclos = CicloEscolar::orderByDesc('created_at')->paginate(12);
        return view('ciclos_escolares.index', compact('ciclos'));
    }

    /**
     * Formulario crear.
     * NO APLICA BITÁCORA
     */
    public function create()
    {
        return view('ciclos_escolares.create');
    }

    /**
     * Guardar ciclo.
     * ✔ SI APLICA BITÁCORA
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:ciclos_escolares,nombre',
            'tipo_periodo' => 'required|in:Cuatrimestral,Semestral,Anual,Otro',
            'fecha_inicio_inscripcion' => 'required|date',
            'fecha_fin_inscripcion' => 'required|date|after:fecha_inicio_inscripcion',
            'fecha_inicio_clases' => 'required|date',
            'fecha_fin_clases' => 'required|date|after:fecha_inicio_clases',
            'activo' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated) {

            // Desactivar ciclo activo, si se indicó activar este
            if (!empty($validated['activo'])) {
                CicloEscolar::where('activo', true)->update(['activo' => false]);
            }

            $nuevo = CicloEscolar::create($validated);

            // 🔥 BITÁCORA
            $this->bitacora(
                'Crear Ciclo Escolar',
                "Se creó el ciclo escolar '{$nuevo->nombre}' con periodo {$nuevo->tipo_periodo}."
            );
        });

        return redirect()->route('ciclos_escolares.index')
            ->with('success', 'Ciclo escolar creado correctamente.');
    }

    /**
     * Formulario editar.
     * NO APLICA BITÁCORA
     */
    public function edit(CicloEscolar $ciclo_escolar)
    {
        return view('ciclos_escolares.edit', [
            'ciclo' => $ciclo_escolar
        ]);
    }

    /**
     * Actualizar ciclo.
     * ✔ SI APLICA BITÁCORA
     */
    public function update(Request $request, CicloEscolar $ciclo_escolar)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:ciclos_escolares,nombre,' . $ciclo_escolar->id,
            'tipo_periodo' => 'required|in:Cuatrimestral,Semestral,Anual,Otro',
            'fecha_inicio_inscripcion' => 'required|date',
            'fecha_fin_inscripcion' => 'required|date|after:fecha_inicio_inscripcion',
            'fecha_inicio_clases' => 'required|date',
            'fecha_fin_clases' => 'required|date|after:fecha_inicio_clases',
            'activo' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $ciclo_escolar) {

            if (!empty($validated['activo'])) {
                CicloEscolar::where('id', '!=', $ciclo_escolar->id)
                    ->update(['activo' => false]);
            }

            $ciclo_escolar->update($validated);

            // 🔥 BITÁCORA
            $this->bitacora(
                'Actualizar Ciclo Escolar',
                "El ciclo escolar '{$ciclo_escolar->nombre}' fue actualizado."
            );
        });

        return redirect()->route('ciclos_escolares.index')
            ->with('success', 'Ciclo escolar actualizado correctamente.');
    }

    /**
     * Eliminar ciclo.
     * ✔ SI APLICA BITÁCORA
     */
    public function destroy(CicloEscolar $ciclo_escolar)
    {
        $nombre = $ciclo_escolar->nombre;

        $ciclo_escolar->delete();

        // 🔥 BITÁCORA
        $this->bitacora(
            'Eliminar Ciclo Escolar',
            "Se eliminó el ciclo escolar '{$nombre}'."
        );

        return redirect()->route('ciclos_escolares.index')
            ->with('success', 'Ciclo escolar eliminado correctamente.');
    }
}
