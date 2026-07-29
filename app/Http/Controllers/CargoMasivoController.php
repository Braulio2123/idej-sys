<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\CargoMasivo;
use App\Models\CicloEscolar;
use App\Models\ConceptoPago;
use App\Models\Grupo;
use App\Models\Programa;
use App\Traits\RegistraBitacora;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CargoMasivoController extends Controller
{
    use RegistraBitacora;

    public function index()
    {
        return view('cargos.masivo.index', [
            'conceptos' => ConceptoPago::orderBy('nombre')->get(),
            'programas' => Programa::orderBy('nombre')->get(),
            'grupos' => Grupo::activos()->with(['programa', 'cicloEscolar'])->orderBy('nombre')->get(),
            'ciclos' => CicloEscolar::orderBy('nombre')->get(),
            'historial' => CargoMasivo::with(['concepto', 'usuario'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function filtrarAlumnos(Request $request)
    {
        $query = Alumno::with(['grupo.programa', 'grupo.cicloEscolar'])
            ->where('estatus_academico', 'Activo')
            ->whereHas('grupo', fn ($grupo) => $grupo->where('activo', true));

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
                    'id' => $alumno->id,
                    'matricula' => $alumno->matricula,
                    'nombre_completo' => $alumno->nombre_completo,
                    'grupo' => optional($alumno->grupo)->nombre ?? '—',
                    'programa' => optional(optional($alumno->grupo)->programa)->nombre ?? '—',
                    'estatus_financiero' => $alumno->estatus_financiero,
                ];
            });

        return response()->json([
            'success' => true,
            'alumnos' => $alumnos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operacion_uuid' => ['required', 'uuid'],
            'concepto_id' => ['required', 'exists:conceptos_pagos,id'],
            'fecha_vencimiento' => ['required', 'date'],
            'monto' => ['nullable', 'numeric', 'min:0.01', 'max:99999999.99'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'alumnos' => ['required', 'array', 'min:1'],
            'alumnos.*' => ['integer', 'distinct', 'exists:alumnos,id'],
            'programa_id' => ['nullable', 'integer'],
            'grupo_id' => ['nullable', 'integer', Rule::exists('grupos', 'id')->where('activo', true)],
            'ciclo_id' => ['nullable', 'integer'],
        ], [
            'alumnos.required' => 'Debes seleccionar al menos un alumno.',
            'alumnos.*.distinct' => 'La selección contiene alumnos repetidos. Actualiza la página e intenta nuevamente.',
        ]);

        $operacionExistente = CargoMasivo::where('operacion_uuid', $validated['operacion_uuid'])->first();

        if ($operacionExistente) {
            return redirect()
                ->route('cargos.masivo.show', $operacionExistente)
                ->with('info', "La operación masiva #{$operacionExistente->id} ya había sido procesada. Se evitó duplicar los cargos.");
        }

        $concepto = ConceptoPago::findOrFail($validated['concepto_id']);
        $montoCapturado = round((float) ($validated['monto'] ?? $concepto->monto_base), 2);

        if ($montoCapturado <= 0) {
            throw ValidationException::withMessages([
                'monto' => 'El concepto no tiene un monto base válido. Captura un importe mayor a cero.',
            ]);
        }

        $montoBase = $concepto->montoConIvaEducacionContinua($montoCapturado);

        if ($montoBase > 99999999.99) {
            throw ValidationException::withMessages([
                'monto' => 'El monto final con impuestos excede el límite permitido.',
            ]);
        }

        $descripcion = ($validated['descripcion'] ?? null) ?: ('Cargo masivo: '.$concepto->nombre);

        if ($concepto->aplicaIvaEducacionContinua()) {
            $descripcion .= ' (incluye IVA 16% por Educación Continua)';
        }

        $alumnoIds = collect($validated['alumnos'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        try {
            $resultado = DB::transaction(function () use ($validated, $concepto, $montoCapturado, $montoBase, $descripcion, $alumnoIds) {
                $registro = CargoMasivo::create([
                    'operacion_uuid' => $validated['operacion_uuid'],
                    'concepto_id' => $concepto->id,
                    'monto' => $montoBase,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'],
                    'descripcion' => $descripcion,
                    'programa_id' => $validated['programa_id'] ?? null,
                    'grupo_id' => $validated['grupo_id'] ?? null,
                    'ciclo_escolar_id' => $validated['ciclo_id'] ?? null,
                    'total_alumnos' => 0,
                    'usuario_id' => Auth::id(),
                ]);

                $alumnos = Alumno::whereIn('id', $alumnoIds)
                    ->where('estatus_academico', 'Activo')
                    ->whereHas('grupo', fn ($query) => $query->where('activo', true))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($alumnos->isEmpty()) {
                    throw ValidationException::withMessages([
                        'alumnos' => 'Ninguno de los alumnos seleccionados continúa activo académicamente. No se generaron cargos.',
                    ]);
                }

                $generados = 0;
                $omitidos = $alumnoIds->count() - $alumnos->count();
                $saldoFavorTotal = 0.00;

                foreach ($alumnos as $alumno) {
                    $existe = Cargo::where('alumno_id', $alumno->id)
                        ->where('concepto_id', $concepto->id)
                        ->whereDate('fecha_vencimiento', $validated['fecha_vencimiento'])
                        ->exists();

                    if ($existe) {
                        $omitidos++;
                        continue;
                    }

                    $becaActiva = $alumno->becaVigente();
                    $becaPorcentaje = 0;
                    $becaMonto = 0.00;
                    $becaId = null;

                    if ($becaActiva && $concepto->es_becable) {
                        $becaPorcentaje = (int) $becaActiva->porcentaje;
                        $becaMonto = round($montoBase * ($becaPorcentaje / 100), 2);
                        $becaId = $becaActiva->id;
                    }

                    $montoAdeudo = max(round($montoBase - $becaMonto, 2), 0);
                    $saldoFavorAplicado = round(min((float) $alumno->saldo_a_favor, $montoAdeudo), 2);

                    if ($saldoFavorAplicado > 0) {
                        $montoAdeudo = max(round($montoAdeudo - $saldoFavorAplicado, 2), 0);
                        $saldoFavorTotal = round($saldoFavorTotal + $saldoFavorAplicado, 2);
                        $alumno->forceFill([
                            'saldo_a_favor' => max(round((float) $alumno->saldo_a_favor - $saldoFavorAplicado, 2), 0),
                        ])->save();
                    }

                    Cargo::create([
                        'cargo_masivo_id' => $registro->id,
                        'alumno_id' => $alumno->id,
                        'concepto_id' => $concepto->id,
                        'beca_id' => $becaId,
                        'descripcion_cargo' => $descripcion,
                        'monto_original' => $montoBase,
                        'beca_porcentaje_aplicado' => $becaPorcentaje,
                        'beca_monto_aplicado' => $becaMonto,
                        'saldo_favor_aplicado' => $saldoFavorAplicado,
                        'monto_adeudo' => $montoAdeudo,
                        'fecha_vencimiento' => $validated['fecha_vencimiento'],
                        'estatus' => $montoAdeudo <= 0 ? 'Pagado' : ($saldoFavorAplicado > 0 ? 'Parcialmente Pagado' : 'Pendiente'),
                    ]);

                    $alumno->forceFill([
                        'estatus_financiero' => $alumno->cargos()
                            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado', 'En Convenio'])
                            ->where('monto_adeudo', '>', 0)
                            ->exists()
                                ? 'Con Adeudo'
                                : 'Al Corriente',
                    ])->save();

                    $generados++;
                }

                $registro->forceFill(['total_alumnos' => $generados])->save();

                $this->bitacora(
                    'Cargos Masivos',
                    "Operación masiva #{$registro->id}: se generaron {$generados} cargos y se omitieron {$omitidos}. Concepto: {$concepto->nombre}. Monto por alumno: $".number_format($montoBase, 2).'. Saldo a favor aplicado en total: $'.number_format($saldoFavorTotal, 2).'.',
                    'Cargos',
                    $registro
                );

                return compact('registro', 'generados', 'omitidos', 'saldoFavorTotal');
            }, 3);
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                $operacionExistente = CargoMasivo::where('operacion_uuid', $validated['operacion_uuid'])->first();

                if ($operacionExistente) {
                    return redirect()
                        ->route('cargos.masivo.show', $operacionExistente)
                        ->with('info', "La operación masiva #{$operacionExistente->id} ya había sido procesada. Se evitó duplicar los cargos.");
                }
            }

            throw $e;
        }

        $mensaje = "Operación masiva #{$resultado['registro']->id} completada: {$resultado['generados']} cargo(s) generado(s).";

        if ($resultado['omitidos'] > 0) {
            $mensaje .= " {$resultado['omitidos']} alumno(s) fueron omitidos por inactividad o por tener el mismo concepto y vencimiento.";
        }

        if ($resultado['saldoFavorTotal'] > 0) {
            $mensaje .= ' Se aplicaron $'.number_format($resultado['saldoFavorTotal'], 2).' de saldos a favor.';
        }

        return redirect()
            ->route('cargos.masivo.show', $resultado['registro'])
            ->with('success', $mensaje);
    }

    public function show($id)
    {
        $cargoMasivo = CargoMasivo::with(['concepto', 'usuario'])->findOrFail($id);

        $alumnos = Alumno::whereHas('cargos', function ($query) use ($cargoMasivo) {
            $query->where('cargo_masivo_id', $cargoMasivo->id);
        })
            ->with('grupo')
            ->orderBy('nombre_completo')
            ->paginate(20);

        return view('cargos.masivo.show', compact('cargoMasivo', 'alumnos'));
    }
}
