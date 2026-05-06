<?php

namespace App\Http\Controllers;

use App\Models\DiaNoLaboral;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;

class DiaNoLaboralController extends Controller
{
    use RegistraBitacora;

    public function index()
    {
        $dias = DiaNoLaboral::orderByDesc('fecha')->paginate(20);

        return view('dias_no_laborales.index', [
            'dias' => $dias,
            'tipos' => [DiaNoLaboral::TIPO_LEY, DiaNoLaboral::TIPO_INSTITUCIONAL, DiaNoLaboral::TIPO_INTERNO],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date|unique:dias_no_laborales,fecha',
            'nombre' => 'required|string|max:180',
            'tipo' => 'required|in:Ley,Institucional,Interno',
            'activo' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $dia = DiaNoLaboral::create($validated);

        $this->bitacora('Crear día no laboral', "Se registró {$dia->nombre} para {$dia->fecha->format('d/m/Y')}.", 'Área Académica', $dia);

        return back()->with('success', 'Día no laboral registrado correctamente.');
    }

    public function update(Request $request, DiaNoLaboral $diaNoLaboral)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:180',
            'tipo' => 'required|in:Ley,Institucional,Interno',
            'activo' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $validated['activo'] = $request->boolean('activo');
        $diaNoLaboral->update($validated);

        $this->bitacora('Actualizar día no laboral', "Se actualizó el día no laboral {$diaNoLaboral->fecha->format('d/m/Y')}.", 'Área Académica', $diaNoLaboral);

        return back()->with('success', 'Día no laboral actualizado correctamente.');
    }

    public function destroy(DiaNoLaboral $diaNoLaboral)
    {
        $descripcion = "Se eliminó el día no laboral {$diaNoLaboral->nombre} ({$diaNoLaboral->fecha->format('d/m/Y')}).";
        $diaNoLaboral->delete();

        $this->bitacora('Eliminar día no laboral', $descripcion, 'Área Académica');

        return back()->with('success', 'Día no laboral eliminado correctamente.');
    }
}
