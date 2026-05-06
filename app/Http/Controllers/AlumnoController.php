<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Programa;
use App\Models\CicloEscolar;
use App\Models\Seguimiento;
use App\Models\DocumentoAlumno;
use App\Models\RequisitoDocumental;
use Illuminate\Http\Request;
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

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_completo', 'like', "%{$search}%")
                      ->orWhere('correo', 'like', "%{$search}%")
                      ->orWhere('matricula', 'like', "%{$search}%");
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
            ? $cicloActivo->grupos()->with('programa', 'cicloEscolar')->get()
            : collect();

        $programas = Programa::all();

        return view('alumnos.create', compact('programas', 'grupos'));
    }


    /**
     * GUARDAR NUEVO ALUMNO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricula'          => 'required|string|unique:alumnos,matricula',
            'nombre_completo'    => 'required|string|max:255',
            'correo'             => 'required|email|unique:alumnos,correo',
            'telefono'           => 'nullable|string|max:20',
            'estatus_financiero' => 'required|string',
            'estatus_academico'  => 'required|string',
            'condicion_alumno'   => 'nullable|string|max:255',
            'grupo_id'           => 'nullable|exists:grupos,id',
        ]);

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
            ? $cicloActivo->grupos()->with('programa', 'cicloEscolar')->get()
            : collect();

        $programas = Programa::all();

        return view('alumnos.edit', compact('alumno', 'programas', 'grupos'));
    }


    /**
     * ACTUALIZAR ALUMNO
     */
    public function update(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'nombre_completo'    => 'required|string|max:255',
            'correo'             => 'required|email|unique:alumnos,correo,' . $alumno->id,
            'telefono'           => 'nullable|string|max:20',
            'estatus_academico'  => 'required|string',
            'condicion_alumno'   => 'nullable|string|max:255',
            'grupo_id'           => 'nullable|exists:grupos,id',
        ]);

        $alumno->update($validated);

        $becaVigente = $alumno->becaVigente();
        if ($becaVigente) {
            $alumno->forceFill([
                'beca_porcentaje' => $becaVigente->porcentaje,
                'condicion_alumno' => 'Becado',
            ])->save();
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
    public function show(Alumno $alumno)
    {
        $alumno->load([
            'grupo.programa',
            'grupo.cicloEscolar',
            'cargos.concepto',
            'pagos.usuario',
            'convenios.parcialidades',
            'convenios.cargos.concepto',
            'seguimientos.usuario',
            'documentos.usuarioSubio',
            'documentos.usuarioReviso',
            'documentos.requisitoDocumental',
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
            ->with('usuario')
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

        $documentos = $alumno->documentos()
            ->with(['usuarioSubio', 'usuarioReviso', 'requisitoDocumental'])
            ->orderByRaw("FIELD(estatus, 'Rechazado', 'Pendiente', 'Entregado', 'En revisión', 'Aceptado')")
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $documentosTotal = $alumno->documentos()->count();

        $documentosPendientes = $alumno->documentos()
            ->whereIn('estatus', [DocumentoAlumno::ESTATUS_PENDIENTE, DocumentoAlumno::ESTATUS_RECHAZADO])
            ->count();

        $documentosAceptados = $alumno->documentos()
            ->where('estatus', DocumentoAlumno::ESTATUS_ACEPTADO)
            ->count();

        $requisitosDocumentales = RequisitoDocumental::paraAlumno($alumno)->count();


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
            'requisitosDocumentales'
        ));
    }


    /**
     * ELIMINAR ALUMNO
     */
    public function destroy(Alumno $alumno)
    {
        if ($alumno->cargos()->exists() || $alumno->pagos()->exists()) {
            return back()->with('error', 'No se puede eliminar un alumno con movimientos financieros.');
        }

        $nombre = $alumno->nombre_completo;
        $id = $alumno->id;

        $alumno->delete();

        // 🔥 BITÁCORA → Eliminar alumno
        $this->bitacora(
            'Eliminar Alumno',
            "Se eliminó al alumno {$nombre} (ID: {$id})."
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
