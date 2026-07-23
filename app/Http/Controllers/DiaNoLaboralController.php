<?php

namespace App\Http\Controllers;

use App\Console\Commands\CargarDiasNoLaboralesOficiales;
use App\Models\CalendarioSesion;
use App\Models\CursoSesion;
use App\Models\DiaNoLaboral;
use App\Models\NotificacionInterna;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiaNoLaboralController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);
        $dias = DiaNoLaboral::whereYear('fecha', $anio)->orderByDesc('fecha')->paginate(20)->withQueryString();

        return view('dias_no_laborales.index', [
            'dias' => $dias,
            'tipos' => [DiaNoLaboral::TIPO_LEY, DiaNoLaboral::TIPO_INSTITUCIONAL, DiaNoLaboral::TIPO_INTERNO],
            'anio' => $anio,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date|unique:dias_no_laborales,fecha',
            'nombre' => 'required|string|max:180',
            'tipo' => 'required|in:Ley,Institucional,Interno',
            'activo' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $dia = DB::transaction(function () use ($validated) {
            $dia = DiaNoLaboral::create($validated);
            $this->notificarSesionesAfectadas($dia);
            return $dia;
        });

        $this->bitacora('Crear día no laboral', "Se registró {$dia->nombre} para {$dia->fecha->format('d/m/Y')}.", 'Área Académica', $dia);

        return back()->with('success', 'Día no laboral registrado correctamente. Si había sesiones programadas en esa fecha, se notificó a las áreas involucradas.');
    }

    public function cargarOficiales(Request $request)
    {
        $validated = $request->validate([
            'anio' => 'required|integer|min:2020|max:2100',
        ]);

        foreach (CargarDiasNoLaboralesOficiales::diasOficiales((int) $validated['anio']) as $dia) {
            DiaNoLaboral::updateOrCreate(
                ['fecha' => $dia['fecha']],
                [
                    'nombre' => $dia['nombre'],
                    'tipo' => DiaNoLaboral::TIPO_LEY,
                    'activo' => true,
                    'observaciones' => 'Carga anual oficial base. Revisar cada año contra el calendario institucional aplicable.',
                ]
            );
        }

        $this->bitacora('Cargar días no laborales oficiales', 'Se cargaron días no laborales oficiales para '.$validated['anio'].'.', 'Área Académica');

        return back()->with('success', 'Días no laborales oficiales cargados para '.$validated['anio'].'.');
    }

    public function update(Request $request, DiaNoLaboral $diaNoLaboral)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:180',
            'tipo' => 'required|in:Ley,Institucional,Interno',
            'activo' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $validated['activo'] = $request->boolean('activo');
        $diaNoLaboral->update($validated);

        $this->bitacora('Actualizar día no laboral', "Se actualizó el día no laboral {$diaNoLaboral->fecha->format('d/m/Y')}.", 'Área Académica', $diaNoLaboral);

        return back()->with('success', 'Día no laboral actualizado correctamente.');
    }

    public function destroy(DiaNoLaboral $diaNoLaboral)
    {
        $descripcion = "Se eliminó el día no laboral {$diaNoLaboral->nombre} ({$diaNoLaboral->fecha->format('d/m/Y')}).";
        $diaNoLaboral->delete();

        $this->bitacora('Eliminar día no laboral', $descripcion, 'Área Académica');

        return back()->with('success', 'Día no laboral eliminado correctamente.');
    }

    private function notificarSesionesAfectadas(DiaNoLaboral $dia): void
    {
        $fecha = $dia->fecha->toDateString();
        $academicas = CalendarioSesion::whereDate('fecha', $fecha)->whereNotIn('estatus', ['Cancelada', 'Suspendida'])->count();
        $continua = CursoSesion::whereDate('fecha', $fecha)->where('estatus', '!=', 'Cancelada')->count();
        $total = $academicas + $continua;

        if ($total === 0) {
            return;
        }

        foreach (['Academica', 'Sistemas', 'Recepcion', 'Direccion', 'CAdmin'] as $rol) {
            NotificacionInterna::sincronizar([
                'rol_clave' => $rol,
                'tipo' => 'dia_no_laboral_sesiones_afectadas',
                'modulo' => 'Días no laborales',
                'titulo' => 'Sesiones afectadas por día no laboral',
                'mensaje' => "El día {$dia->fecha->format('d/m/Y')} se registró como no laboral y tiene {$total} sesión(es) programada(s). Revisa si se mantienen, cancelan o reprograman.",
                'url' => route('agenda-operativa.index', ['fecha_inicio' => $fecha, 'fecha_fin' => $fecha, 'rango' => 'personalizado'], false),
                'severidad' => NotificacionInterna::SEVERIDAD_ALTA,
                'referencia_tipo' => DiaNoLaboral::class,
                'referencia_id' => $dia->id,
                'metadata' => ['fecha' => $fecha, 'sesiones_academicas' => $academicas, 'sesiones_educacion_continua' => $continua],
            ]);
        }
    }
}
