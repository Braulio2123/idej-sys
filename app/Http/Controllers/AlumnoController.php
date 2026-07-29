<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Programa;
use App\Models\CicloEscolar;
use App\Models\Seguimiento;
use App\Models\DocumentoAlumno;
use App\Models\RequisitoDocumental;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\RegistraBitacora;

class AlumnoController extends Controller
{
    use RegistraBitacora;

    /**
     * LISTA DE ALUMNOS (CON FILTROS Y BÚSQUEDA)
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $estatusFinanciero = $request->estatus_financiero;
        $condicion = $request->condicion_alumno;
        $estatusAcademico = $request->estatus_academico;
        $programam = $request->programa;
        $grupoId = $request->grupo_id;

        $alumnos = Alumno::query()
            ->with(['grupo.programa'])

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_completo', 'like', "%{$search}%")
                      ->orWhere('correo', 'like', "%{$search}%")
                      ->orWhere('matricula', 'like', "%{$search}%")
                      ->orWhereHas('grupo', function ($grupoQuery) use ($search) {
                          $grupoQuery->where('nombre', 'like', "%{$search}%")
                              ->orWhereHas('programa', function ($programaQuery) use ($search) {
                                  $programaQuery->where('nombre', 'like', "%{$search}%")
                                      ->orWhere('nivel', 'like', "%{$search}%");
                              });
                      });
                });
            })

            ->when($estatusFinanciero, function ($query) use ($estatusFinanciero) {
                $query->where('estatus_financiero', $estatusFinanciero);
            })

            ->when($condicion, function ($query) use ($condicion) {
                $query->where('condicion_alumno', $condicion);
            })

            ->when($estatusAcademico, function ($query) use ($estatusAcademico) {
                $query->where('estatus_academico', $estatusAcademico);
            })

            ->when($programam, function ($query) use ($programam) {
                $query->whereHas('grupo.programa', function ($q) use ($programam) {
                    $q->where('nombre', $programam);
                });
            })

            ->when($grupoId, function ($query) use ($grupoId) {
                $query->where('grupo_id', $grupoId);
            })

            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $programas = Programa::orderBy('nombre')->get();
        $grupos = Grupo::with('programa')->orderBy('nombre')->get();

        return view('alumnos.index', compact(
            'alumnos',
            'search',
            'programas',
            'grupos'
        ));
    }


    /**
     * FORMULARIO DE CREACIÓN
     */
    public function create()
    {
        $cicloActivo = CicloEscolar::where('activo', true)->first();

        $grupos = $cicloActivo
            ? $cicloActivo->grupos()->activos()->with('programa', 'cicloEscolar')->get()
            : collect();

        $programas = Programa::all();

        return view('alumnos.create', compact('programas', 'grupos'));
    }


    /**
     * GUARDAR NUEVO ALUMNO
     */
    public function store(Request $request)
    {
        $esRecepcion = $request->user()?->rolClave() === Rol::RECEPCION;

        $reglas = [
            'matricula' => 'required|string|unique:alumnos,matricula',
            'nombre_completo' => 'required|string|max:255',
            'correo' => 'required|email|unique:alumnos,correo',
            'telefono' => 'nullable|string|max:20',
        ];

        if (! $esRecepcion) {
            $reglas += [
                'estatus_financiero' => ['required', Rule::in(['Al Corriente', 'Con Adeudo', 'En Convenio', 'Becado'])],
                'estatus_academico' => ['required', Rule::in(['Activo', 'Baja Temporal', 'Suspendido'])],
                'condicion_alumno' => ['nullable', Rule::in(['Normal', 'Becado', 'En Convenio'])],
                'grupo_id' => ['nullable', Rule::exists('grupos', 'id')->where('activo', true)],
            ];
        }

        $validated = $request->validate($reglas);

        if ($esRecepcion) {
            $validated += [
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => null,
            ];
        }

        $alumno = Alumno::create($validated);

        // 🔥 BITÁCORA → Crear alumno
        $this->bitacora(
            'Crear Alumno',
            "Se registró al alumno {$alumno->nombre_completo} (ID: {$alumno->id})."
        );

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno registrado correctamente.');
    }


    /**
     * FORMULARIO DE EDICIÓN
     */
    public function edit(Alumno $alumno)
    {
        $cicloActivo = CicloEscolar::where('activo', true)->first();

        $grupos = $cicloActivo
            ? $cicloActivo->grupos()->activos()->with('programa', 'cicloEscolar')->get()
            : collect();

        $programas = Programa::all();

        return view('alumnos.edit', compact('alumno', 'programas', 'grupos'));
    }


    /**
     * ACTUALIZAR ALUMNO
     */
    public function update(Request $request, Alumno $alumno)
    {
        $esRecepcion = $request->user()?->rolClave() === Rol::RECEPCION;

        $reglas = [
            'nombre_completo' => 'required|string|max:255',
            'correo' => 'required|email|unique:alumnos,correo,' . $alumno->id,
            'telefono' => 'nullable|string|max:20',
        ];

        if (! $esRecepcion) {
            $reglas += [
                'estatus_academico' => ['required', Rule::in(['Activo', 'Baja Temporal', 'Suspendido'])],
                'condicion_alumno' => ['nullable', Rule::in(['Normal', 'Becado', 'En Convenio'])],
                'grupo_id' => ['nullable', Rule::exists('grupos', 'id')->where('activo', true)],
            ];
        }

        $validated = $request->validate($reglas);
        $alumno->update($validated);

        if (! $esRecepcion) {
            $becaVigente = $alumno->becaVigente();
            if ($becaVigente) {
                $alumno->forceFill([
                    'beca_porcentaje' => $becaVigente->porcentaje,
                    'condicion_alumno' => 'Becado',
                ])->save();
            }
        }

        // 🔥 BITÁCORA → Actualizar alumno
        $this->bitacora(
            'Actualizar Alumno',
            "Se actualizó al alumno {$alumno->nombre_completo} (ID: {$alumno->id})."
        );

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno actualizado correctamente.');
    }


    /**
     * MOSTRAR FICHA DEL ALUMNO
     */
    public function show(Request $request, Alumno $alumno)
    {
        $alumno->load([
            'grupo.programa',
            'grupo.cicloEscolar',
            'cargos.concepto',
            'pagos.usuario',
            'convenios.parcialidades',
            'convenios.cargos.concepto',
            'seguimientos.usuario',
            'seguimientos.canceladoPor',
            'becas.autorizadoPor',
        ]);

        $cargos = $alumno->cargos()
            ->with('concepto')
            ->orderByDesc('created_at')
            ->limit(5)->get();

        $pagos = $alumno->pagos()
            ->with(['usuario', 'corteCaja'])
            ->orderByDesc('fecha_pago')
            ->limit(5)->get();

        $convenios = $alumno->convenios()
            ->with(['parcialidades', 'cargos.concepto'])
            ->orderByDesc('created_at')
            ->limit(3)->get();

        $seguimientos = $alumno->seguimientos()
            ->with(['usuario', 'canceladoPor'])
            ->orderByRaw('CASE WHEN fecha_proximo_contacto IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_proximo_contacto')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $seguimientosAbiertos = $alumno->seguimientos()
            ->whereIn('estatus', [Seguimiento::ESTATUS_ABIERTO, Seguimiento::ESTATUS_EN_PROCESO])
            ->count();

        $seguimientosVencidos = $alumno->seguimientos()
            ->vencidos()
            ->count();

        $usuario = $request->user();
        $documentosVisibles = fn () => $alumno->documentos()->visiblesPara($usuario);

        $documentos = $documentosVisibles()
            ->with(['usuarioSubio', 'usuarioReviso', 'requisitoDocumental'])
            ->orderByRaw("FIELD(estatus, 'Rechazado', 'Pendiente', 'Entregado', 'En revisión', 'Aceptado')")
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $documentosTotal = $documentosVisibles()->count();

        $documentosPendientes = $documentosVisibles()
            ->whereIn('estatus', [DocumentoAlumno::ESTATUS_PENDIENTE, DocumentoAlumno::ESTATUS_RECHAZADO])
            ->count();

        $documentosAceptados = $documentosVisibles()
            ->where('estatus', DocumentoAlumno::ESTATUS_ACEPTADO)
            ->count();

        $documentosEntregados = $documentosVisibles()
            ->whereIn('estatus', [
                DocumentoAlumno::ESTATUS_ENTREGADO,
                DocumentoAlumno::ESTATUS_EN_REVISION,
                DocumentoAlumno::ESTATUS_ACEPTADO,
            ])
            ->count();

        $documentosRechazados = $documentosVisibles()
            ->where('estatus', DocumentoAlumno::ESTATUS_RECHAZADO)
            ->count();

        $requisitosDocumentales = RequisitoDocumental::paraAlumno($alumno)
            ->get()
            ->filter(fn (RequisitoDocumental $requisito) => in_array(
                DocumentoAlumno::clasificacionParaTipo($requisito->tipo_documento),
                DocumentoAlumno::clasificacionesVisiblesPara($usuario),
                true
            ))
            ->count();
        $documentosEsperados = max($requisitosDocumentales, $documentosTotal);


        return view('alumnos.show', compact(
            'alumno',
            'cargos',
            'pagos',
            'convenios',
            'seguimientos',
            'seguimientosAbiertos',
            'seguimientosVencidos',
            'documentos',
            'documentosTotal',
            'documentosPendientes',
            'documentosAceptados',
            'documentosEntregados',
            'documentosRechazados',
            'documentosEsperados',
            'requisitosDocumentales'
        ));
    }


    /**
     * ELIMINAR ALUMNO
     */
    public function destroy(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'motivo_eliminacion' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'motivo_eliminacion.required' => 'Explica por qué el registro fue capturado por error y debe eliminarse.',
            'motivo_eliminacion.min' => 'El motivo debe describir claramente el error de captura.',
        ]);
        $tieneHistorialOperativo = $alumno->cargos()->exists()
            || $alumno->pagos()->exists()
            || $alumno->convenios()->exists()
            || $alumno->becas()->exists()
            || $alumno->seguimientos()->exists()
            || $alumno->documentos()->withTrashed()->exists()
            || $alumno->bitacoras()->exists()
            || $alumno->ajustesCaja()->exists()
            || $alumno->cursosEducacionContinua()->exists()
            || $alumno->prospectoOrigen()->exists();

        if ($tieneHistorialOperativo) {
            return back()->with('error', 'No se puede eliminar físicamente un alumno con historial operativo, financiero, documental o de seguimiento. Para baja institucional, cambia su estatus académico.');
        }

        $nombre = $alumno->nombre_completo;
        $id = $alumno->id;

        $alumno->delete();

        // 🔥 BITÁCORA → Eliminar alumno
        $this->bitacora(
            'Eliminar Alumno',
            "Se eliminó al alumno {$nombre} (ID: {$id}) sin historial operativo asociado. Motivo: {$validated['motivo_eliminacion']}"
        );

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno eliminado correctamente.');
    }


    // ===============================
    // MÓDULOS DETALLADOS DEL ALUMNO
    // ===============================

    public function cargosIndex(Alumno $alumno)
    {
        // No aplica bitácora
        $cargos = $alumno->cargos()
            ->with('concepto')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('alumnos.cargos_index', compact('alumno', 'cargos'));
    }

    public function pagosIndex(Alumno $alumno)
    {
        // No aplica bitácora
        $pagos = $alumno->pagos()
            ->with(['usuario', 'corteCaja'])
            ->orderByDesc('fecha_pago')
            ->paginate(15);

        return view('alumnos.pagos_index', compact('alumno', 'pagos'));
    }

    public function conveniosIndex(Alumno $alumno)
    {
        // No aplica bitácora
        $convenios = $alumno->convenios()
            ->with(['parcialidades', 'cargos.concepto'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('alumnos.convenios_index', compact('alumno', 'convenios'));
    }
}
