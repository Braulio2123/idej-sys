@extends('layouts.app')

@section('title', 'Grupos Académicos')

@section('content')
@php
    $puedeGestionarCatalogos = usuarioTienePermiso('catalogos_academicos.gestionar');
    $puedeVerDetalleAcademico = usuarioTienePermiso('academica.ver');
@endphp
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📘 Grupos Académicos</h1>
            <p class="text-sm text-gray-500 mt-1">Los grupos con historial se archivan; no se eliminan alumnos, calendarios ni sesiones.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('grupos.index') }}"
               class="px-4 py-2 rounded shadow {{ !$mostrarArchivados ? 'bg-slate-800 text-white' : 'bg-white border text-slate-700' }}">
                Activos
            </a>
            <a href="{{ route('grupos.index', ['archivados' => 1]) }}"
               class="px-4 py-2 rounded shadow {{ $mostrarArchivados ? 'bg-slate-800 text-white' : 'bg-white border text-slate-700' }}">
                Archivados
            </a>
            @if($puedeGestionarCatalogos && ! $mostrarArchivados)
                <a href="{{ route('grupos.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">+ Nuevo Grupo</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 mb-4 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-3 mb-4 rounded">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="bg-blue-100 text-blue-800 px-4 py-3 mb-4 rounded">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-700 px-4 py-3 mb-4 rounded border border-red-200">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($grupos->isEmpty())
        <p class="text-gray-600">No hay grupos {{ $mostrarArchivados ? 'archivados' : 'activos' }}.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-indigo-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Ciclo</th>
                        <th class="px-4 py-3">Educación Programática</th>
                        <th class="px-4 py-3">Semestre</th>
                        <th class="px-4 py-3">Cupo</th>
                        <th class="px-4 py-3">Estatus</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupos as $grupo)
                        <tr class="border-b hover:bg-gray-50 {{ !$grupo->activo ? 'bg-slate-50' : '' }}">
                            <td class="px-4 py-3 font-semibold">{{ $grupo->nombre }}</td>
                            <td class="px-4 py-3">{{ $grupo->cicloEscolar->nombre ?? 'Sin ciclo' }}</td>
                            <td class="px-4 py-3">{{ $grupo->programa->nombre ?? 'Sin programa' }}</td>
                            <td class="px-4 py-3">{{ $grupo->semestre_o_cuatrimestre }}</td>
                            <td class="px-4 py-3">{{ $grupo->cupo_maximo }}</td>
                            <td class="px-4 py-3">
                                @if($grupo->activo)
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Activo</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-slate-200 text-slate-700 text-xs font-semibold">Archivado</span>
                                    <p class="text-xs text-slate-500 mt-1">{{ $grupo->archivado_at?->format('d/m/Y H:i') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-top">
                                <div class="flex flex-wrap justify-center gap-2">
                                    @if($puedeVerDetalleAcademico)
                                        <a href="{{ route('grupos.show', $grupo) }}" class="text-indigo-600 hover:underline">Ver detalle</a>
                                    @endif
                                    @if($puedeGestionarCatalogos && $grupo->activo)
                                        <a href="{{ route('grupos.edit', $grupo) }}" class="text-blue-600 hover:underline">Editar</a>
                                    @endif
                                    @if(! $puedeVerDetalleAcademico && ! $puedeGestionarCatalogos)
                                        <span class="text-xs text-slate-500">Oferta disponible</span>
                                    @endif
                                </div>

                                @if($puedeGestionarCatalogos && $grupo->activo)
                                    <details class="mt-3 text-left">
                                        <summary class="cursor-pointer text-red-600 hover:underline text-xs font-semibold">Archivar grupo</summary>
                                        <form action="{{ route('grupos.destroy', $grupo) }}" method="POST" class="mt-2 space-y-2" data-confirm="¿Archivar este grupo? Se conservarán alumnos, calendarios y sesiones.">
                                            @csrf
                                            @method('DELETE')
                                            <textarea name="motivo_archivo" required minlength="10" maxlength="2000" rows="3"
                                                      class="w-full rounded border-slate-300 text-xs"
                                                      placeholder="Explica el motivo institucional del archivo"></textarea>
                                            <button class="w-full bg-red-600 text-white px-3 py-2 rounded text-xs hover:bg-red-700">Confirmar archivo</button>
                                        </form>
                                    </details>
                                @elseif($grupo->motivo_archivo)
                                    <p class="mt-2 text-xs text-slate-600 text-left"><strong>Motivo:</strong> {{ $grupo->motivo_archivo }}</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $grupos->links() }}</div>
    @endif
</div>
@endsection
