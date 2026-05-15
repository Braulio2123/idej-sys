<?php

namespace App\Http\Controllers;

use App\Models\CalendarioMateria;
use App\Models\CursoEducacionContinua;
use App\Models\CursoSesion;
use App\Models\Docente;
use App\Models\Rol;
use App\Models\SolicitudPagoDocente;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SolicitudPagoDocenteController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $rol = Auth::user()->rolClave();

        $query = SolicitudPagoDocente::with(['docente', 'creadoPor', 'autorizadoPor', 'procesadoPor'])
            ->orderByRaw("FIELD(estatus, 'Pendiente', 'Observada', 'Autorizada', 'Pagada', 'Cancelada')")
            ->orderByDesc('fecha_solicitud')
            ->orderByDesc('id');

        if ($rol === Rol::ACADEMICA) {
            $query->where(function ($q) {
                $q->where('creado_por_id', Auth::id())
                    ->orWhereIn('estatus', [
                        SolicitudPagoDocente::ESTATUS_PENDIENTE,
                        SolicitudPagoDocente::ESTATUS_OBSERVADA,
                        SolicitudPagoDocente::ESTATUS_AUTORIZADA,
                    ]);
            });
        } elseif ($rol === Rol::DIRECCION) {
            $query->whereNotIn('estatus', [SolicitudPagoDocente::ESTATUS_CANCELADA]);
        } elseif (!in_array($rol, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true)) {
            $query->where('creado_por_id', Auth::id());
        }

        $query->when($request->filled('estatus'), fn ($q) => $q->where('estatus', $request->estatus));
        $query->when($request->filled('docente_id'), fn ($q) => $q->where('docente_id', $request->docente_id));
        $query->when($request->filled('origen'), fn ($q) => $q->where('origen', $request->origen));
        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = trim($request->q);
            $q->where(function ($sub) use ($term) {
                $sub->where('folio', 'like', "%{$term}%")
                    ->orWhere('materia_actividad', 'like', "%{$term}%")
                    ->orWhere('programa_grupo', 'like', "%{$term}%")
                    ->orWhereHas('docente', fn ($d) => $d->where('nombre_completo', 'like', "%{$term}%"));
            });
        });

        $solicitudes = $query->paginate(15)->withQueryString();

        $resumen = [
            'pendientes' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PENDIENTE)->count(),
            'observadas' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_OBSERVADA)->count(),
            'autorizadas' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_AUTORIZADA)->count(),
            'pagadas_mes' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PAGADA)
                ->whereDate('fecha_pago', '>=', now()->startOfMonth()->toDateString())
                ->sum('monto'),
        ];

        return view('solicitudes_pago.index', [
            'solicitudes' => $solicitudes,
            'docentes' => Docente::orderBy('nombre_completo')->get(['id', 'nombre_completo']),
            'estatuses' => SolicitudPagoDocente::estatuses(),
            'origenes' => SolicitudPagoDocente::origenes(),
            'resumen' => $resumen,
        ]);
    }

    public function create()
    {
        return view('solicitudes_pago.create', $this->formData(new SolicitudPagoDocente()));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSolicitud($request);

        DB::transaction(function () use ($validated) {
            $solicitud = SolicitudPagoDocente::create(array_merge($validated, [
                'folio' => null,
                'fecha_solicitud' => $validated['fecha_solicitud'] ?? now()->toDateString(),
                'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
                'creado_por_id' => Auth::id(),
                'observaciones' => $validated['observaciones_academica'] ?? null,
            ]));

            $solicitud->forceFill([
                'folio' => $this->generarFolio($solicitud),
            ])->save();

            $this->bitacora(
                'Crear Solicitud de Pago Docente',
                "Solicitud {$solicitud->folio} creada para {$solicitud->docente?->nombre_completo} por {$solicitud->resumen_servicio}.",
                'Solicitudes de Pago Docente',
                $solicitud
            );
        }, 3);

        return redirect()->route('solicitudes_pago.index')
            ->with('success', 'Solicitud creada correctamente y enviada a Coordinación Administrativa/Finanzas para revisión.');
    }

    public function show(SolicitudPagoDocente $solicitud_pago)
    {
        $solicitud_pago->load([
            'docente',
            'creadoPor',
            'autorizadoPor',
            'procesadoPor',
            'canceladoPor',
            'calendarioMateria.calendario.grupo.programa',
            'calendarioMateria.materia',
            'curso',
            'cursoSesion',
        ]);

        return view('solicitudes_pago.show', ['solicitud' => $solicitud_pago]);
    }

    public function edit(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarEdicionAcademica($solicitud_pago);

        return view('solicitudes_pago.edit', $this->formData($solicitud_pago));
    }

    public function update(Request $request, SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarEdicionAcademica($solicitud_pago);

        $validated = $this->validateSolicitud($request, $solicitud_pago);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $nuevoEstatus = $solicitud_pago->estatus === SolicitudPagoDocente::ESTATUS_OBSERVADA
                ? SolicitudPagoDocente::ESTATUS_PENDIENTE
                : $solicitud_pago->estatus;

            $solicitud_pago->update(array_merge($validated, [
                'estatus' => $nuevoEstatus,
                'motivo_observacion' => $nuevoEstatus === SolicitudPagoDocente::ESTATUS_PENDIENTE ? null : $solicitud_pago->motivo_observacion,
                'observaciones' => $validated['observaciones_academica'] ?? $solicitud_pago->observaciones,
            ]));

            $this->bitacora(
                'Actualizar Solicitud de Pago Docente',
                "Solicitud {$solicitud_pago->folio} actualizada.",
                'Solicitudes de Pago Docente',
                $solicitud_pago
            );
        });

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud actualizada correctamente.');
    }

    public function aprobar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        if (!in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'observaciones_administracion' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if ($solicitud->estatus !== SolicitudPagoDocente::ESTATUS_PENDIENTE) {
                throw ValidationException::withMessages([
                    'observaciones_administracion' => 'Solo se pueden autorizar solicitudes pendientes. La solicitud pudo haber cambiado en otra pestaña.',
                ]);
            }

            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_AUTORIZADA,
                'autorizado_por_id' => Auth::id(),
                'fecha_autorizacion' => now(),
                'observaciones_administracion' => $validated['observaciones_administracion'] ?? $solicitud->observaciones_administracion,
            ]);

            $this->bitacora(
                'Autorizar Solicitud de Pago Docente',
                "Solicitud {$solicitud->folio} autorizada para pago.",
                'Solicitudes de Pago Docente',
                $solicitud
            );
        }, 3);

        return back()->with('success', 'Solicitud autorizada correctamente. Ahora puede registrarse el pago.');
    }

    public function formObservar(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if (!in_array($solicitud_pago->estatus, [SolicitudPagoDocente::ESTATUS_PENDIENTE, SolicitudPagoDocente::ESTATUS_AUTORIZADA], true)) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'Esta solicitud ya no puede marcarse como observada.');
        }

        return view('solicitudes_pago.observar', ['solicitud' => $solicitud_pago->load('docente')]);
    }

    public function observar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();

        $validated = $request->validate([
            'motivo_observacion' => 'required|string|min:8|max:1500',
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if (!in_array($solicitud->estatus, [SolicitudPagoDocente::ESTATUS_PENDIENTE, SolicitudPagoDocente::ESTATUS_AUTORIZADA], true)) {
                throw ValidationException::withMessages([
                    'motivo_observacion' => 'Esta solicitud ya no puede marcarse como observada. La solicitud pudo haber cambiado en otra pestaña.',
                ]);
            }

            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_OBSERVADA,
                'motivo_observacion' => $validated['motivo_observacion'],
                'autorizado_por_id' => null,
                'fecha_autorizacion' => null,
            ]);

            $this->bitacora(
                'Observar Solicitud de Pago Docente',
                "Solicitud {$solicitud->folio} devuelta a Académica con observaciones.",
                'Solicitudes de Pago Docente',
                $solicitud
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud marcada como observada. Académica podrá corregirla y reenviarla.');
    }

    public function formPagar(SolicitudPagoDocente $solicitud_pago)
    {
        if (!in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true)) {
            abort(403);
        }

        if ($solicitud_pago->estatus !== SolicitudPagoDocente::ESTATUS_AUTORIZADA) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'Solo se pueden pagar solicitudes autorizadas.');
        }

        return view('solicitudes_pago.pagar', [
            'solicitud' => $solicitud_pago->load(['docente', 'autorizadoPor']),
            'metodosPago' => SolicitudPagoDocente::metodosPago(),
        ]);
    }

    public function pagar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        if (!in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'fecha_pago' => 'required|date',
            'metodo_pago' => ['required', Rule::in(SolicitudPagoDocente::metodosPago())],
            'referencia_pago' => 'nullable|string|max:200',
            'banco_pago' => 'nullable|string|max:120',
            'comprobante_pago' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones_administracion' => 'nullable|string|max:1000',
            'pago_operacion_uuid' => ['required', 'uuid'],
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated, $request) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if ($solicitud->estatus !== SolicitudPagoDocente::ESTATUS_AUTORIZADA) {
                throw ValidationException::withMessages([
                    'fecha_pago' => 'Solo se pueden pagar solicitudes autorizadas. La solicitud pudo haber cambiado en otra pestaña.',
                ]);
            }

            $path = $solicitud->comprobante_pago_path;
            $original = $solicitud->comprobante_pago_original;

            if ($request->hasFile('comprobante_pago')) {
                $path = $request->file('comprobante_pago')->store('comprobantes/docentes', 'local');
                $original = $request->file('comprobante_pago')->getClientOriginalName();
            }

            $solicitud->update([
                'fecha_pago' => $validated['fecha_pago'],
                'metodo_pago' => $validated['metodo_pago'],
                'referencia_pago' => $validated['referencia_pago'] ?? null,
                'banco_pago' => $validated['banco_pago'] ?? null,
                'comprobante_pago_path' => $path,
                'comprobante_pago_original' => $original,
                'pago_operacion_uuid' => $validated['pago_operacion_uuid'],
                'observaciones_administracion' => $validated['observaciones_administracion'] ?? $solicitud->observaciones_administracion,
                'procesado_por_id' => Auth::id(),
                'estatus' => SolicitudPagoDocente::ESTATUS_PAGADA,
            ]);

            $this->bitacora(
                'Pagar Solicitud de Pago Docente',
                "Solicitud {$solicitud->folio} pagada por $".number_format((float) $solicitud->monto, 2).'.',
                'Solicitudes de Pago Docente',
                $solicitud
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Pago registrado correctamente.');
    }

    public function formCancelar(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if ($solicitud_pago->estatus === SolicitudPagoDocente::ESTATUS_PAGADA) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'Una solicitud pagada no debe cancelarse desde este flujo. Registra un ajuste administrativo de egresos.');
        }

        return view('solicitudes_pago.cancelar', ['solicitud' => $solicitud_pago->load('docente')]);
    }

    public function cancelar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();

        $validated = $request->validate([
            'motivo_cancelacion' => 'required|string|min:8|max:1500',
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if ($solicitud->estatus === SolicitudPagoDocente::ESTATUS_PAGADA) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'Una solicitud pagada no debe cancelarse desde este flujo.',
                ]);
            }

            if ($solicitud->estatus === SolicitudPagoDocente::ESTATUS_CANCELADA) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'Esta solicitud ya fue cancelada anteriormente.',
                ]);
            }

            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_CANCELADA,
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => $validated['motivo_cancelacion'],
            ]);

            $this->bitacora(
                'Cancelar Solicitud de Pago Docente',
                "Solicitud {$solicitud->folio} cancelada.",
                'Solicitudes de Pago Docente',
                $solicitud
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud cancelada correctamente.');
    }

    public function descargarComprobante(SolicitudPagoDocente $solicitud_pago)
    {
        if (! $solicitud_pago->comprobante_pago_path) {
            abort(404);
        }

        $disk = Storage::disk('local')->exists($solicitud_pago->comprobante_pago_path)
            ? 'local'
            : (Storage::disk('public')->exists($solicitud_pago->comprobante_pago_path) ? 'public' : null);

        if (! $disk) {
            abort(404);
        }

        $this->bitacora(
            'Descargar Comprobante Pago Docente',
            "Se descargó el comprobante de la solicitud {$solicitud_pago->folio}.",
            'Solicitudes de Pago Docente',
            $solicitud_pago
        );

        return Storage::disk($disk)->download(
            $solicitud_pago->comprobante_pago_path,
            $solicitud_pago->comprobante_pago_original ?: 'comprobante-pago-docente-'.$solicitud_pago->id
        );
    }

    public function destroy(SolicitudPagoDocente $solicitud_pago)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }

        if ($solicitud_pago->estatus === SolicitudPagoDocente::ESTATUS_PAGADA) {
            return back()->with('error', 'No se puede eliminar una solicitud pagada. Conserva la trazabilidad.');
        }

        DB::transaction(function () use ($solicitud_pago) {
            $folio = $solicitud_pago->folio ?: '#'.$solicitud_pago->id;
            $solicitud_pago->delete();

            $this->bitacora(
                'Eliminar Solicitud de Pago Docente',
                "Solicitud {$folio} eliminada.",
                'Solicitudes de Pago Docente'
            );
        });

        return redirect()->route('solicitudes_pago.index')
            ->with('success', 'Solicitud eliminada correctamente.');
    }

    private function formData(SolicitudPagoDocente $solicitud): array
    {
        $calendarioMaterias = CalendarioMateria::with(['calendario.grupo.programa', 'materia', 'docente'])
            ->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA])
            ->orderByDesc('id')
            ->limit(150)
            ->get();

        $cursos = CursoEducacionContinua::operativos()
            ->orderByDesc('fecha_inicio')
            ->get(['id', 'nombre', 'tipo', 'fecha_inicio', 'fecha_fin']);

        $cursoSesiones = CursoSesion::with(['curso', 'docente'])
            ->whereDate('fecha', '>=', now()->subMonths(2)->toDateString())
            ->orderBy('fecha')
            ->limit(200)
            ->get();

        return [
            'solicitud' => $solicitud,
            'docentes' => Docente::orderBy('nombre_completo')->get(),
            'origenes' => SolicitudPagoDocente::origenes(),
            'conceptos' => SolicitudPagoDocente::conceptos(),
            'prioridades' => SolicitudPagoDocente::prioridades(),
            'calendarioMaterias' => $calendarioMaterias,
            'cursos' => $cursos,
            'cursoSesiones' => $cursoSesiones,
            'niveles' => ['Licenciatura', 'Maestría', 'Doctorado', 'Posdoctorado', 'Educación continua', 'Otro'],
            'modalidades' => ['Presencial', 'Virtual', 'Mixta'],
        ];
    }

    private function validateSolicitud(Request $request, ?SolicitudPagoDocente $solicitud = null): array
    {
        $validated = $request->validate([
            'docente_id' => 'required|exists:docentes,id',
            'origen' => ['required', Rule::in(SolicitudPagoDocente::origenes())],
            'calendario_materia_id' => 'nullable|exists:calendario_materias,id',
            'curso_id' => 'nullable|exists:cursos_educacion_continua,id',
            'curso_sesion_id' => 'nullable|exists:curso_sesiones,id',
            'concepto_pago' => ['required', Rule::in(SolicitudPagoDocente::conceptos())],
            'nivel' => 'required|string|max:50',
            'programa_grupo' => 'nullable|string|max:180',
            'materia_actividad' => 'required|string|max:220',
            'periodo' => 'nullable|string|max:120',
            'modalidad' => 'nullable|string|max:60',
            'numero_sesiones' => 'nullable|integer|min:1|max:300',
            'horas_totales' => 'nullable|numeric|min:0|max:9999',
            'tarifa_hora' => 'nullable|numeric|min:0|max:999999',
            'monto' => 'required|numeric|min:1|max:9999999',
            'fecha_solicitud' => 'required|date',
            'fecha_inicio_periodo' => 'nullable|date',
            'fecha_fin_periodo' => 'nullable|date|after_or_equal:fecha_inicio_periodo',
            'fecha_limite_pago' => 'nullable|date',
            'prioridad' => ['required', Rule::in(SolicitudPagoDocente::prioridades())],
            'observaciones_academica' => 'nullable|string|max:1500',
        ]);

        if ($validated['origen'] === SolicitudPagoDocente::ORIGEN_CALENDARIO) {
            $validated['curso_id'] = null;
            $validated['curso_sesion_id'] = null;
        } elseif ($validated['origen'] === SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA) {
            $validated['calendario_materia_id'] = null;
        } else {
            $validated['calendario_materia_id'] = null;
            $validated['curso_id'] = null;
            $validated['curso_sesion_id'] = null;
        }

        return $validated;
    }

    private function autorizarEdicionAcademica(SolicitudPagoDocente $solicitud): void
    {
        $rol = Auth::user()->rolClave();

        if ($rol === Rol::ADMIN) {
            return;
        }

        if ($rol === Rol::ACADEMICA && $solicitud->puedeEditarAcademica()) {
            return;
        }

        abort(403);
    }

    private function autorizarRevisionAdministrativa(): void
    {
        if (!in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true)) {
            abort(403);
        }
    }

    private function generarFolio(SolicitudPagoDocente $solicitud): string
    {
        $fecha = optional($solicitud->fecha_solicitud)->format('Ym') ?: now()->format('Ym');

        return 'SPD-'.$fecha.'-'.str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT);
    }
}
