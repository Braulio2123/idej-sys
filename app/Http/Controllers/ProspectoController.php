<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Programa;
use App\Models\Prospecto;
use App\Models\Seguimiento;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProspectoController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $prospectos = Prospecto::query()
            ->with(['programa', 'asesor', 'alumno'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_completo', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%");
                });
            })
            ->when($request->estatus, fn ($query, $estatus) => $query->where('estatus', $estatus))
            ->when($request->programa_id, fn ($query, $programaId) => $query->where('programa_id', $programaId))
            ->when($request->medio_contacto, fn ($query, $medio) => $query->where('medio_contacto', $medio))
            ->when($request->asesor_id, fn ($query, $asesorId) => $query->where('asesor_id', $asesorId))
            ->when($request->vencidos, fn ($query) => $query->vencidos())
            ->orderByRaw("FIELD(prioridad, 'Urgente', 'Alta', 'Normal', 'Baja')")
            ->orderByRaw('CASE WHEN fecha_proximo_contacto IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_proximo_contacto')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->appends($request->query());

        return view('prospectos.index', [
            'prospectos' => $prospectos,
            'programas' => Programa::orderBy('nombre')->get(),
            'asesores' => Usuario::orderBy('nombre')->get(),
            'estatusDisponibles' => Prospecto::estatusDisponibles(),
            'mediosContacto' => Prospecto::mediosContacto(),
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('prospectos.create', $this->formData(new Prospecto([
            'estatus' => Prospecto::ESTATUS_NUEVO,
            'prioridad' => Prospecto::PRIORIDAD_NORMAL,
            'fecha_contacto' => now(),
            'asesor_id' => Auth::id(),
        ])));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProspecto($request);

        $prospecto = Prospecto::create($validated);

        $this->bitacora(
            'Crear Prospecto',
            "Se registró el prospecto {$prospecto->nombre_completo} (ID: {$prospecto->id}).",
            'Prospectos',
            $prospecto
        );

        return redirect()
            ->route('prospectos.show', $prospecto)
            ->with('success', 'Prospecto registrado correctamente.');
    }

    public function show(Prospecto $prospecto)
    {
        $prospecto->load(['programa', 'asesor', 'alumno', 'seguimientos.usuario']);

        $seguimientos = $prospecto->seguimientos()
            ->with('usuario')
            ->orderByRaw('CASE WHEN fecha_proximo_contacto IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_proximo_contacto')
            ->orderByDesc('created_at')
            ->get();

        $grupos = Grupo::with(['programa', 'cicloEscolar'])
            ->orderBy('nombre')
            ->get();

        return view('prospectos.show', [
            'prospecto' => $prospecto,
            'seguimientos' => $seguimientos,
            'grupos' => $grupos,
            'tiposSeguimiento' => Seguimiento::tipos(),
            'prioridadesSeguimiento' => Seguimiento::prioridades(),
            'estatusSeguimiento' => Seguimiento::estatusDisponibles(),
        ]);
    }

    public function edit(Prospecto $prospecto)
    {
        if ($prospecto->estaConvertido()) {
            return redirect()
                ->route('prospectos.show', $prospecto)
                ->with('error', 'No se puede editar un prospecto que ya fue convertido a alumno.');
        }

        return view('prospectos.edit', $this->formData($prospecto));
    }

    public function update(Request $request, Prospecto $prospecto)
    {
        if ($prospecto->estaConvertido()) {
            return redirect()
                ->route('prospectos.show', $prospecto)
                ->with('error', 'No se puede editar un prospecto que ya fue convertido a alumno.');
        }

        $validated = $this->validateProspecto($request, $prospecto);
        $prospecto->update($validated);

        $this->bitacora(
            'Actualizar Prospecto',
            "Se actualizó el prospecto {$prospecto->nombre_completo} (ID: {$prospecto->id}).",
            'Prospectos',
            $prospecto
        );

        return redirect()
            ->route('prospectos.show', $prospecto)
            ->with('success', 'Prospecto actualizado correctamente.');
    }

    public function destroy(Prospecto $prospecto)
    {
        if ($prospecto->estaConvertido()) {
            return back()->with('error', 'No se puede eliminar un prospecto convertido a alumno.');
        }

        $nombre = $prospecto->nombre_completo;
        $id = $prospecto->id;

        $prospecto->delete();

        $this->bitacora(
            'Eliminar Prospecto',
            "Se eliminó el prospecto {$nombre} (ID: {$id}).",
            'Prospectos'
        );

        return redirect()
            ->route('prospectos.index')
            ->with('success', 'Prospecto eliminado correctamente.');
    }

    public function storeSeguimiento(Request $request, Prospecto $prospecto)
    {
        if ($prospecto->estaConvertido()) {
            return back()->with('error', 'Este prospecto ya fue convertido a alumno. Registra seguimientos desde el expediente del alumno.');
        }

        $validated = $request->validate([
            'tipo' => ['required', Rule::in(Seguimiento::tipos())],
            'prioridad' => ['required', Rule::in(Seguimiento::prioridades())],
            'estatus' => ['required', Rule::in(Seguimiento::estatusDisponibles())],
            'asunto' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string'],
            'resultado' => ['nullable', 'string'],
            'fecha_proximo_contacto' => ['nullable', 'date'],
        ]);

        $seguimiento = $prospecto->seguimientos()->create([
            ...$validated,
            'usuario_id' => Auth::id(),
            'area' => Auth::user()?->rol?->nombre,
            'fecha_contacto' => now(),
            'fecha_cierre' => in_array($validated['estatus'], [Seguimiento::ESTATUS_CERRADO, Seguimiento::ESTATUS_CANCELADO], true) ? now() : null,
        ]);

        $nuevoEstatus = $this->estatusPosteriorAlSeguimiento($prospecto, $validated['estatus']);
        $prospecto->update([
            'estatus' => $nuevoEstatus,
            'prioridad' => $validated['prioridad'],
            'fecha_contacto' => now(),
            'fecha_proximo_contacto' => $validated['fecha_proximo_contacto'] ?? $prospecto->fecha_proximo_contacto,
        ]);

        $this->bitacora(
            'Crear Seguimiento de Prospecto',
            "Se registró seguimiento para el prospecto {$prospecto->nombre_completo} (ID: {$prospecto->id}).",
            'Prospectos',
            $seguimiento
        );

        return redirect()
            ->route('prospectos.show', $prospecto)
            ->with('success', 'Seguimiento registrado correctamente.');
    }

    public function convertirAlumno(Request $request, Prospecto $prospecto)
    {
        if ($prospecto->estaConvertido()) {
            return redirect()
                ->route('alumnos.show', $prospecto->alumno)
                ->with('success', 'Este prospecto ya estaba convertido a alumno.');
        }

        $validated = $request->validate([
            'matricula' => ['required', 'string', 'max:50', 'unique:alumnos,matricula'],
            'grupo_id' => ['nullable', 'exists:grupos,id'],
            'correo' => ['nullable', 'email', 'max:255', 'unique:alumnos,correo'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $alumno = DB::transaction(function () use ($validated, $prospecto) {
            $grupo = ! empty($validated['grupo_id'])
                ? Grupo::find($validated['grupo_id'])
                : null;

            $alumno = Alumno::create([
                'matricula' => $validated['matricula'],
                'nombre_completo' => $prospecto->nombre_completo,
                'correo' => $validated['correo'] ?? $prospecto->correo,
                'telefono' => $validated['telefono'] ?? $prospecto->telefono ?? $prospecto->whatsapp,
                'grupo_id' => $validated['grupo_id'] ?? null,
                'ciclo_escolar_id' => $grupo?->ciclo_escolar_id,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'beca_porcentaje' => 0,
                'saldo_a_favor' => 0,
            ]);

            Seguimiento::where('prospecto_id', $prospecto->id)
                ->update(['alumno_id' => $alumno->id]);

            $prospecto->update([
                'estatus' => Prospecto::ESTATUS_INSCRITO,
                'alumno_id' => $alumno->id,
                'fecha_conversion' => now(),
                'fecha_proximo_contacto' => null,
            ]);

            return $alumno;
        });

        $this->bitacora(
            'Convertir Prospecto a Alumno',
            "El prospecto {$prospecto->nombre_completo} (ID: {$prospecto->id}) fue convertido al alumno {$alumno->matricula}.",
            'Prospectos',
            $prospecto,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Prospecto convertido a alumno correctamente.');
    }

    private function formData(Prospecto $prospecto): array
    {
        return [
            'prospecto' => $prospecto,
            'programas' => Programa::orderBy('nombre')->get(),
            'asesores' => Usuario::orderBy('nombre')->get(),
            'estatusDisponibles' => Prospecto::estatusDisponibles(),
            'prioridades' => Prospecto::prioridades(),
            'mediosContacto' => Prospecto::mediosContacto(),
        ];
    }

    private function validateProspecto(Request $request, ?Prospecto $prospecto = null): array
    {
        return $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'programa_id' => ['nullable', 'exists:programas,id'],
            'nivel_interes' => ['nullable', 'string', 'max:80'],
            'medio_contacto' => ['nullable', 'string', 'max:80'],
            'origen' => ['nullable', 'string', 'max:120'],
            'asesor_id' => ['nullable', 'exists:usuarios,id'],
            'estatus' => ['required', Rule::in(Prospecto::estatusDisponibles())],
            'prioridad' => ['required', Rule::in(Prospecto::prioridades())],
            'fecha_contacto' => ['nullable', 'date'],
            'fecha_proximo_contacto' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
            'motivo_descarte' => ['nullable', 'string', 'required_if:estatus,'.Prospecto::ESTATUS_DESCARTADO],
        ]);
    }

    private function estatusPosteriorAlSeguimiento(Prospecto $prospecto, string $estatusSeguimiento): string
    {
        if (in_array($prospecto->estatus, [Prospecto::ESTATUS_INSCRITO, Prospecto::ESTATUS_DESCARTADO], true)) {
            return $prospecto->estatus;
        }

        if ($estatusSeguimiento === Seguimiento::ESTATUS_CERRADO) {
            return Prospecto::ESTATUS_CONTACTADO;
        }

        return Prospecto::ESTATUS_EN_SEGUIMIENTO;
    }
}
