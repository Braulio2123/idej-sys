<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionInstitucional;
use Illuminate\View\View;

/**
 * Ubicacion institucional para alumnos.
 *
 * No modifica configuracion institucional; solo la consulta para mostrar datos
 * utiles dentro de la PWA.
 */
class UbicacionController extends Controller
{
    public function index(): View
    {
        $configuracion = ConfiguracionInstitucional::query()->first();

        return view('portal_alumno.ubicacion.index', compact('configuracion'));
    }
}
