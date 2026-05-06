<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\CargoMasivo;
use App\Models\ConceptoPago;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\RegistraBitacora;

class CargoMasivoController extends Controller
{
    use RegistraBitacora;

    /**
     * Vista principal: filtros + historial.
     * NO APLICA BITÁCORA.
     */
    public function index()
    {
        return view('cargos.masivo.index', [
            'conceptos' => ConceptoPago::orderBy('nombre')->get(),
            'programas' => Programa::orderBy('nombre')->get(),
            'grupos'    => Grupo::with(['programa', 'cicloEscolar'])->orderBy('nombre')->get(),
            'ciclos'    => CicloEscolar::orderBy('nombre')->get(),
            'historial' => CargoMasivo::with(['concepto', 'usuario'])
                ->latest()
                ->paginate(20),
        ]);
    }

    /**
     * FILTROS EN TIEMPO REAL (AJAX).
     * NO APLICA BITÁCORA.
     */
    public function filtrarAlumnos(Request $request)
    {
        $query = Alumno::with(['grupo.programa', 'grupo.cicloEscolar']);

        if ($request->filled('programa_id')) {
            $programaId = $request->input('programa_id');
            $query->whereHas('grupo', function ($q) use ($programaId) {
                $q->where('programa_id', $programaId);
            });
        }

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->input('grupo_id'));
        }

        if ($request->filled('ciclo_id')) {
            $cicloId = $request->input('ciclo_id');
            $query->whereHas('grupo', function ($q) use ($cicloId) {
                $q->where('ciclo_escolar_id', $cicloId);
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus_financiero', $request->input('estatus'));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('matricula', 'like', "%{$buscar}%");
            });
        }

        $alumnos = $query
            ->orderBy('nombre_completo')
            ->get()
            ->map(function (Alumno $alumno) {
                return [
                    'id'                 => $alumno->id,
                    'matricula'          => $alumno->matricula,
                    'nombre_completo'    => $alumno->nombre_completo,
                    'grupo'              => optional($alumno->grupo)->nombre ?? '—',
                    'programa'           => optional(optional($alumno->grupo)->programa)->nombre ?? '—',
                    'estatus_financiero' => $alumno->estatus_financiero,
                ];
            });

        return response()->json([
            'success' => true,
            'alumnos' => $alumnos,
        ]);
    }

    /**
     * Guardar cargos masivos.
     * ✔ SÍ APLICA BITÁCORA.
     */
    public function store(Request $request)
    {
        $request->validate([
            'concepto_id'       => 'required|exists:conceptos_pagos,id',
            'fecha_vencimiento' => 'required|date',
            'monto'             => 'nullable|numeric|min:0',
            'descripcion'       => 'nullable|string|max:255',
            'alumnos'           => 'required|array|min:1',
            'alumnos.*'         => 'integer|exists:alumnos,id',
            'programa_id'       => 'nullable|integer',
            'grupo_id'          => 'nullable|integer',
            'ciclo_id'          => 'nullable|integer',
        ], [
            'alumnos.required'  => 'Debes seleccionar al menos un alumno.',
        ]);

        $concepto = ConceptoPago::findOrFail($request->concepto_id);
        $monto = $request->monto ?? $concepto->monto_base;
        $descripcion = $request->descripcion ?: ('Cargo masivo: ' . $concepto->nombre);
        $fechaVencimiento = $request->fecha_vencimiento;

        $cargosInsert = [];
        $alumnosAfectados = Alumno::whereIn('id', $request->alumnos)
            ->with('becas')
            ->get()
            ->keyBy('id');

        foreach ($request->alumnos as $alumnoId) {

            $existe = Cargo::where('alumno_id', $alumnoId)
                ->where('concepto_id', $concepto->id)
                ->where('fecha_vencimiento', $fechaVencimiento)
                ->exists();

            if ($existe) {
                continue;
            }

            $alumno = $alumnosAfectados->get($alumnoId);
            $becaActiva = $alumno?->becaVigente();
            $becaPorcentaje = 0;
            $becaMonto = 0.00;
            $becaId = null;
            $montoAdeudo = round((float) $monto, 2);

            if ($becaActiva && $concepto->es_becable) {
                $becaPorcentaje = (int) $becaActiva->porcentaje;
                $becaMonto = round(((float) $monto) * ($becaPorcentaje / 100), 2);
                $becaId = $becaActiva->id;
                $montoAdeudo = max(round(((float) $monto) - $becaMonto, 2), 0);
            }

            $cargosInsert[] = [
                'alumno_id'         => $alumnoId,
                'concepto_id'       => $concepto->id,
                'beca_id'           => $becaId,
                'descripcion_cargo' => $descripcion,
                'monto_original'    => $monto,
                'beca_porcentaje_aplicado' => $becaPorcentaje,
                'beca_monto_aplicado' => $becaMonto,
                'monto_adeudo'      => $montoAdeudo,
                'fecha_vencimiento' => $fechaVencimiento,
                'estatus'           => $montoAdeudo <= 0 ? 'Pagado' : 'Pendiente',
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        if (!empty($cargosInsert)) {
            Cargo::insert($cargosInsert);

            foreach (collect($cargosInsert)->pluck('alumno_id')->unique() as $alumnoId) {
                $tieneAdeudo = Cargo::where('alumno_id', $alumnoId)
                    ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado', 'En Convenio'])
                    ->where('monto_adeudo', '>', 0)
                    ->exists();

                Alumno::where('id', $alumnoId)->update([
                    'estatus_financiero' => $tieneAdeudo ? 'Con Adeudo' : 'Al Corriente',
                ]);
            }
        }

        // Registrar resumen en tabla cargos_masivos
        $registro = CargoMasivo::create([
            'concepto_id'       => $concepto->id,
            'monto'             => $monto,
            'fecha_vencimiento' => $fechaVencimiento,
            'descripcion'       => $descripcion,
            'programa_id'       => $request->programa_id,
            'grupo_id'          => $request->grupo_id,
            'ciclo_escolar_id'  => $request->ciclo_id,
            'total_alumnos'     => count($cargosInsert),
            'usuario_id'        => Auth::id(),
        ]);

        // 🔥 BITÁCORA → Registrar cargos masivos
        $this->bitacora(
            'Cargos Masivos',
            "Se generaron cargos masivos para {$registro->total_alumnos} alumnos. "
            . "Concepto: {$concepto->nombre}, Monto base: {$monto}, "
            . "Fecha vencimiento: {$fechaVencimiento}."
        );

        return redirect()
            ->route('cargos.masivo.index')
            ->with('success', 'Cargos masivos generados correctamente: ' . count($cargosInsert));
    }

    /**
     * Detalle del registro masivo.
     * NO APLICA BITÁCORA.
     */
    public function show($id)
    {
        $cargoMasivo = CargoMasivo::with(['concepto', 'usuario'])
            ->findOrFail($id);

        $alumnos = Alumno::whereIn('id', function ($query) use ($cargoMasivo) {
                $query->select('alumno_id')
                    ->from('cargos')
                    ->where('concepto_id', $cargoMasivo->concepto_id)
                    ->where('fecha_vencimiento', $cargoMasivo->fecha_vencimiento);
            })
            ->with('grupo')
            ->orderBy('nombre_completo')
            ->paginate(20);

        return view('cargos.masivo.show', compact('cargoMasivo', 'alumnos'));
    }
}
