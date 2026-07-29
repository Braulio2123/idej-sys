<?php

namespace App\Http\Controllers;

use App\Models\CalendarioMateria;
use App\Models\CursoEducacionContinua;
use App\Models\CursoSesion;
use App\Models\CalendarioSesion;
use App\Models\Docente;
use App\Models\NotificacionInterna;
use App\Models\Rol;
use App\Models\SolicitudPagoDocente;
use App\Services\PrivateFileService;
use App\Services\TeacherPaymentCalculator;
use App\Traits\RegistraBitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SolicitudPagoDocenteController extends Controller
{
    use RegistraBitacora;

    public function __construct(
        private readonly PrivateFileService $privateFiles,
        private readonly TeacherPaymentCalculator $paymentCalculator,
    ) {
    }

    public function index(Request $request)
    {
        $rol = Auth::user()->rolClave();

        $query = SolicitudPagoDocente::with([
            'docente', 'creadoPor', 'valoradoPor', 'autorizadoPor', 'procesadoPor', 'rechazadoPor',
        ])
            ->orderByRaw("FIELD(estatus, 'Pendiente', 'Observada', 'Autorizada', 'Pagada', 'Rechazada', 'Cancelada')")
            ->orderByDesc('fecha_solicitud')
            ->orderByDesc('id');

        if ($rol === Rol::DIRECCION) {
            $query->whereNotIn('estatus', [SolicitudPagoDocente::ESTATUS_CANCELADA]);
        } elseif (! in_array($rol, [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA], true)) {
            $query->where('creado_por_id', Auth::id());
        }

        $query->when($request->filled('estatus'), fn ($q) => $q->where('estatus', $request->estatus));
        $query->when($request->filled('docente_id'), fn ($q) => $q->where('docente_id', $request->docente_id));
        $query->when($request->filled('tipo_clase'), fn ($q) => $q->where('tipo_clase', $request->tipo_clase));
        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = trim((string) $request->q);
            $q->where(function ($sub) use ($term) {
                $sub->where('folio', 'like', "%{$term}%")
                    ->orWhere('materia_actividad', 'like', "%{$term}%")
                    ->orWhere('programa_grupo', 'like', "%{$term}%")
                    ->orWhereHas('docente', fn ($d) => $d->where('nombre_completo', 'like', "%{$term}%"));
            });
        });

        return view('solicitudes_pago.index', [
            'solicitudes' => $query->paginate(15)->withQueryString(),
            'docentes' => Docente::orderBy('nombre_completo')->get(['id', 'nombre_completo']),
            'estatuses' => SolicitudPagoDocente::estatuses(),
            'tiposClase' => SolicitudPagoDocente::tiposClase(),
            'resumen' => [
                'pendientes' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PENDIENTE)->count(),
                'observadas' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_OBSERVADA)->count(),
                'autorizadas' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_AUTORIZADA)->count(),
                'pagadas_mes' => SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PAGADA)
                    ->whereDate('fecha_pago', '>=', now()->startOfMonth()->toDateString())->sum('monto'),
            ],
        ]);
    }

    public function create()
    {
        return view('solicitudes_pago.create', $this->formData(new SolicitudPagoDocente()));
    }

    public function store(Request $request)
    {
        $validated = $this->validarRegistroAcademico($request);

        $solicitud = DB::transaction(function () use ($validated) {
            $solicitud = SolicitudPagoDocente::create(array_merge($validated, [
                'folio' => null,
                'fecha_solicitud' => now()->toDateString(),
                'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
                'creado_por_id' => Auth::id(),
                'monto' => 0,
                'tarifa_hora' => null,
                'tarifa_unitaria' => null,
                'esquema_pago' => null,
                'concepto_pago' => null,
                'fecha_tentativa_pago' => null,
                'fecha_limite_pago' => null,
            ]));

            $solicitud->forceFill(['folio' => $this->generarFolio($solicitud)])->save();

            $this->bitacora(
                'Crear Solicitud de Pago Docente',
                "Académica registró la solicitud {$solicitud->folio} para {$solicitud->docente?->nombre_completo}.",
                'Solicitudes de Pago Docente',
                $solicitud
            );

            $this->notificarCAdmin(
                $solicitud,
                'solicitud_docente_nueva',
                'Nueva solicitud de pago docente',
                "Académica envió {$solicitud->folio} para {$solicitud->docente?->nombre_completo}. Requiere valoración y fecha tentativa.",
                NotificacionInterna::SEVERIDAD_ALTA
            );

            return $solicitud;
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud)
            ->with('success', 'Solicitud enviada a Coordinación Administrativa para valoración. Académica no asigna montos ni precios.');
    }

    public function show(SolicitudPagoDocente $solicitud_pago)
    {
        $solicitud_pago->load([
            'docente', 'creadoPor', 'valoradoPor', 'autorizadoPor', 'procesadoPor',
            'canceladoPor', 'rechazadoPor', 'calendarioMateria.calendario.grupo.programa',
            'calendarioMateria.materia', 'curso', 'cursoSesion',
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
        $validated = $this->validarRegistroAcademico($request);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if (! $solicitud->puedeEditarAcademica()) {
                throw ValidationException::withMessages([
                    'fechas_clase' => 'La solicitud ya fue valorada o cerrada y no puede modificarse desde Académica.',
                ]);
            }

            $solicitud->update(array_merge($validated, [
                'estatus' => SolicitudPagoDocente::ESTATUS_PENDIENTE,
                'motivo_observacion' => null,
                'motivo_rechazo' => null,
                'valorado_por_id' => null,
                'autorizado_por_id' => null,
                'fecha_valoracion' => null,
                'fecha_autorizacion' => null,
                'esquema_pago' => null,
                'tarifa_unitaria' => null,
                'tarifa_hora' => null,
                'monto' => 0,
                'concepto_pago' => null,
                'fecha_tentativa_pago' => null,
                'fecha_limite_pago' => null,
            ]));

            $this->bitacora(
                'Reenviar Solicitud de Pago Docente',
                "Académica corrigió y reenvió la solicitud {$solicitud->folio}.",
                'Solicitudes de Pago Docente',
                $solicitud
            );

            $this->notificarCAdmin(
                $solicitud,
                'solicitud_docente_corregida',
                'Solicitud docente corregida',
                "Académica corrigió {$solicitud->folio}. Está pendiente de una nueva valoración.",
                NotificacionInterna::SEVERIDAD_ALTA
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud corregida y reenviada a Coordinación Administrativa.');
    }

    public function formValorar(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if (! in_array($solicitud_pago->estatus, [
            SolicitudPagoDocente::ESTATUS_PENDIENTE,
            SolicitudPagoDocente::ESTATUS_AUTORIZADA,
        ], true)) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'Solo pueden valorarse solicitudes pendientes o autorizadas que aún no han sido pagadas.');
        }

        return view('solicitudes_pago.valorar', [
            'solicitud' => $solicitud_pago->load('docente'),
            'esquemasPago' => SolicitudPagoDocente::esquemasPago(),
            'prioridades' => SolicitudPagoDocente::prioridades(),
        ]);
    }

    public function valorar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();

        $validated = $request->validate([
            'esquema_pago' => ['required', Rule::in(SolicitudPagoDocente::esquemasPago())],
            'tarifa_unitaria' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'monto' => ['nullable', 'numeric', 'decimal:0,2', 'min:1', 'max:9999999.99'],
            'fecha_tentativa_pago' => ['required', 'date', 'after_or_equal:today'],
            'fecha_limite_pago' => ['nullable', 'date', 'after_or_equal:fecha_tentativa_pago'],
            'prioridad' => ['required', Rule::in(SolicitudPagoDocente::prioridades())],
            'observaciones_administracion' => ['nullable', 'string', 'max:1500'],
        ]);

        if ($validated['esquema_pago'] !== SolicitudPagoDocente::ESQUEMA_FIJO && empty($validated['tarifa_unitaria'])) {
            throw ValidationException::withMessages([
                'tarifa_unitaria' => 'Indica la tarifa unitaria cuando el pago se calcula por sesión o por hora.',
            ]);
        }

        if ($validated['esquema_pago'] === SolicitudPagoDocente::ESQUEMA_FIJO && empty($validated['monto'])) {
            throw ValidationException::withMessages([
                'monto' => 'Indica el monto fijo aprobado.',
            ]);
        }

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if (! in_array($solicitud->estatus, [
                SolicitudPagoDocente::ESTATUS_PENDIENTE,
                SolicitudPagoDocente::ESTATUS_AUTORIZADA,
            ], true)) {
                throw ValidationException::withMessages([
                    'monto' => 'La solicitud ya fue pagada, rechazada o cancelada y no puede valorarse nuevamente.',
                ]);
            }

            $esRevaloracion = $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA;

            try {
                $montoCalculado = $this->paymentCalculator->calculate(
                    $validated['esquema_pago'],
                    $validated['tarifa_unitaria'] ?? null,
                    count($solicitud->fechas_clase_ordenadas),
                    $solicitud->horas_totales,
                    $validated['monto'] ?? null,
                );
            } catch (\InvalidArgumentException $exception) {
                $campo = $validated['esquema_pago'] === SolicitudPagoDocente::ESQUEMA_FIJO
                    ? 'monto'
                    : 'tarifa_unitaria';

                throw ValidationException::withMessages([
                    $campo => $exception->getMessage(),
                ]);
            }

            $tarifaUnit = $validated['esquema_pago'] === SolicitudPagoDocente::ESQUEMA_FIJO
                ? null
                : ($validated['tarifa_unitaria'] ?? null);

            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_AUTORIZADA,
                'concepto_pago' => SolicitudPagoDocente::conceptoParaTipo($solicitud->tipo_clase),
                'esquema_pago' => $validated['esquema_pago'],
                'tarifa_unitaria' => $tarifaUnit,
                'tarifa_hora' => $validated['esquema_pago'] === SolicitudPagoDocente::ESQUEMA_HORA
                    ? $tarifaUnit : null,
                'monto' => $montoCalculado,
                'fecha_tentativa_pago' => $validated['fecha_tentativa_pago'],
                'fecha_limite_pago' => $validated['fecha_limite_pago'] ?? null,
                'prioridad' => $validated['prioridad'],
                'observaciones_administracion' => $validated['observaciones_administracion'] ?? null,
                'valorado_por_id' => Auth::id(),
                'autorizado_por_id' => Auth::id(),
                'fecha_valoracion' => now(),
                'fecha_autorizacion' => now(),
            ]);

            $tentativa = $solicitud->fecha_tentativa_pago?->format('d/m/Y') ?? 'por confirmar';

            $this->bitacora(
                $esRevaloracion ? 'Corregir Valoración de Pago Docente' : 'Valorar Solicitud de Pago Docente',
                "CAdmin ".($esRevaloracion ? 'corrigió la valoración de' : 'valoró')." {$solicitud->folio} por {$solicitud->esquema_pago}, con total de $".number_format((float) $solicitud->monto, 2)." y tentativa para {$tentativa}.",
                'Solicitudes de Pago Docente',
                $solicitud
            );

            $this->notificarAcademica(
                $solicitud,
                $esRevaloracion ? 'solicitud_docente_valoracion_corregida' : 'solicitud_docente_valorada',
                $esRevaloracion ? 'Valoración docente corregida' : 'Solicitud docente valorada',
                "CAdmin ".($esRevaloracion ? 'corrigió la valoración de' : 'valoró')." {$solicitud->folio}. Fecha tentativa de pago: {$tentativa}.",
                NotificacionInterna::SEVERIDAD_MEDIA
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Valoración calculada y guardada correctamente. Académica recibió la fecha tentativa de pago.');
    }

    /** Compatibilidad con formularios anteriores. */
    public function aprobar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        return $this->valorar($solicitud_pago, $request);
    }

    public function actualizarTentativa(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();

        $validated = $request->validate([
            'fecha_tentativa_pago' => ['required', 'date', 'after_or_equal:today'],
            'observaciones_administracion' => ['nullable', 'string', 'max:1500'],
        ]);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if ($solicitud->estatus !== SolicitudPagoDocente::ESTATUS_AUTORIZADA) {
                throw ValidationException::withMessages([
                    'fecha_tentativa_pago' => 'Solo puede actualizarse la tentativa de una solicitud autorizada y pendiente de pago.',
                ]);
            }

            $anterior = $solicitud->fecha_tentativa_pago?->format('d/m/Y') ?? 'sin fecha';
            $solicitud->update([
                'fecha_tentativa_pago' => $validated['fecha_tentativa_pago'],
                'observaciones_administracion' => $validated['observaciones_administracion'] ?? $solicitud->observaciones_administracion,
            ]);
            $nueva = $solicitud->fecha_tentativa_pago?->format('d/m/Y');

            $this->bitacora(
                'Reprogramar Tentativa de Pago Docente',
                "CAdmin cambió la tentativa de {$solicitud->folio} de {$anterior} a {$nueva}.",
                'Solicitudes de Pago Docente',
                $solicitud
            );

            $this->notificarAcademica(
                $solicitud,
                'solicitud_docente_tentativa_actualizada',
                'Fecha tentativa de pago actualizada',
                "CAdmin actualizó {$solicitud->folio}. Nueva tentativa: {$nueva}.",
                NotificacionInterna::SEVERIDAD_ALTA
            );
        }, 3);

        return back()->with('success', 'Fecha tentativa actualizada y notificada a Coordinación Académica.');
    }

    public function formObservar(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if (! in_array($solicitud_pago->estatus, [SolicitudPagoDocente::ESTATUS_PENDIENTE, SolicitudPagoDocente::ESTATUS_AUTORIZADA], true)) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'Esta solicitud ya no puede devolverse a Académica.');
        }

        return view('solicitudes_pago.observar', ['solicitud' => $solicitud_pago->load('docente')]);
    }

    public function observar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();
        $validated = $request->validate(['motivo_observacion' => 'required|string|min:8|max:1500']);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if (! in_array($solicitud->estatus, [SolicitudPagoDocente::ESTATUS_PENDIENTE, SolicitudPagoDocente::ESTATUS_AUTORIZADA], true)) {
                throw ValidationException::withMessages(['motivo_observacion' => 'La solicitud cambió de estado en otra pestaña.']);
            }

            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_OBSERVADA,
                'motivo_observacion' => $validated['motivo_observacion'],
                'valorado_por_id' => null,
                'autorizado_por_id' => null,
                'fecha_valoracion' => null,
                'fecha_autorizacion' => null,
                'esquema_pago' => null,
                'tarifa_unitaria' => null,
                'tarifa_hora' => null,
                'monto' => 0,
                'fecha_tentativa_pago' => null,
                'fecha_limite_pago' => null,
            ]);

            $this->bitacora('Observar Solicitud de Pago Docente', "CAdmin devolvió {$solicitud->folio} a Académica.", 'Solicitudes de Pago Docente', $solicitud);
            $this->notificarAcademica(
                $solicitud,
                'solicitud_docente_observada',
                'Solicitud docente con observaciones',
                "CAdmin devolvió {$solicitud->folio}: {$validated['motivo_observacion']}",
                NotificacionInterna::SEVERIDAD_ALTA
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud devuelta a Académica con observaciones.');
    }

    public function formRechazar(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if ($solicitud_pago->estaCerrada()) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'La solicitud ya está cerrada.');
        }

        return view('solicitudes_pago.rechazar', ['solicitud' => $solicitud_pago->load('docente')]);
    }

    public function rechazar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();
        $validated = $request->validate(['motivo_rechazo' => 'required|string|min:8|max:1500']);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

            if ($solicitud->estaCerrada()) {
                throw ValidationException::withMessages(['motivo_rechazo' => 'La solicitud ya está cerrada.']);
            }

            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_RECHAZADA,
                'rechazado_por_id' => Auth::id(),
                'fecha_rechazo' => now(),
                'motivo_rechazo' => $validated['motivo_rechazo'],
                'fecha_tentativa_pago' => null,
            ]);

            $this->bitacora('Rechazar Solicitud de Pago Docente', "CAdmin rechazó {$solicitud->folio}.", 'Solicitudes de Pago Docente', $solicitud);
            $this->notificarAcademica(
                $solicitud,
                'solicitud_docente_rechazada',
                'Solicitud docente no aprobada',
                "CAdmin decidió no ejecutar {$solicitud->folio}: {$validated['motivo_rechazo']}",
                NotificacionInterna::SEVERIDAD_ALTA
            );
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Solicitud rechazada y notificada a Coordinación Académica.');
    }

    public function formPagar(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if ($solicitud_pago->estatus !== SolicitudPagoDocente::ESTATUS_AUTORIZADA || (float) $solicitud_pago->monto <= 0) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)
                ->with('error', 'Solo pueden pagarse solicitudes valoradas y autorizadas con monto definido.');
        }

        return view('solicitudes_pago.pagar', [
            'solicitud' => $solicitud_pago->load(['docente', 'valoradoPor']),
            'metodosPago' => SolicitudPagoDocente::metodosPago(),
        ]);
    }

    public function pagar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        $this->autorizarRevisionAdministrativa();

        $validated = $request->validate([
            'fecha_pago' => 'required|date',
            'metodo_pago' => ['required', Rule::in(SolicitudPagoDocente::metodosPago())],
            'referencia_pago' => ['nullable', 'string', 'max:200', 'required_if:metodo_pago,Transferencia,Cheque,Tarjeta'],
            'banco_pago' => ['nullable', 'string', 'max:120', 'required_if:metodo_pago,Transferencia,Cheque,Tarjeta'],
            'comprobante_pago' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120', 'required_if:metodo_pago,Transferencia,Cheque,Tarjeta'],
            'observaciones_administracion' => 'nullable|string|max:1000',
            'pago_operacion_uuid' => ['required', 'uuid'],
        ]);

        $archivoGuardado = $request->hasFile('comprobante_pago')
            ? $this->privateFiles->store($request->file('comprobante_pago'), 'comprobantes/docentes', PrivateFileService::DOCUMENT_MIMES, 'comprobante_pago')
            : null;

        try {
            DB::transaction(function () use ($solicitud_pago, $validated, $archivoGuardado) {
                $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();

                if ($solicitud->estatus !== SolicitudPagoDocente::ESTATUS_AUTORIZADA || (float) $solicitud->monto <= 0) {
                    throw ValidationException::withMessages(['fecha_pago' => 'La solicitud ya no está disponible para pago.']);
                }

                $solicitud->update([
                    'fecha_pago' => $validated['fecha_pago'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'referencia_pago' => $validated['referencia_pago'] ?? null,
                    'banco_pago' => $validated['banco_pago'] ?? null,
                    'comprobante_pago_path' => $archivoGuardado['path'] ?? null,
                    'comprobante_pago_original' => $archivoGuardado['original_name'] ?? null,
                    'comprobante_pago_mime' => $archivoGuardado['mime_type'] ?? null,
                    'comprobante_pago_tamano' => $archivoGuardado['size'] ?? null,
                    'comprobante_pago_sha256' => $archivoGuardado['sha256'] ?? null,
                    'pago_operacion_uuid' => $validated['pago_operacion_uuid'],
                    'observaciones_administracion' => $validated['observaciones_administracion'] ?? $solicitud->observaciones_administracion,
                    'procesado_por_id' => Auth::id(),
                    'estatus' => SolicitudPagoDocente::ESTATUS_PAGADA,
                ]);

                $this->bitacora('Pagar Solicitud de Pago Docente', "CAdmin pagó {$solicitud->folio}.", 'Solicitudes de Pago Docente', $solicitud);
                $this->notificarAcademica(
                    $solicitud,
                    'solicitud_docente_pagada',
                    'Pago docente ejecutado',
                    "CAdmin registró el pago de {$solicitud->folio} para {$solicitud->docente?->nombre_completo} el {$solicitud->fecha_pago?->format('d/m/Y')}.",
                    NotificacionInterna::SEVERIDAD_MEDIA
                );
            }, 3);
        } catch (\Throwable $e) {
            $this->privateFiles->delete($archivoGuardado['path'] ?? null);
            throw $e;
        }

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)
            ->with('success', 'Pago registrado y notificado a Coordinación Académica.');
    }

    public function formCancelar(SolicitudPagoDocente $solicitud_pago)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }

        if ($solicitud_pago->estaCerrada()) {
            return redirect()->route('solicitudes_pago.show', $solicitud_pago)->with('error', 'La solicitud ya está cerrada.');
        }

        return view('solicitudes_pago.cancelar', ['solicitud' => $solicitud_pago->load('docente')]);
    }

    public function cancelar(SolicitudPagoDocente $solicitud_pago, Request $request)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }

        $validated = $request->validate(['motivo_cancelacion' => 'required|string|min:8|max:1500']);

        DB::transaction(function () use ($solicitud_pago, $validated) {
            $solicitud = SolicitudPagoDocente::whereKey($solicitud_pago->id)->lockForUpdate()->firstOrFail();
            if ($solicitud->estaCerrada()) {
                throw ValidationException::withMessages(['motivo_cancelacion' => 'La solicitud ya está cerrada.']);
            }
            $solicitud->update([
                'estatus' => SolicitudPagoDocente::ESTATUS_CANCELADA,
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => $validated['motivo_cancelacion'],
            ]);
            $this->bitacora('Cancelar Solicitud de Pago Docente', "Admin canceló {$solicitud->folio}.", 'Solicitudes de Pago Docente', $solicitud);
            $this->notificarAcademica($solicitud, 'solicitud_docente_cancelada', 'Solicitud docente cancelada', "Admin canceló {$solicitud->folio}.", NotificacionInterna::SEVERIDAD_ALTA);
        }, 3);

        return redirect()->route('solicitudes_pago.show', $solicitud_pago)->with('success', 'Solicitud cancelada.');
    }

    public function descargarComprobante(SolicitudPagoDocente $solicitud_pago)
    {
        if (! $solicitud_pago->comprobante_pago_path) {
            abort(404);
        }

        $path = $this->privateFiles->ensurePrivate($solicitud_pago->comprobante_pago_path);
        if (! $path) {
            $this->bitacora('Incidente Comprobante Pago Docente', "No existe el comprobante de {$solicitud_pago->folio}.", 'Seguridad de Archivos', $solicitud_pago);
            return back()->with('error', 'El comprobante no está disponible. El incidente quedó registrado.');
        }

        $sha256 = $this->privateFiles->sha256($path);
        if ($solicitud_pago->comprobante_pago_sha256 && (! $sha256 || ! hash_equals($solicitud_pago->comprobante_pago_sha256, $sha256))) {
            $this->bitacora('Incidente Integridad Pago Docente', "El comprobante de {$solicitud_pago->folio} no coincide con su huella.", 'Seguridad de Archivos', $solicitud_pago);
            return back()->with('error', 'El comprobante no superó la validación de integridad.');
        }
        if (! $solicitud_pago->comprobante_pago_sha256 && $sha256) {
            $solicitud_pago->forceFill(['comprobante_pago_sha256' => $sha256])->saveQuietly();
        }

        $this->bitacora('Descargar Comprobante Pago Docente', "Se descargó el comprobante de {$solicitud_pago->folio}.", 'Solicitudes de Pago Docente', $solicitud_pago);

        return $this->privateFiles->download($path, $solicitud_pago->comprobante_pago_original ?: 'comprobante-pago-docente-'.$solicitud_pago->id.'.pdf');
    }

    public function acusePago(SolicitudPagoDocente $solicitud_pago)
    {
        $this->autorizarRevisionAdministrativa();

        if ($solicitud_pago->estatus !== SolicitudPagoDocente::ESTATUS_PAGADA) {
            return back()->with('error', 'El formato se genera hasta que la solicitud está pagada.');
        }

        $solicitud_pago->load(['docente', 'creadoPor', 'valoradoPor', 'autorizadoPor', 'procesadoPor', 'calendarioMateria.calendario.grupo.programa', 'calendarioMateria.materia', 'curso', 'cursoSesion']);
        $pdf = Pdf::loadView('solicitudes_pago.acuse_pdf', ['solicitud' => $solicitud_pago])->setPaper('letter', 'portrait');
        $this->bitacora('Generar Formato de Pago Docente', "Se generó formato para {$solicitud_pago->folio}.", 'Solicitudes de Pago Docente', $solicitud_pago);

        return $pdf->stream('pago_docente_'.str_replace(['/', '\\', ' '], '_', $solicitud_pago->folio ?: $solicitud_pago->id).'.pdf');
    }

    public function destroy(SolicitudPagoDocente $solicitud_pago)
    {
        if (Auth::user()->rolClave() !== Rol::ADMIN) {
            abort(403);
        }
        if ($solicitud_pago->estatus === SolicitudPagoDocente::ESTATUS_PAGADA) {
            return back()->with('error', 'No puede eliminarse una solicitud pagada.');
        }

        DB::transaction(function () use ($solicitud_pago) {
            $folio = $solicitud_pago->folio ?: '#'.$solicitud_pago->id;
            $solicitud_pago->delete();
            $this->bitacora('Eliminar Solicitud de Pago Docente', "Solicitud {$folio} eliminada.", 'Solicitudes de Pago Docente');
        });

        return redirect()->route('solicitudes_pago.index')->with('success', 'Solicitud eliminada.');
    }

    private function formData(SolicitudPagoDocente $solicitud): array
    {
        return [
            'solicitud' => $solicitud,
            'docentes' => Docente::orderBy('nombre_completo')->get(['id', 'nombre_completo']),
            'tiposClase' => SolicitudPagoDocente::tiposClase(),
            'origenes' => SolicitudPagoDocente::origenes(),
            'modalidades' => ['Presencial', 'Virtual', 'Mixta'],
            'calendarioMaterias' => CalendarioMateria::with(['calendario.grupo.programa', 'materia', 'docente', 'sesiones'])
                ->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA])->orderByDesc('id')->limit(150)->get(),
            'cursos' => CursoEducacionContinua::query()
                ->where('estatus', '!=', CursoEducacionContinua::ESTATUS_CANCELADO)
                ->where(function ($query) {
                    $query->whereNull('fecha_fin')
                        ->orWhereDate('fecha_fin', '>=', now()->subYear()->toDateString());
                })
                ->with(['sesiones' => fn ($query) => $query->whereDate('fecha', '<=', today()->toDateString())
                    ->where('estatus', '!=', CursoSesion::ESTATUS_CANCELADA)
                    ->orderBy('fecha')])
                ->orderByDesc('fecha_inicio')
                ->get(['id', 'nombre', 'tipo', 'modalidad', 'fecha_inicio', 'fecha_fin']),
        ];
    }

    private function validarRegistroAcademico(Request $request): array
    {
        $validated = $request->validate([
            'docente_id' => ['required', 'exists:docentes,id'],
            'tipo_clase' => ['required', Rule::in(SolicitudPagoDocente::tiposClase())],
            'origen' => ['required', Rule::in(SolicitudPagoDocente::origenes())],
            'calendario_materia_id' => ['nullable', 'exists:calendario_materias,id'],
            'curso_id' => ['nullable', 'exists:cursos_educacion_continua,id'],
            'fechas_clase' => ['required', 'array', 'min:1', 'max:100'],
            'fechas_clase.*' => ['required', 'date', 'before_or_equal:today', 'distinct'],
            'programa_grupo' => ['nullable', 'string', 'max:180'],
            'materia_actividad' => ['required', 'string', 'max:220'],
            'periodo' => ['nullable', 'string', 'max:120'],
            'modalidad' => ['nullable', 'string', 'max:60'],
            'horas_totales' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'observaciones_academica' => ['nullable', 'string', 'max:1500'],
        ], [
            'fechas_clase.required' => 'Registra al menos una fecha en la que el docente impartió clase.',
            'fechas_clase.*.before_or_equal' => 'Las fechas deben corresponder a clases ya impartidas.',
            'fechas_clase.*.distinct' => 'No repitas una misma fecha de clase.',
        ]);

        $fechas = collect($validated['fechas_clase'])->map(fn ($fecha) => (string) $fecha)->unique()->sort()->values();
        $validated['fechas_clase'] = $fechas->all();
        $validated['numero_sesiones'] = $fechas->count();
        $validated['fecha_inicio_periodo'] = $fechas->first();
        $validated['fecha_fin_periodo'] = $fechas->last();
        $validated['nivel'] = $validated['tipo_clase'];
        $validated['observaciones'] = $validated['observaciones_academica'] ?? null;

        $validated['calendario_sesion_ids'] = null;
        $validated['curso_sesion_ids'] = null;
        $validated['curso_sesion_id'] = null;

        if ($validated['origen'] === SolicitudPagoDocente::ORIGEN_CALENDARIO) {
            $validated['curso_id'] = null;

            if (! empty($validated['calendario_materia_id'])) {
                $materia = CalendarioMateria::with('sesiones')->findOrFail($validated['calendario_materia_id']);

                if ($materia->docente_id && (int) $materia->docente_id !== (int) $validated['docente_id']) {
                    throw ValidationException::withMessages([
                        'docente_id' => 'El docente seleccionado no coincide con el docente asignado a la materia relacionada.',
                    ]);
                }

                $sesiones = $materia->sesiones
                    ->filter(fn ($sesion) => $sesion->fecha
                        && $sesion->fecha->lte(today())
                        && ! in_array($sesion->estatus, [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA], true))
                    ->filter(fn ($sesion) => in_array($sesion->fecha->format('Y-m-d'), $validated['fechas_clase'], true));

                $fechasRelacionadas = $sesiones->pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->unique()->sort()->values();
                $faltantes = collect($validated['fechas_clase'])->diff($fechasRelacionadas);

                if ($faltantes->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'fechas_clase' => 'Al relacionar una materia, todas las fechas deben corresponder a sesiones activas de esa materia. Fechas sin coincidencia: '.$faltantes->implode(', ').'.',
                    ]);
                }

                $validated['calendario_sesion_ids'] = $sesiones->pluck('id')->values()->all();
            }
        } elseif ($validated['origen'] === SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA) {
            $validated['calendario_materia_id'] = null;

            if (! empty($validated['curso_id'])) {
                $sesiones = CursoSesion::query()
                    ->where('curso_id', $validated['curso_id'])
                    ->where('docente_id', $validated['docente_id'])
                    ->whereDate('fecha', '<=', today()->toDateString())
                    ->where('estatus', '!=', CursoSesion::ESTATUS_CANCELADA)
                    ->whereIn('fecha', $validated['fechas_clase'])
                    ->orderBy('fecha')
                    ->get();

                $fechasRelacionadas = $sesiones->pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->unique()->sort()->values();
                $faltantes = collect($validated['fechas_clase'])->diff($fechasRelacionadas);

                if ($faltantes->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'fechas_clase' => 'Al relacionar un curso o diplomado, todas las fechas deben corresponder a sesiones impartidas por el docente seleccionado. Fechas sin coincidencia: '.$faltantes->implode(', ').'.',
                    ]);
                }

                $validated['curso_sesion_ids'] = $sesiones->pluck('id')->values()->all();
                $validated['curso_sesion_id'] = $sesiones->first()?->id;
            }
        } else {
            $validated['calendario_materia_id'] = null;
            $validated['curso_id'] = null;
        }

        return $validated;
    }

    private function autorizarEdicionAcademica(SolicitudPagoDocente $solicitud): void
    {
        $rol = Auth::user()->rolClave();
        if ($rol === Rol::ADMIN || ($rol === Rol::ACADEMICA && $solicitud->puedeEditarAcademica())) {
            return;
        }
        abort(403);
    }

    private function autorizarRevisionAdministrativa(): void
    {
        if (! in_array(Auth::user()->rolClave(), [Rol::ADMIN, Rol::CADMIN], true)) {
            abort(403);
        }
    }

    private function notificarCAdmin(SolicitudPagoDocente $solicitud, string $tipo, string $titulo, string $mensaje, string $severidad): void
    {
        $this->crearNotificacion($solicitud, $tipo, $titulo, $mensaje, $severidad, Rol::CADMIN);
    }

    private function notificarAcademica(SolicitudPagoDocente $solicitud, string $tipo, string $titulo, string $mensaje, string $severidad): void
    {
        $this->crearNotificacion($solicitud, $tipo, $titulo, $mensaje, $severidad, Rol::ACADEMICA);
    }

    private function crearNotificacion(SolicitudPagoDocente $solicitud, string $tipo, string $titulo, string $mensaje, string $severidad, string $rol): void
    {
        NotificacionInterna::create([
            'rol_clave' => $rol,
            'tipo' => $tipo,
            'modulo' => 'Solicitudes de Pago Docente',
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => route('solicitudes_pago.show', $solicitud, false),
            'severidad' => $severidad,
            'referencia_tipo' => SolicitudPagoDocente::class,
            'referencia_id' => $solicitud->id,
            'hash' => sha1('spd|'.$solicitud->id.'|'.$tipo.'|'.Str::uuid()),
            'metadata' => [
                'folio' => $solicitud->folio,
                'docente_id' => $solicitud->docente_id,
                'estatus' => $solicitud->estatus,
            ],
        ]);
    }

    private function generarFolio(SolicitudPagoDocente $solicitud): string
    {
        $fecha = optional($solicitud->fecha_solicitud)->format('Ym') ?: now()->format('Ym');
        return 'SPD-'.$fecha.'-'.str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT);
    }
}
