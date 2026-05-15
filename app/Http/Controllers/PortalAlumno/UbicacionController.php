<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionInstitucional;
use Illuminate\View\View;

/**
 * Ubicación institucional para alumnos.
 *
 * Este controlador pertenece exclusivamente al Portal Alumno.
 *
 * IMPORTANTE:
 * - Solo consulta configuración institucional.
 * - No modifica información administrativa.
 * - No depende del panel interno.
 */
class UbicacionController extends Controller
{
    public function index(): View
    {
        $configuracion = ConfiguracionInstitucional::actual();

        return view('portal_alumno.ubicacion.index', compact('configuracion'));
    }
}
