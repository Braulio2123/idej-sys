<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\CursoAsistencia;
use App\Models\CursoEducacionContinua;
use App\Models\CursoInscrito;
use App\Models\CursoSesion;
use App\Models\Docente;
use App\Models\Prospecto;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CursoEducacionContinuaController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $query = CursoEducacionContinua::with(['responsable'])
            ->withCount(['sesiones', 'inscritos'])
            ->latest();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('tipo', 'like', "%{$buscar}%")
                    ->orWhere('modalidad', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $cursos = $query->paginate(12)->withQueryString();

        return view('educacion_continua.index', [
            'cursos' => $cursos,
            'tipos' => CursoEducacionContinua::tipos(),
            'estatuses' => CursoEducacionContinua::estatuses(),
        ]);
    }

    public function create()
    {
        return view('educacion_continua.create', $this->formData(new CursoEducacionContinua()));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedCurso($request);
        $validated['creado_por_id'] = Auth::id();
        $validated['equipo_requerido'] = $request->input('equipo_requerido', []);
        $validated['requiere_equipo'] = !empty($validated['equipo_requerido']);

        $resultadoPlaneador = null;
        $curso = null;

        DB::transaction(function () use ($request, $validated, &$curso, &$resultadoPlaneador) {
            $curso = CursoEducacionContinua::create($validated);

            if ($request->boolean('planeador_generar_sesiones')) {
                $resultadoPlaneador = $this->crearSesionesDesdePlaneador($request, $curso);
            }
        });

        $this->bitacora('Crear curso de educación continua', 'Se creó el curso '.$curso->nombre, 'Educación Continua', $curso);

        $mensaje = 'Curso de educación continua creado correctamente.';
        if ($resultadoPlaneador) {
            $mensaje .= ' Sesiones generadas: '.$resultadoPlaneador['creadas'].'. Horas programadas: '.number_format($resultadoPlaneador['horas'], 2).'h.';
            if ($resultadoPlaneador['omitidas'] > 0) {
                $mensaje .= ' Sesiones omitidas por duplicado: '.$resultadoPlaneador['omitidas'].'.';
            }
        }

        return redirect()
            ->route('educacion_continua.show', $curso)
            ->with('success', $mensaje);
    }

    public function show(CursoEducacionContinua $educacionContinua)
    {
        $curso = $educacionContinua->load([
            'responsable',
            'sesiones.docente',
            'inscritos.alumno',
            'inscritos.prospecto',
            'inscritos.asistencias',
        ]);

        $horasProgramadas = $curso->horasProgramadas();
        $horasImpartidas = $curso->horasImpartidas();
        $inscritosActivos = $curso->inscritos->where('estatus', CursoInscrito::ESTATUS_INSCRITO)->count();

        return view('educacion_continua.show', [
            'curso' => $curso,
            'horasProgramadas' => $horasProgramadas,
            'horasImpartidas' => $horasImpartidas,
            'inscritosActivos' => $inscritosActivos,
            'docentes' => Docente::where('estatus', 'Activo')->orderBy('nombre_completo')->get(),
            'alumnos' => Alumno::orderBy('nombre_completo')->limit(250)->get(),
            'prospectos' => Prospecto::activos()->orderBy('nombre_completo')->limit(250)->get(),
            'modalidades' => CursoEducacionContinua::modalidades(),
            'estatusesSesion' => CursoSesion::estatuses(),
            'estatusesInscrito' => CursoInscrito::estatuses(),
            'tiposParticipante' => CursoInscrito::tiposParticipante(),
            'equipos' => CursoEducacionContinua::equiposDisponibles(),
        ]);
    }

    public function edit(CursoEducacionContinua $educacionContinua)
    {
        return view('educacion_continua.edit', $this->formData($educacionContinua));
    }

    public function update(Request $request, CursoEducacionContinua $educacionContinua)
    {
        $validated = $this->validatedCurso($request);
        $validated['equipo_requerido'] = $request->input('equipo_requerido', []);
        $validated['requiere_equipo'] = !empty($validated['equipo_requerido']);

        $resultadoPlaneador = null;

        DB::transaction(function () use ($request, $educacionContinua, $validated, &$resultadoPlaneador) {
            $educacionContinua->update($validated);

            if ($request->boolean('planeador_generar_sesiones')) {
                $resultadoPlaneador = $this->crearSesionesDesdePlaneador($request, $educacionContinua->fresh());
            }
        });

        $this->bitacora('Actualizar curso de educación continua', 'Se actualizó el curso '.$educacionContinua->nombre, 'Educación Continua', $educacionContinua);

        $mensaje = 'Curso actualizado correctamente.';
        if ($resultadoPlaneador) {
            $mensaje .= ' Sesiones generadas: '.$resultadoPlaneador['creadas'].'. Horas programadas: '.number_format($resultadoPlaneador['horas'], 2).'h.';
            if ($resultadoPlaneador['omitidas'] > 0) {
                $mensaje .= ' Sesiones omitidas por duplicado: '.$resultadoPlaneador['omitidas'].'.';
            }
        }

        return redirect()
            ->route('educacion_continua.show', $educacionContinua)
            ->with('success', $mensaje);
    }

    public function destroy(CursoEducacionContinua $educacionContinua)
    {
        if ($educacionContinua->sesiones()->where('estatus', CursoSesion::ESTATUS_IMPARTIDA)->exists()) {
            return back()->with('error', 'No se puede eliminar un curso con sesiones impartidas. Cámbialo a cancelado o finalizado.');
        }

        $nombre = $educacionContinua->nombre;
        $educacionContinua->delete();

        $this->bitacora('Eliminar curso de educación continua', 'Se eliminó el curso '.$nombre, 'Educación Continua');

        return redirect()->route('educacion_continua.index')->with('success', 'Curso eliminado correctamente.');
    }

    public function storeSesion(Request $request, CursoEducacionContinua $educacionContinua)
    {
        $validated = $this->validatedSesion($request);
        $validated['duracion_horas'] = $this->calcularDuracion($validated['hora_inicio'], $validated['hora_fin']);
        $validated['equipo_requerido'] = $request->input('equipo_requerido', []);
        $validated['requiere_equipo'] = !empty($validated['equipo_requerido']);

        if ($validated['duracion_horas'] <= 0) {
            return back()->withInput()->with('error', 'La hora fin debe ser mayor que la hora inicio.');
        }

        $sesion = $educacionContinua->sesiones()->create($validated);

        $this->bitacora('Crear sesión de educación continua', 'Se programó sesión del curso '.$educacionContinua->nombre.' para '.$sesion->fecha->format('d/m/Y'), 'Educación Continua', $sesion);

        return redirect()->route('educacion_continua.show', $educacionContinua)->with('success', 'Sesión agregada correctamente.');
    }

    public function updateSesion(Request $request, CursoEducacionContinua $educacionContinua, CursoSesion $sesion)
    {
        $this->validarSesionPerteneceCurso($educacionContinua, $sesion);

        $validated = $this->validatedSesion($request);
        $validated['duracion_horas'] = $this->calcularDuracion($validated['hora_inicio'], $validated['hora_fin']);
        $validated['equipo_requerido'] = $request->input('equipo_requerido', []);
        $validated['requiere_equipo'] = !empty($validated['equipo_requerido']);

        if ($validated['duracion_horas'] <= 0) {
            return back()->withInput()->with('error', 'La hora fin debe ser mayor que la hora inicio.');
        }

        $sesion->update($validated);

        $this->bitacora('Actualizar sesión de educación continua', 'Se actualizó una sesión del curso '.$educacionContinua->nombre, 'Educación Continua', $sesion);

        return redirect()->route('educacion_continua.show', $educacionContinua)->with('success', 'Sesión actualizada correctamente.');
    }

    public function destroySesion(CursoEducacionContinua $educacionContinua, CursoSesion $sesion)
    {
        $this->validarSesionPerteneceCurso($educacionContinua, $sesion);

        if ($sesion->asistencias()->exists()) {
            return back()->with('error', 'No se puede eliminar una sesión que ya tiene asistencia. Cancélala si ya no se impartirá.');
        }

        $sesion->delete();

        $this->bitacora('Eliminar sesión de educación continua', 'Se eliminó una sesión del curso '.$educacionContinua->nombre, 'Educación Continua');

        return back()->with('success', 'Sesión eliminada correctamente.');
    }

    public function storeInscrito(Request $request, CursoEducacionContinua $educacionContinua)
    {
        $validated = $request->validate([
            'tipo_participante' => ['required', Rule::in(CursoInscrito::tiposParticipante())],
            'alumno_id' => ['nullable', 'required_if:tipo_participante,'.CursoInscrito::TIPO_ALUMNO, 'exists:alumnos,id'],
            'prospecto_id' => ['nullable', 'required_if:tipo_participante,'.CursoInscrito::TIPO_PROSPECTO, 'exists:prospectos,id'],
            'nombre_externo' => ['nullable', 'required_if:tipo_participante,'.CursoInscrito::TIPO_EXTERNO, 'string', 'max:180'],
            'correo_externo' => ['nullable', 'email', 'max:180'],
            'telefono_externo' => ['nullable', 'string', 'max:40'],
            'fecha_inscripcion' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $validated['estatus'] = CursoInscrito::ESTATUS_INSCRITO;
        $validated['fecha_inscripcion'] = $validated['fecha_inscripcion'] ?? now()->toDateString();

        $this->validarInscritoDuplicado($educacionContinua, $validated);

        $inscrito = $educacionContinua->inscritos()->create($validated);

        $this->bitacora('Inscribir participante a educación continua', 'Se inscribió a '.$inscrito->nombre.' en '.$educacionContinua->nombre, 'Educación Continua', $inscrito);

        return redirect()->route('educacion_continua.show', $educacionContinua)->with('success', 'Participante inscrito correctamente.');
    }

    public function updateInscrito(Request $request, CursoEducacionContinua $educacionContinua, CursoInscrito $inscrito)
    {
        $this->validarInscritoPerteneceCurso($educacionContinua, $inscrito);

        $validated = $request->validate([
            'estatus' => ['required', Rule::in(CursoInscrito::estatuses())],
            'observaciones' => ['nullable', 'string'],
        ]);

        $inscrito->update($validated);

        $this->bitacora('Actualizar inscrito de educación continua', 'Se actualizó al participante '.$inscrito->nombre.' en '.$educacionContinua->nombre, 'Educación Continua', $inscrito);

        return back()->with('success', 'Participante actualizado correctamente.');
    }

    public function destroyInscrito(CursoEducacionContinua $educacionContinua, CursoInscrito $inscrito)
    {
        $this->validarInscritoPerteneceCurso($educacionContinua, $inscrito);

        if ($inscrito->asistencias()->exists()) {
            return back()->with('error', 'No se puede eliminar un inscrito con asistencia registrada. Cámbialo a baja si ya no continuará.');
        }

        $inscrito->delete();

        $this->bitacora('Eliminar inscrito de educación continua', 'Se eliminó un participante de '.$educacionContinua->nombre, 'Educación Continua');

        return back()->with('success', 'Participante eliminado correctamente.');
    }

    public function asistencia(CursoEducacionContinua $educacionContinua, CursoSesion $sesion)
    {
        $this->validarSesionPerteneceCurso($educacionContinua, $sesion);

        $sesion->load('asistencias.inscrito');
        $inscritos = $educacionContinua->inscritos()
            ->with(['alumno', 'prospecto', 'asistencias' => fn ($q) => $q->where('curso_sesion_id', $sesion->id)])
            ->orderBy('tipo_participante')
            ->get();

        return view('educacion_continua.asistencia', [
            'curso' => $educacionContinua,
            'sesion' => $sesion,
            'inscritos' => $inscritos,
            'estatuses' => CursoAsistencia::estatuses(),
        ]);
    }

    public function guardarAsistencia(Request $request, CursoEducacionContinua $educacionContinua, CursoSesion $sesion)
    {
        $this->validarSesionPerteneceCurso($educacionContinua, $sesion);

        $validated = $request->validate([
            'asistencias' => ['array'],
            'asistencias.*.curso_inscrito_id' => ['required', 'exists:curso_inscritos,id'],
            'asistencias.*.estatus' => ['required', Rule::in(CursoAsistencia::estatuses())],
            'asistencias.*.horas_acreditadas' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'asistencias.*.observaciones' => ['nullable', 'string'],
            'marcar_impartida' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $educacionContinua, $sesion) {
            foreach ($validated['asistencias'] ?? [] as $row) {
                $inscrito = CursoInscrito::where('curso_id', $educacionContinua->id)->findOrFail($row['curso_inscrito_id']);
                $estatus = $row['estatus'];
                $horas = in_array($estatus, [CursoAsistencia::ESTATUS_ASISTIO, CursoAsistencia::ESTATUS_RETARDO, CursoAsistencia::ESTATUS_JUSTIFICADO], true)
                    ? ($row['horas_acreditadas'] ?? $sesion->duracion_horas)
                    : 0;

                CursoAsistencia::updateOrCreate(
                    [
                        'curso_sesion_id' => $sesion->id,
                        'curso_inscrito_id' => $inscrito->id,
                    ],
                    [
                        'estatus' => $estatus,
                        'horas_acreditadas' => $horas,
                        'observaciones' => $row['observaciones'] ?? null,
                        'registrado_por_id' => Auth::id(),
                    ]
                );
            }

            if (request()->boolean('marcar_impartida')) {
                $sesion->update(['estatus' => CursoSesion::ESTATUS_IMPARTIDA]);
            }
        });

        $this->bitacora('Registrar asistencia de educación continua', 'Se registró asistencia de la sesión '.$sesion->fecha->format('d/m/Y').' del curso '.$educacionContinua->nombre, 'Educación Continua', $sesion);

        return redirect()->route('educacion_continua.show', $educacionContinua)->with('success', 'Asistencia guardada correctamente.');
    }

    private function formData(CursoEducacionContinua $curso): array
    {
        return [
            'curso' => $curso,
            'tipos' => CursoEducacionContinua::tipos(),
            'modalidades' => CursoEducacionContinua::modalidades(),
            'estatuses' => CursoEducacionContinua::estatuses(),
            'equipos' => CursoEducacionContinua::equiposDisponibles(),
            'usuarios' => Usuario::orderBy('nombre')->get(),
            'docentes' => Docente::where('estatus', 'Activo')->orderBy('nombre_completo')->get(),
            'estatusesSesion' => CursoSesion::estatuses(),
            'diasSemana' => $this->diasSemanaPlaneador(),
            'horariosPredefinidos' => $this->horariosPredefinidosPlaneador(),
            'plantillasPlaneador' => $this->plantillasPlaneador(),
        ];
    }

    private function validatedCurso(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:180'],
            'tipo' => ['required', Rule::in(CursoEducacionContinua::tipos())],
            'modalidad' => ['required', Rule::in(CursoEducacionContinua::modalidades())],
            'horas_totales' => ['required', 'numeric', 'min:0', 'max:999'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estatus' => ['required', Rule::in(CursoEducacionContinua::estatuses())],
            'responsable_id' => ['nullable', 'exists:usuarios,id'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);
    }

    private function validatedSesion(Request $request): array
    {
        return $request->validate([
            'docente_id' => ['nullable', 'exists:docentes,id'],
            'expositor_nombre' => ['nullable', 'string', 'max:180'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'aula_liga' => ['nullable', 'string', 'max:180'],
            'modalidad' => ['required', Rule::in(CursoEducacionContinua::modalidades())],
            'estatus' => ['required', Rule::in(CursoSesion::estatuses())],
            'observaciones' => ['nullable', 'string'],
        ]);
    }

    private function calcularDuracion(string $inicio, string $fin): float
    {
        $start = \Carbon\Carbon::createFromFormat('H:i', $inicio);
        $end = \Carbon\Carbon::createFromFormat('H:i', $fin);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        return round($start->diffInMinutes($end) / 60, 2);
    }


    private function diasSemanaPlaneador(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
    }

    private function horariosPredefinidosPlaneador(): array
    {
        return [
            ['label' => '05:00 pm - 09:00 pm', 'inicio' => '17:00', 'fin' => '21:00'],
            ['label' => '08:00 am - 01:00 pm', 'inicio' => '08:00', 'fin' => '13:00'],
            ['label' => '09:00 am - 01:00 pm', 'inicio' => '09:00', 'fin' => '13:00'],
            ['label' => '05:00 pm - 08:00 pm', 'inicio' => '17:00', 'fin' => '20:00'],
            ['label' => '09:00 am - 11:00 am', 'inicio' => '09:00', 'fin' => '11:00'],
        ];
    }

    private function plantillasPlaneador(): array
    {
        return [
            'masc_viernes_sabado' => [
                'nombre' => 'MASC viernes-sábado',
                'dias' => [5, 6],
                'horarios' => [
                    5 => ['inicio' => '17:00', 'fin' => '21:00'],
                    6 => ['inicio' => '09:00', 'fin' => '13:00'],
                ],
            ],
            'masterclass_martes_jueves' => [
                'nombre' => 'MasterClass martes-jueves',
                'dias' => [2, 4],
                'horarios' => [
                    2 => ['inicio' => '17:00', 'fin' => '21:00'],
                    4 => ['inicio' => '17:00', 'fin' => '21:00'],
                ],
            ],
            'sabatino_9_13' => [
                'nombre' => 'Sabatino 09:00-13:00',
                'dias' => [6],
                'horarios' => [
                    6 => ['inicio' => '09:00', 'fin' => '13:00'],
                ],
            ],
        ];
    }

    private function crearSesionesDesdePlaneador(Request $request, CursoEducacionContinua $curso): array
    {
        $data = $request->validate([
            'planeador_dias' => ['required', 'array', 'min:1'],
            'planeador_dias.*' => ['integer', 'between:1,7'],
            'planeador_horarios' => ['required', 'array'],
            'planeador_horarios.*.hora_inicio' => ['nullable', 'date_format:H:i'],
            'planeador_horarios.*.hora_fin' => ['nullable', 'date_format:H:i'],
            'planeador_docente_id' => ['nullable', 'exists:docentes,id'],
            'planeador_expositor_nombre' => ['nullable', 'string', 'max:180'],
            'planeador_aula_liga' => ['nullable', 'string', 'max:180'],
            'planeador_modalidad' => ['required', Rule::in(CursoEducacionContinua::modalidades())],
            'planeador_estatus' => ['required', Rule::in(CursoSesion::estatuses())],
            'planeador_equipo_requerido' => ['array'],
            'planeador_equipo_requerido.*' => ['string', 'max:80'],
            'planeador_observaciones' => ['nullable', 'string'],
            'planeador_limitar_a_fecha_fin' => ['nullable', 'boolean'],
        ]);

        if (!$curso->fecha_inicio) {
            abort(422, 'Debes capturar la fecha de inicio del curso para generar sesiones automáticas.');
        }

        $horariosPorDia = [];
        foreach ($data['planeador_dias'] as $dia) {
            $inicio = $data['planeador_horarios'][$dia]['hora_inicio'] ?? null;
            $fin = $data['planeador_horarios'][$dia]['hora_fin'] ?? null;

            if (!$inicio || !$fin) {
                abort(422, 'Todos los días seleccionados deben tener hora de inicio y hora fin.');
            }

            $duracion = $this->calcularDuracion($inicio, $fin);
            if ($duracion <= 0) {
                abort(422, 'La hora fin debe ser mayor que la hora inicio en todos los días seleccionados.');
            }

            $horariosPorDia[(int) $dia] = compact('inicio', 'fin', 'duracion');
        }

        $fechaInicio = $curso->fecha_inicio->copy()->startOfDay();
        $fechaFin = $request->boolean('planeador_limitar_a_fecha_fin') && $curso->fecha_fin
            ? $curso->fecha_fin->copy()->startOfDay()
            : $fechaInicio->copy()->addYears(2);

        if ($fechaFin->lt($fechaInicio)) {
            abort(422, 'La fecha fin no puede ser anterior a la fecha de inicio.');
        }

        $horasObjetivo = (float) $curso->horas_totales;
        $horasAcumuladas = 0;
        $creadas = 0;
        $omitidas = 0;
        $limiteSeguridad = 500;

        foreach (CarbonPeriod::create($fechaInicio, $fechaFin) as $fecha) {
            if ($horasObjetivo > 0 && $horasAcumuladas >= $horasObjetivo) {
                break;
            }

            $diaIso = $fecha->dayOfWeekIso;
            if (!isset($horariosPorDia[$diaIso])) {
                continue;
            }

            $horario = $horariosPorDia[$diaIso];

            $existe = $curso->sesiones()
                ->whereDate('fecha', $fecha->toDateString())
                ->where('hora_inicio', $horario['inicio'])
                ->where('hora_fin', $horario['fin'])
                ->exists();

            if ($existe) {
                $omitidas++;
                continue;
            }

            $curso->sesiones()->create([
                'docente_id' => $data['planeador_docente_id'] ?? null,
                'expositor_nombre' => $data['planeador_expositor_nombre'] ?? null,
                'fecha' => $fecha->toDateString(),
                'hora_inicio' => $horario['inicio'],
                'hora_fin' => $horario['fin'],
                'duracion_horas' => $horario['duracion'],
                'aula_liga' => $data['planeador_aula_liga'] ?? null,
                'modalidad' => $data['planeador_modalidad'],
                'estatus' => $data['planeador_estatus'],
                'requiere_equipo' => !empty($data['planeador_equipo_requerido'] ?? []),
                'equipo_requerido' => $data['planeador_equipo_requerido'] ?? [],
                'observaciones' => $data['planeador_observaciones'] ?? null,
            ]);

            $creadas++;
            $horasAcumuladas += $horario['duracion'];

            if ($creadas >= $limiteSeguridad) {
                abort(422, 'El planeador intentó generar demasiadas sesiones. Revisa fechas, horas totales o días seleccionados.');
            }
        }

        if ($creadas === 0 && $omitidas === 0) {
            abort(422, 'No se generó ninguna sesión. Revisa días seleccionados, rango de fechas y horas totales.');
        }

        $this->bitacora(
            'Generar sesiones de educación continua',
            'Se generaron '.$creadas.' sesiones automáticas para '.$curso->nombre,
            'Educación Continua',
            $curso
        );

        return [
            'creadas' => $creadas,
            'omitidas' => $omitidas,
            'horas' => $horasAcumuladas,
        ];
    }

    private function validarSesionPerteneceCurso(CursoEducacionContinua $curso, CursoSesion $sesion): void
    {
        abort_unless((int) $sesion->curso_id === (int) $curso->id, 404);
    }

    private function validarInscritoPerteneceCurso(CursoEducacionContinua $curso, CursoInscrito $inscrito): void
    {
        abort_unless((int) $inscrito->curso_id === (int) $curso->id, 404);
    }

    private function validarInscritoDuplicado(CursoEducacionContinua $curso, array $data): void
    {
        $query = $curso->inscritos();

        if (!empty($data['alumno_id'])) {
            $query->where('alumno_id', $data['alumno_id']);
        } elseif (!empty($data['prospecto_id'])) {
            $query->where('prospecto_id', $data['prospecto_id']);
        } else {
            $query->where('nombre_externo', $data['nombre_externo']);
        }

        if ($query->exists()) {
            abort(422, 'El participante ya está inscrito en este curso.');
        }
    }
}
