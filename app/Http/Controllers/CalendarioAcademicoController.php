<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcademico;
use App\Models\CalendarioSesion;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarioAcademicoController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $query = CalendarioAcademico::with(['grupo.programa', 'cicloEscolar'])->withCount('sesiones');

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('periodo')) {
            $query->where('periodo', 'like', '%' . $request->periodo . '%');
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $calendarios = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        return view('calendarios_academicos.index', [
            'calendarios' => $calendarios,
            'grupos' => Grupo::with('programa')->orderBy('nombre')->get(),
            'estatuses' => $this->estatuses(),
        ]);
    }

    public function create(Request $request)
    {
        return view('calendarios_academicos.create', array_merge($this->catalogos(), [
            'calendario' => new CalendarioAcademico(['grupo_id' => $request->grupo_id]),
        ]));
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $validated['creado_por_id'] = Auth::id();

        $validated['estatus'] = $this->estatusAutomatico($validated);
        $calendario = CalendarioAcademico::create($validated);

        $this->bitacora('Crear calendario académico', "Se creó el calendario {$calendario->nombre}.", 'Área Académica', $calendario);

        return redirect()->route('calendarios_academicos.show', $calendario)->with('success', 'Calendario académico creado correctamente.');
    }

    public function show(CalendarioAcademico $calendarioAcademico)
    {
        $calendarioAcademico->load([
            'grupo.programa',
            'grupo.cicloEscolar',
            'cicloEscolar',
            'materiasCalendario.materia',
            'materiasCalendario.docente',
            'materiasCalendario.sesiones',
        ]);

        $sesiones = CalendarioSesion::with(['calendarioMateria.materia', 'calendarioMateria.docente'])
            ->whereHas('calendarioMateria', fn ($q) => $q->where('calendario_academico_id', $calendarioAcademico->id))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $resumenPorFecha = $sesiones
            ->groupBy(fn ($sesion) => $sesion->fecha->toDateString())
            ->map(fn ($items) => [
                'fecha' => $items->first()->fecha,
                'total' => $items->count(),
                'materias' => $items->pluck('calendarioMateria.nombre_materia')->filter()->unique()->values(),
            ])
            ->values();

        return view('calendarios_academicos.show', [
            'calendario' => $calendarioAcademico,
            'sesiones' => $sesiones,
            'resumenPorFecha' => $resumenPorFecha,
        ]);
    }

    public function edit(CalendarioAcademico $calendarioAcademico)
    {
        return view('calendarios_academicos.edit', array_merge($this->catalogos(), [
            'calendario' => $calendarioAcademico,
        ]));
    }

    public function update(Request $request, CalendarioAcademico $calendarioAcademico)
    {
        $validated = $this->validar($request);

        $validated['estatus'] = $this->estatusAutomatico($validated);

        $calendarioAcademico->update($validated);

        $this->bitacora('Actualizar calendario académico', "Se actualizó el calendario {$calendarioAcademico->nombre}.", 'Área Académica', $calendarioAcademico);

        return redirect()->route('calendarios_academicos.show', $calendarioAcademico)->with('success', 'Calendario académico actualizado correctamente.');
    }

    public function destroy(CalendarioAcademico $calendarioAcademico)
    {
        $descripcion = "Se eliminó el calendario académico {$calendarioAcademico->nombre}.";
        $calendarioAcademico->delete();

        $this->bitacora('Eliminar calendario académico', $descripcion, 'Área Académica');

        return redirect()->route('calendarios_academicos.index')->with('success', 'Calendario eliminado correctamente.');
    }

    private function catalogos(): array
    {
        return [
            'grupos' => Grupo::with(['programa', 'cicloEscolar'])->orderBy('nombre')->get(),
            'ciclos' => CicloEscolar::orderByDesc('created_at')->get(),
            'modalidades' => [CalendarioAcademico::MODALIDAD_PRESENCIAL, CalendarioAcademico::MODALIDAD_VIRTUAL, CalendarioAcademico::MODALIDAD_MIXTA],
            'tiposCalendario' => CalendarioAcademico::tiposCalendario(),
            'estatuses' => $this->estatuses(),
        ];
    }

    private function validar(Request $request): array
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'ciclo_escolar_id' => 'nullable|exists:ciclos_escolares,id',
            'nombre' => 'required|string|max:255',
            'periodo' => 'nullable|string|max:50',
            'modalidad' => 'required|in:Presencial,Virtual,Mixta',
            'tipo_calendario' => 'required|in:'.implode(',', CalendarioAcademico::tiposCalendario()),
            'estatus' => 'required|in:Agendado,En curso,Finalizado,Cancelado',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:3000',
        ]);

        $ciclo = ! blank($validated['ciclo_escolar_id'] ?? null) ? CicloEscolar::find($validated['ciclo_escolar_id']) : null;

        if (blank($validated['periodo'] ?? null) && $ciclo) {
            $validated['periodo'] = $ciclo->nombre;
        }

        if ($ciclo && ! empty($validated['fecha_inicio'])) {
            $inicio = \Carbon\Carbon::parse($validated['fecha_inicio']);
            $fin = ! empty($validated['fecha_fin']) ? \Carbon\Carbon::parse($validated['fecha_fin']) : null;

            if ($ciclo->fecha_inicio_clases && $inicio->lt($ciclo->fecha_inicio_clases)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'fecha_inicio' => 'La fecha de inicio del calendario no puede estar dentro del periodo de inscripción ni antes del inicio de clases del ciclo escolar.',
                ]);
            }

            if ($ciclo->fecha_fin_clases && $fin && $fin->gt($ciclo->fecha_fin_clases)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'fecha_fin' => 'La fecha de fin del calendario no puede exceder la fecha final de clases del ciclo escolar.',
                ]);
            }
        }

        return $validated;
    }

    private function estatusAutomatico(array $validated): string
    {
        if (($validated['estatus'] ?? null) === CalendarioAcademico::ESTATUS_CANCELADO) {
            return CalendarioAcademico::ESTATUS_CANCELADO;
        }

        $hoy = now()->toDateString();
        $inicio = $validated['fecha_inicio'] ?? null;
        $fin = $validated['fecha_fin'] ?? null;

        if ($inicio && $hoy < $inicio) {
            return CalendarioAcademico::ESTATUS_AGENDADO;
        }
        if ($fin && $hoy > $fin) {
            return CalendarioAcademico::ESTATUS_FINALIZADO;
        }
        if ($inicio && $fin && $hoy >= $inicio && $hoy <= $fin) {
            return CalendarioAcademico::ESTATUS_EN_CURSO;
        }

        return CalendarioAcademico::ESTATUS_AGENDADO;
    }

    private function estatuses(): array
    {
        return [
            CalendarioAcademico::ESTATUS_AGENDADO,
            CalendarioAcademico::ESTATUS_EN_CURSO,
            CalendarioAcademico::ESTATUS_FINALIZADO,
            CalendarioAcademico::ESTATUS_CANCELADO,
        ];
    }
}
