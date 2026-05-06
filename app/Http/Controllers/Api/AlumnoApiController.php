<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiAlumno; // modelo simple API

class AlumnoApiController extends Controller
{
    /**
     * GET – Listar alumnos (Android espera un ARRAY)
     */
    public function index()
    {
        return ApiAlumno::all();
    }

    /**
     * GET – Mostrar un alumno
     */
    public function show($id)
    {
        return ApiAlumno::findOrFail($id);
    }

    /**
     * POST – Crear alumno
     */
    public function store(Request $request)
    {
    $validated = $request->validate([
        'matricula'         => 'required|string|unique:alumnos,matricula',
        'nombre_completo'   => 'required|string',
        'correo'            => 'nullable|string',
        'telefono'          => 'nullable|string',
        'estatus_financiero'=> 'nullable|string',
        'estatus_academico' => 'nullable|string',
        'beca_porcentaje'   => 'nullable|integer',
        'condicion_alumno'  => 'nullable|string',
        'grupo_id'          => 'nullable|integer',
    ]);
    $validated['estatus_financiero'] = $validated['estatus_financiero'] ?? 'Al Corriente';
    $validated['estatus_academico'] = $validated['estatus_academico'] ?? 'Activo';
    $validated['beca_porcentaje'] = $validated['beca_porcentaje'] ?? 0;


        return ApiAlumno::create($validated);

    }

    /**
     * PUT – Actualizar alumno
     */
    public function update(Request $request, $id)
    {
        $alumno = ApiAlumno::findOrFail($id);

        $validated = $request->validate([
            'matricula'         => "required|string|unique:alumnos,matricula,{$id}",
            'nombre_completo'   => 'required|string',
            'correo'            => 'nullable|string',
            'telefono'          => 'nullable|string',
            'estatus_financiero'=> 'nullable|string',
            'estatus_academico' => 'nullable|string',
            'beca_porcentaje'   => 'nullable|integer',
            'condicion_alumno'  => 'nullable|string',
            'grupo_id'          => 'nullable|integer',
        ]);

            $validated['estatus_financiero'] = $validated['estatus_financiero'] ?? 'Al Corriente';
            $validated['estatus_academico'] = $validated['estatus_academico'] ?? 'Activo';
            $validated['beca_porcentaje'] = $validated['beca_porcentaje'] ?? 0;



        $alumno->update($validated);

        return $alumno; // Android espera un objeto plano
    }

    /**
     * DELETE – Eliminar alumno
     */
    public function destroy($id)
    {
        $alumno = ApiAlumno::findOrFail($id);
        $alumno->delete();

        return ['deleted' => true];
    }

    // funcion para buscar alumnos por matricula
    public function buscarPorMatricula($matricula)
    {
        return ApiAlumno::where('matricula', $matricula)->first();
    }
}
