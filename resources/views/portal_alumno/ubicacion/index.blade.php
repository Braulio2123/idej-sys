@extends('portal_alumno.layouts.app')

@section('title', 'Ubicación')
@section('mobile_title', 'Ubicación')

@section('content')
@php
    $nombre = $configuracion->nombre_institucion ?: 'Instituto de Altos Estudios Jurídicos de Jalisco';
    $nombreCorto = $configuracion->nombre_corto ?: 'IDEJ';

    $domicilio = $configuracion->direccionCompleta();

    $direccionBusqueda = $domicilio
        ? $domicilio . ', México'
        : $nombre . ', Jalisco, México';

    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($direccionBusqueda);
    $mapsEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($direccionBusqueda) . '&output=embed';

    $telefonoPrincipal = $configuracion->telefono_principal;
    $telefonoSecundario = $configuracion->telefono_secundario;
    $correoContacto = $configuracion->correo_contacto;
    $sitioWeb = $configuracion->sitio_web;

    $sitioWebUrl = null;

    if ($sitioWeb) {
        $sitioWebUrl = str_starts_with($sitioWeb, 'http://') || str_starts_with($sitioWeb, 'https://')
            ? $sitioWeb
            : 'https://' . $sitioWeb;
    }

    $telefonoMarcacion = $telefonoPrincipal
        ? preg_replace('/[^0-9+]/', '', $telefonoPrincipal)
        : null;
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-[#0f2a5f] text-white p-5 md:p-7 portal-card">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-amber-300/20"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <p class="text-sm font-bold text-amber-200">
                    {{ $nombreCorto }}
                </p>

                <h2 class="text-3xl md:text-4xl font-extrabold mt-1">
                    Ubicación del plantel
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Consulta la dirección institucional, datos de contacto y acceso al mapa del plantel.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[230px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Institución</p>

                <p class="text-xl font-extrabold mt-1 leading-tight">
                    {{ $nombreCorto }}
                </p>

                <p class="text-xs text-blue-100 mt-2 leading-relaxed">
                    {{ $nombre }}
                </p>
            </div>
        </div>
    </section>

    <section class="grid lg:grid-cols-[.9fr_1.1fr] gap-5">
        <div class="space-y-5">
            <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-950">Dirección</h3>
                        <p class="text-sm text-slate-500">
                            Información registrada por la institución.
                        </p>
                    </div>

                    <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                        <i class='bx bx-map text-2xl'></i>
                    </div>
                </div>

                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-xs font-bold uppercase text-slate-400">Plantel</p>

                    <h4 class="font-extrabold text-slate-950 mt-1 leading-tight">
                        {{ $nombre }}
                    </h4>

                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                        {{ $domicilio ?: 'La dirección institucional está pendiente de configurar.' }}
                    </p>
                </div>

                <a href="{{ $mapsUrl }}"
                   target="_blank"
                   rel="noopener"
                   class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-[#0f2a5f] px-5 py-4 text-sm font-extrabold text-white hover:bg-[#123879] transition">
                    <i class='bx bx-navigation mr-2 text-xl'></i>
                    Cómo llegar
                </a>
            </section>

            <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-950">Contacto</h3>
                        <p class="text-sm text-slate-500">
                            Medios institucionales disponibles.
                        </p>
                    </div>

                    <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class='bx bx-phone text-2xl'></i>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-400">Teléfono principal</p>

                        @if($telefonoPrincipal && $telefonoMarcacion)
                            <a href="tel:{{ $telefonoMarcacion }}"
                               class="inline-flex items-center font-extrabold text-slate-950 mt-1">
                                {{ $telefonoPrincipal }}
                            </a>
                        @else
                            <p class="font-extrabold text-slate-950 mt-1">
                                Pendiente
                            </p>
                        @endif
                    </div>

                    @if($telefonoSecundario)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Teléfono secundario</p>
                            <p class="font-extrabold text-slate-950 mt-1">
                                {{ $telefonoSecundario }}
                            </p>
                        </div>
                    @endif

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-400">Correo</p>

                        @if($correoContacto)
                            <a href="mailto:{{ $correoContacto }}"
                               class="inline-flex items-center font-extrabold text-slate-950 mt-1 break-all">
                                {{ $correoContacto }}
                            </a>
                        @else
                            <p class="font-extrabold text-slate-950 mt-1">
                                Pendiente
                            </p>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-400">Sitio web</p>

                        @if($sitioWeb && $sitioWebUrl)
                            <a href="{{ $sitioWebUrl }}"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center font-extrabold text-[#0f2a5f] mt-1 break-all">
                                {{ $sitioWeb }}
                            </a>
                        @else
                            <p class="font-extrabold text-slate-950 mt-1">
                                Pendiente
                            </p>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-3xl bg-white overflow-hidden portal-card min-h-[420px]">
            <div class="p-5 md:p-6 border-b border-slate-100">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-950">Mapa</h3>
                        <p class="text-sm text-slate-500">
                            Referencia visual de ubicación.
                        </p>
                    </div>

                    <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                        <i class='bx bx-current-location text-2xl'></i>
                    </div>
                </div>
            </div>

            <iframe
                title="Mapa IDEJ"
                class="w-full h-[420px] lg:h-[calc(100%-97px)] border-0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="{{ $mapsEmbedUrl }}">
            </iframe>
        </section>
    </section>

    <section class="rounded-3xl bg-white p-5 portal-card">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class='bx bx-info-circle text-xl'></i>
            </div>

            <div>
                <h3 class="font-extrabold text-slate-950">Información de ubicación</h3>
                <p class="text-sm text-slate-500 mt-1">
                    La dirección y los datos de contacto se toman de la configuración institucional del IDEJ. Si algún dato no aparece o es incorrecto, debe actualizarse desde la configuración interna del sistema.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
