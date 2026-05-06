@extends('layouts.app')

@section('title', 'Configuración Institucional')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Configuración institucional</h1>
            <p class="text-sm text-slate-500 mt-1">
                Administra los datos oficiales que aparecen en recibos, documentos, encabezados y parámetros operativos de IDEJ-SYS.
            </p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-600">
            <p class="font-semibold text-slate-800">Última actualización</p>
            <p>{{ optional($configuracion->updated_at)->format('d/m/Y H:i') ?? 'Sin cambios registrados' }}</p>
            <p class="text-xs text-slate-500">{{ $configuracion->actualizadoPor->nombre ?? 'Sistema' }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <p class="font-semibold mb-1">Revisa los campos marcados:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion.institucional.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Identidad institucional</h2>
                <p class="text-xs text-slate-500">Información oficial que identifica al instituto dentro del sistema.</p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre completo de la institución *</label>
                    <input type="text" name="nombre_institucion" value="{{ old('nombre_institucion', $configuracion->nombre_institucion) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre corto *</label>
                    <input type="text" name="nombre_corto" value="{{ old('nombre_corto', $configuracion->nombre_corto) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $configuracion->rfc) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Opcional">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Razón social</label>
                    <input type="text" name="razon_social" value="{{ old('razon_social', $configuracion->razon_social) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Opcional">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Lema o descripción institucional</label>
                    <input type="text" name="lema" value="{{ old('lema', $configuracion->lema) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-[180px,1fr] gap-5 items-start">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                        <img src="{{ $configuracion->logoUrl() }}" class="max-h-24 mx-auto object-contain" alt="Logo institucional">
                        <p class="text-xs text-slate-500 mt-2">Logo actual</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Actualizar logo</label>
                        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-500 mt-2">Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.</p>

                        @if($configuracion->logo_path)
                            <label class="inline-flex items-center gap-2 mt-3 text-sm text-red-700">
                                <input type="checkbox" name="eliminar_logo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                Eliminar logo personalizado y usar logo base del sistema
                            </label>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Contacto y domicilio</h2>
                <p class="text-xs text-slate-500">Estos datos pueden aparecer en recibos y documentos institucionales.</p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Domicilio</label>
                    <input type="text" name="domicilio" value="{{ old('domicilio', $configuracion->domicilio) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Colonia</label>
                    <input type="text" name="colonia" value="{{ old('colonia', $configuracion->colonia) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Municipio</label>
                    <input type="text" name="municipio" value="{{ old('municipio', $configuracion->municipio) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                    <input type="text" name="estado" value="{{ old('estado', $configuracion->estado) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Código postal</label>
                    <input type="text" name="codigo_postal" value="{{ old('codigo_postal', $configuracion->codigo_postal) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono principal</label>
                    <input type="text" name="telefono_principal" value="{{ old('telefono_principal', $configuracion->telefono_principal) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono secundario</label>
                    <input type="text" name="telefono_secundario" value="{{ old('telefono_secundario', $configuracion->telefono_secundario) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Correo de contacto</label>
                    <input type="email" name="correo_contacto" value="{{ old('correo_contacto', $configuracion->correo_contacto) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Sitio web</label>
                    <input type="text" name="sitio_web" value="{{ old('sitio_web', $configuracion->sitio_web) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="https://...">
                </div>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Recibos y parámetros financieros</h2>
                <p class="text-xs text-slate-500">Configura textos y reglas generales usadas por pagos, recibos y moratorios.</p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prefijo de folio *</label>
                    <input type="text" name="recibo_prefijo" value="{{ old('recibo_prefijo', $configuracion->recibo_prefijo) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Moneda *</label>
                    <select name="moneda" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        @foreach(['MXN' => 'MXN - Peso mexicano', 'USD' => 'USD - Dólar'] as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('moneda', $configuracion->moneda) === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Zona horaria *</label>
                    <select name="zona_horaria" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="America/Mexico_City" @selected(old('zona_horaria', $configuracion->zona_horaria) === 'America/Mexico_City')>America/Mexico_City</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Moratorio % *</label>
                    <input type="number" step="0.01" min="0" max="100" name="moratorio_porcentaje" value="{{ old('moratorio_porcentaje', $configuracion->moratorio_porcentaje) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Días de gracia *</label>
                    <input type="number" min="0" max="365" name="moratorio_dias_gracia" value="{{ old('moratorio_dias_gracia', $configuracion->moratorio_dias_gracia) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="recordatorios_pago_activos" value="1" @checked(old('recordatorios_pago_activos', $configuracion->recordatorios_pago_activos)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label class="text-sm font-semibold text-slate-700">Recordatorios de pago activos</label>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Leyenda del recibo</label>
                    <textarea name="recibo_leyenda" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">{{ old('recibo_leyenda', $configuracion->recibo_leyenda) }}</textarea>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nota fiscal / administrativa</label>
                    <textarea name="recibo_nota_fiscal" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">{{ old('recibo_nota_fiscal', $configuracion->recibo_nota_fiscal) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Firma izquierda *</label>
                    <input type="text" name="recibo_firma_recibio" value="{{ old('recibo_firma_recibio', $configuracion->recibo_firma_recibio) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Firma derecha *</label>
                    <input type="text" name="recibo_firma_conformidad" value="{{ old('recibo_firma_conformidad', $configuracion->recibo_firma_conformidad) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="recibo_mostrar_logo" value="1" @checked(old('recibo_mostrar_logo', $configuracion->recibo_mostrar_logo)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label class="text-sm font-semibold text-slate-700">Mostrar logo en recibos</label>
                </div>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Colores institucionales</h2>
                <p class="text-xs text-slate-500">Por ahora se guardan para documentos y futuras personalizaciones visuales.</p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Color primario *</label>
                    <input type="color" name="color_primario" value="{{ old('color_primario', $configuracion->color_primario) }}" class="h-11 w-full rounded-xl border border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Color secundario *</label>
                    <input type="color" name="color_secundario" value="{{ old('color_secundario', $configuracion->color_secundario) }}" class="h-11 w-full rounded-xl border border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Color acento *</label>
                    <input type="color" name="color_acento" value="{{ old('color_acento', $configuracion->color_acento) }}" class="h-11 w-full rounded-xl border border-slate-300">
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-700 text-white font-semibold hover:bg-blue-800 shadow-sm">
                Guardar configuración
            </button>
        </div>
    </form>
</div>
@endsection
