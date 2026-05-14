@extends('portal_alumno.layouts.app')

@section('title', 'Ubicacion')
@section('mobile_title', 'Ubicacion')

@section('content')
@php
    $nombre = $configuracion->nombre_institucion ?? 'Instituto de Altos Estudios Juridicos de Jalisco';
    $domicilio = collect([
        $configuracion->domicilio ?? null,
        $configuracion->colonia ?? null,
        $configuracion->municipio ?? null,
        $configuracion->estado ?? 'Jalisco',
        $configuracion->codigo_postal ?? null,
    ])->filter()->implode(', ');

    $direccionBusqueda = $domicilio ?: $nombre . ', Jalisco, Mexico';
    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($direccionBusqueda);
@endphp

<div class="space-y-6">
    <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Ubicacion del plantel</h2>
        <p class="text-slate-500 mt-1">Datos de contacto y acceso rapido a Google Maps.</p>
    </div>

    <div class="grid lg:grid-cols-[.8fr_1.2fr] gap-5">
        <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="h-14 w-14 rounded-2xl bg-[#0f2a5f] text-white flex items-center justify-center mb-5">
                <i class='bx bx-map text-3xl'></i>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-950">{{ $nombre }}</h3>
            <p class="text-slate-500 mt-3">{{ $domicilio ?: 'Domicilio pendiente de configurar en el sistema institucional.' }}</p>

            <div class="mt-6 space-y-3 text-sm">
                <p><strong>Telefono:</strong> {{ $configuracion->telefono_principal ?? 'Pendiente' }}</p>
                <p><strong>Correo:</strong> {{ $configuracion->correo_contacto ?? 'Pendiente' }}</p>
                <p><strong>Sitio web:</strong> {{ $configuracion->sitio_web ?? 'Pendiente' }}</p>
            </div>

            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-[#0f2a5f] px-5 py-4 text-sm font-extrabold text-white hover:bg-[#123879] transition">
                <i class='bx bx-navigation mr-2 text-xl'></i> Como llegar
            </a>
        </section>

        <section class="rounded-3xl bg-white overflow-hidden portal-card min-h-[420px]">
            <iframe
                title="Mapa IDEJ"
                class="w-full h-[420px] lg:h-full border-0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q={{ urlencode($direccionBusqueda) }}&output=embed">
            </iframe>
        </section>
    </div>
</div>
@endsection
