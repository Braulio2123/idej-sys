<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\RegistraBitacora;

class DocenteController extends Controller
{
    use RegistraBitacora;

    /**
     * LISTADO DE DOCENTES (con filtros)
     * NO aplica bitácora
     */
    public function index(Request $request)
    {
        $query = Docente::query();

        // Buscar por nombre o email
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        // Filtro por estatus
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $docentes = $query->orderBy('nombre_completo')->paginate(10);

        return view('docentes.index', compact('docentes'));
    }

    /**
     * FORMULARIO PARA AGREGAR NUEVO DOCENTE
     * NO aplica bitácora
     */
    public function create()
    {
        return view('docentes.create');
    }

    /**
     * GUARDAR NUEVO DOCENTE
     * ✔ SÍ aplica bitácora
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo'   => 'required|string|max:255',
            'email'             => 'nullable|email',
            'telefono'          => 'nullable|string',
            'domicilio'         => 'nullable|string',
            'area_especialidad' => 'required|string|max:255',
            'rfc'               => 'nullable|string|max:20',
            'numero_cuenta'     => 'nullable|string|max:30',
        ]);

        // Registrar quién lo creó
        $validated['creado_por_id'] = Auth::id();

        // Crear docente
        $docente = Docente::create($validated);

        // Recalcular estatus automático
        $docente->estatus = $docente->calcularEstatus();
        $docente->save();

        // 🔥 BITÁCORA
        $this->bitacora(
            'Crear Docente',
            "Se registró un nuevo docente: {$docente->nombre_completo} (ID {$docente->id})."
        );

        return redirect()
            ->route('docentes.index')
            ->with('success', 'Docente registrado correctamente.');
    }

    /**
     * MOSTRAR DETALLES DEL DOCENTE
     * NO aplica bitácora
     */
    public function show(Docente $docente)
    {
        $docente->load(['calendarioMaterias.calendario.grupo.programa', 'calendarioMaterias.materia', 'calendarioMaterias.sesiones']);

        return view('docentes.show', compact('docente'));
    }

    /**
     * FORMULARIO DE EDICIÓN
     * NO aplica bitácora
     */
    public function edit(Docente $docente)
    {
        return view('docentes.edit', compact('docente'));
    }

    /**
     * ACTUALIZAR DOCENTE
     * ✔ SÍ aplica bitácora
     */
    public function update(Request $request, Docente $docente)
    {
        $validated = $request->validate([
            'nombre_completo'   => 'required|string|max:255',
            'email'             => 'nullable|email',
            'telefono'          => 'nullable|string',
            'domicilio'         => 'nullable|string',
            'area_especialidad' => 'required|string|max:255',
            'rfc'               => 'nullable|string|max:20',
            'numero_cuenta'     => 'nullable|string|max:30',
        ]);

        // Actualizar datos
        $docente->update($validated);

        // Recalcular estatus automático
        $docente->estatus = $docente->calcularEstatus();
        $docente->save();

        // 🔥 BITÁCORA
        $this->bitacora(
            'Actualizar Docente',
            "Se actualizó el docente {$docente->nombre_completo} (ID {$docente->id})."
        );

        return redirect()
            ->route('docentes.index')
            ->with('success', 'Docente actualizado correctamente.');
    }

    /**
     * ELIMINAR DOCENTE
     * ✔ SÍ aplica bitácora
     */
    public function destroy(Docente $docente)
    {
        $id = $docente->id;
        $nombre = $docente->nombre_completo;

        $docente->delete();

        // 🔥 BITÁCORA
        $this->bitacora(
            'Eliminar Docente',
            "Se eliminó el docente {$nombre} (ID {$id})."
        );

        return redirect()
            ->route('docentes.index')
            ->with('success', 'Docente eliminado correctamente.');
    }
}
