<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\ConceptoPago;
use App\Models\Grupo;
use App\Models\PlanCargoRecurrente;
use App\Models\Programa;
use App\Services\CargosRecurrentesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlanCargoRecurrenteController extends Controller
{
    public function index()
    {
        $planes = PlanCargoRecurrente::with(['concepto', 'programa', 'grupo', 'ejecuciones' => fn ($q) => $q->latest()->limit(5)])
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->paginate(15);

        return view('cargos.recurrentes.index', compact('planes'));
    }

    public function create()
    {
        return view('cargos.recurrentes.create', $this->catalogos());
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $validated['activo'] = $request->boolean('activo');
        $validated['enviar_recordatorio_email'] = $request->boolean('enviar_recordatorio_email');
        $validated['creado_por_id'] = Auth::id();
        $validated['actualizado_por_id'] = Auth::id();

        $plan = PlanCargoRecurrente::create($validated);

        $this->registrarBitacora('Crear plan de cargos recurrentes', "Se creó el plan {$plan->nombre}.", $plan);

        return redirect()->route('cargos.recurrentes.index')->with('success', 'Plan de cargos recurrentes creado correctamente.');
    }

    public function edit(PlanCargoRecurrente $recurrente)
    {
        return view('cargos.recurrentes.edit', array_merge($this->catalogos(), ['plan' => $recurrente]));
    }

    public function update(Request $request, PlanCargoRecurrente $recurrente)
    {
        $validated = $this->validar($request);
        $validated['activo'] = $request->boolean('activo');
        $validated['enviar_recordatorio_email'] = $request->boolean('enviar_recordatorio_email');
        $validated['actualizado_por_id'] = Auth::id();

        $recurrente->update($validated);

        $this->registrarBitacora('Actualizar plan de cargos recurrentes', "Se actualizó el plan {$recurrente->nombre}.", $recurrente);

        return redirect()->route('cargos.recurrentes.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(PlanCargoRecurrente $recurrente)
    {
        $recurrente->update([
            'activo' => false,
            'actualizado_por_id' => Auth::id(),
        ]);

        $this->registrarBitacora('Inactivar plan de cargos recurrentes', "Se inactivó el plan {$recurrente->nombre}.", $recurrente);

        return redirect()->route('cargos.recurrentes.index')->with('success', 'Plan inactivado. No se elimina para conservar historial.');
    }

    public function ejecutar(Request $request, PlanCargoRecurrente $recurrente, CargosRecurrentesService $service)
    {
        $validated = $request->validate([
            'dry_run' => ['nullable', 'boolean'],
            'fecha' => ['nullable', 'date'],
        ]);

        $resultado = $service->generar(
            $recurrente,
            isset($validated['fecha']) ? \Carbon\Carbon::parse($validated['fecha']) : now(),
            $request->boolean('dry_run')
        );

        $this->registrarBitacora(
            $request->boolean('dry_run') ? 'Simular cargos recurrentes' : 'Generar cargos recurrentes',
            "Plan {$recurrente->nombre}. Generados: {$resultado['generados']}. Simulados: {$resultado['simulados']}. Omitidos: {$resultado['omitidos']}.",
            $recurrente
        );

        $mensaje = $request->boolean('dry_run')
            ? "Simulación realizada. Se generarían {$resultado['simulados']} cargos. Omitidos: {$resultado['omitidos']}."
            : "Generación realizada. Cargos generados: {$resultado['generados']}. Omitidos: {$resultado['omitidos']}.";

        return redirect()->route('cargos.recurrentes.index')->with('success', $mensaje);
    }

    private function catalogos(): array
    {
        return [
            'conceptos' => ConceptoPago::orderBy('nombre')->get(),
            'programas' => Programa::orderBy('nombre')->get(),
            'grupos' => Grupo::activos()->with('programa')->orderBy('nombre')->get(),
        ];
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'concepto_id' => ['required', 'exists:conceptos_pagos,id'],
            'alcance' => ['required', Rule::in(['todos', 'programa', 'grupo'])],
            'programa_id' => ['nullable', 'required_if:alcance,programa', 'exists:programas,id'],
            'grupo_id' => ['nullable', 'required_if:alcance,grupo', Rule::exists('grupos', 'id')->where('activo', true)],
            'monto' => ['nullable', 'numeric', 'min:0'],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:28'],
            'frecuencia_meses' => ['required', 'integer', 'min:1', 'max:12'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
            'enviar_recordatorio_email' => ['nullable', 'boolean'],
        ], [
            'programa_id.required_if' => 'Selecciona el programa al que aplicará el plan.',
            'grupo_id.required_if' => 'Selecciona el grupo al que aplicará el plan.',
            'dia_vencimiento.max' => 'Usa un día del 1 al 28 para evitar problemas con meses cortos.',
        ]);
    }

    private function registrarBitacora(string $accion, string $descripcion, PlanCargoRecurrente $plan): void
    {
        try {
            Bitacora::create([
                'usuario_id' => Auth::id(),
                'tipo' => 'Visita',
                'accion' => $accion,
                'modulo' => 'Cargos recurrentes',
                'descripcion' => $descripcion,
                'modelo_type' => PlanCargoRecurrente::class,
                'modelo_id' => $plan->id,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'url' => request()?->fullUrl(),
                'metodo_http' => request()?->method(),
                'fecha_evento' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
