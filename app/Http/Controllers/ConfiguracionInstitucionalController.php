<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionInstitucional;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Validation\Rule;

class ConfiguracionInstitucionalController extends Controller
{
    use RegistraBitacora;

    public function edit()
    {
        $configuracion = ConfiguracionInstitucional::actual();

        return view('configuracion.institucional', compact('configuracion'));
    }

    public function update(Request $request)
    {
        $configuracion = ConfiguracionInstitucional::actual();

        $validated = $request->validate([
            'nombre_institucion' => ['required', 'string', 'max:180'],
            'nombre_corto' => ['required', 'string', 'max:60'],
            'razon_social' => ['nullable', 'string', 'max:180'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'lema' => ['nullable', 'string', 'max:180'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
            'eliminar_logo' => ['nullable', 'boolean'],

            'domicilio' => ['nullable', 'string', 'max:220'],
            'colonia' => ['nullable', 'string', 'max:120'],
            'municipio' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:120'],
            'codigo_postal' => ['nullable', 'string', 'max:12'],
            'telefono_principal' => ['nullable', 'string', 'max:40'],
            'telefono_secundario' => ['nullable', 'string', 'max:40'],
            'correo_contacto' => ['nullable', 'email', 'max:150'],
            'sitio_web' => ['nullable', 'url:http,https', 'max:180'],

            'color_primario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_secundario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_acento' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'recibo_prefijo' => ['required', 'string', 'max:20'],
            'recibo_leyenda' => ['nullable', 'string', 'max:1000'],
            'recibo_nota_fiscal' => ['nullable', 'string', 'max:1000'],
            'recibo_firma_recibio' => ['required', 'string', 'max:80'],
            'recibo_firma_conformidad' => ['required', 'string', 'max:80'],
            'recibo_mostrar_logo' => ['nullable', 'boolean'],

            'moneda' => ['required', Rule::in(['MXN', 'USD'])],
            'zona_horaria' => ['required', Rule::in(['America/Mexico_City'])],
            'moratorio_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'moratorio_dias_gracia' => ['required', 'integer', 'min:0', 'max:365'],
            'recordatorios_pago_activos' => ['nullable', 'boolean'],
        ]);

        $validated['recibo_mostrar_logo'] = $request->boolean('recibo_mostrar_logo');
        $validated['recordatorios_pago_activos'] = $request->boolean('recordatorios_pago_activos');
        $validated['actualizado_por_id'] = Auth::id();

        unset($validated['logo'], $validated['eliminar_logo']);

        $logoAnterior = $configuracion->logo_path;
        $logoNuevo = null;

        try {
            if ($request->hasFile('logo')) {
                $logoNuevo = $request->file('logo')->store('configuracion/logo', 'public');
                $validated['logo_path'] = $logoNuevo;
            } elseif ($request->boolean('eliminar_logo')) {
                $validated['logo_path'] = null;
            }

            DB::transaction(function () use ($configuracion, $validated): void {
                $configuracion->fill($validated)->save();
            });
        } catch (Throwable $e) {
            if ($logoNuevo) {
                Storage::disk('public')->delete($logoNuevo);
            }

            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la configuración institucional. Contacta al área de Sistemas.');
        }

        if ($logoAnterior && $logoAnterior !== $configuracion->logo_path) {
            Storage::disk('public')->delete($logoAnterior);
        }

        $this->bitacora(
            'Actualizar Configuración Institucional',
            'Se actualizaron los datos institucionales, parámetros de recibos y reglas operativas del sistema.',
            'Configuración',
            $configuracion
        );

        return redirect()
            ->route('configuracion.institucional.edit')
            ->with('success', 'Configuración institucional actualizada correctamente.');
    }

}
