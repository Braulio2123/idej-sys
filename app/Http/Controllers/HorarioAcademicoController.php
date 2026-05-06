<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Grupo;
use App\Models\HorarioAcademico;
use App\Models\Materia;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HorarioAcademicoController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $query = HorarioAcademico::with(['grupo.programa', 'materia', 'docente']);

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('docente_id')) {
            $query->where('docente_id', $request->docente_id);
        }

        if ($request->filled('dia_semana')) {
            $query->where('dia_semana', $request->dia_semana);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $ordenDias = "FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')";
        $horarios = $query
            ->orderByRaw($ordenDias)
            ->orderBy('hora_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('horarios_academicos.index', [
            'horarios' => $horarios,
            'grupos' => Grupo::with('programa')->orderBy('nombre')->get(),
            'docentes' => Docente::orderBy('nombre_completo')->get(),
            'dias' => HorarioAcademico::DIAS,
        ]);
    }

    public function create()
    {
        return view('horarios_academicos.create', $this->catalogos());
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $this->validarConflictos($validated);

        $horario = HorarioAcademico::create($validated);

        $this->bitacora(
            'Crear Horario Académico',
            "Se asignó {$horario->materia->nombre} al grupo {$horario->grupo->nombre} con docente {$horario->docente->nombre_completo}.",
            'Área Académica',
            $horario
        );

        return redirect()
            ->route('horarios_academicos.show', $horario)
            ->with('success', 'Horario/asignación registrado correctamente.');
    }

    public function show(HorarioAcademico $horarioAcademico)
    {
        $horarioAcademico->load(['grupo.programa', 'grupo.cicloEscolar', 'materia', 'docente']);

        return view('horarios_academicos.show', ['horario' => $horarioAcademico]);
    }

    public function edit(HorarioAcademico $horarioAcademico)
    {
        $horarioAcademico->load(['grupo', 'materia', 'docente']);

        return view('horarios_academicos.edit', array_merge(
            $this->catalogos(),
            ['horario' => $horarioAcademico]
        ));
    }

    public function update(Request $request, HorarioAcademico $horarioAcademico)
    {
        $validated = $this->validar($request);
        $this->validarConflictos($validated, $horarioAcademico->id);

        $horarioAcademico->update($validated);

        $this->bitacora(
            'Actualizar Horario Académico',
            "Se actualizó el horario/asignación ID {$horarioAcademico->id}.",
            'Área Académica',
            $horarioAcademico
        );

        return redirect()
            ->route('horarios_academicos.show', $horarioAcademico)
            ->with('success', 'Horario/asignación actualizado correctamente.');
    }

    public function destroy(HorarioAcademico $horarioAcademico)
    {
        $descripcion = "Se eliminó el horario {$horarioAcademico->dia_semana} {$horarioAcademico->horario}.";
        $horarioAcademico->delete();

        $this->bitacora('Eliminar Horario Académico', $descripcion, 'Área Académica');

        return redirect()
            ->route('horarios_academicos.index')
            ->with('success', 'Horario eliminado correctamente.');
    }

    private function catalogos(): array
    {
        return [
            'grupos' => Grupo::with(['programa', 'cicloEscolar'])->orderBy('nombre')->get(),
            'materias' => Materia::activas()->with('programa')->orderBy('nombre')->get(),
            'docentes' => Docente::where('estatus', 'Activo')->orderBy('nombre_completo')->get(),
            'dias' => HorarioAcademico::DIAS,
            'modalidades' => [HorarioAcademico::MODALIDAD_PRESENCIAL, HorarioAcademico::MODALIDAD_VIRTUAL, HorarioAcademico::MODALIDAD_MIXTA],
            'estatuses' => [HorarioAcademico::ESTATUS_ACTIVO, HorarioAcademico::ESTATUS_SUSPENDIDO, HorarioAcademico::ESTATUS_FINALIZADO],
        ];
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
            'docente_id' => 'required|exists:docentes,id',
            'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:80',
            'modalidad' => 'required|in:Presencial,Virtual,Mixta',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estatus' => 'required|in:Activo,Suspendido,Finalizado',
            'observaciones' => 'nullable|string|max:2000',
        ]);
    }

    private function validarConflictos(array $data, ?int $ignorarId = null): void
    {
        if (($data['estatus'] ?? null) !== HorarioAcademico::ESTATUS_ACTIVO) {
            return;
        }

        $base = HorarioAcademico::activos()
            ->where('dia_semana', $data['dia_semana'])
            ->where('hora_inicio', '<', $data['hora_fin'])
            ->where('hora_fin', '>', $data['hora_inicio'])
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId));

        if ((clone $base)->where('grupo_id', $data['grupo_id'])->exists()) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El grupo ya tiene una clase activa en ese día y horario.',
            ]);
        }

        if ((clone $base)->where('docente_id', $data['docente_id'])->exists()) {
            throw ValidationException::withMessages([
                'docente_id' => 'El docente ya tiene una clase activa en ese día y horario.',
            ]);
        }

        if (!empty($data['aula']) && (clone $base)->where('aula', $data['aula'])->exists()) {
            throw ValidationException::withMessages([
                'aula' => 'El aula ya está ocupada en ese día y horario.',
            ]);
        }
    }
}
