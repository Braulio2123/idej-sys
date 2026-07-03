@extends('layouts.app')

@section('title', 'Permisos internos')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold text-slate-800">Permisos internos</h1>
            <p class="mt-1 max-w-3xl text-sm text-slate-500">
                Consulta qué áreas pueden acceder a cada función del sistema interno.
            </p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900 xl:max-w-xl">
            Esta pantalla es solo de consulta. Los cambios de acceso deben solicitarse al área de Sistemas para evitar modificaciones accidentales en producción.
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($roles as $rol)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="truncate text-sm font-semibold text-slate-800">{{ $rol }}</h2>
                    @if(in_array($rol, $rolesCriticos, true))
                        <span class="rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">Alta seguridad</span>
                    @else
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600">Operativo</span>
                    @endif
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $resumenPorRol[$rol] ?? 0 }}</p>
                <p class="text-xs text-slate-500">funciones permitidas</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-4">
            <h2 class="text-base font-semibold text-slate-800">Accesos por función</h2>
            <p class="mt-1 text-xs text-slate-500">Desliza la tabla horizontalmente si estás en una pantalla pequeña.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[980px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="min-w-[280px] px-4 py-3 text-left">Función</th>
                        <th class="min-w-[120px] px-4 py-3 text-center">Nivel</th>
                        @foreach($roles as $rol)
                            <th class="whitespace-nowrap px-3 py-3 text-center">{{ $rol }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($permisosPorModulo as $modulo => $permisos)
                        <tr class="bg-blue-50/60">
                            <td colspan="{{ 2 + count($roles) }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wide text-blue-900">
                                {{ $modulo }}
                            </td>
                        </tr>

                        @foreach($permisos as $permiso)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $permiso['nombre'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($permiso['sensible'])
                                        <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">Sensible</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-slate-500">Normal</span>
                                    @endif
                                </td>

                                @foreach($roles as $rol)
                                    @php
                                        $permitido = $rol === \App\Models\Rol::ADMIN || in_array($rol, $permiso['roles'], true);
                                    @endphp
                                    <td class="px-3 py-3 text-center">
                                        @if($permitido)
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-green-200 bg-green-50 font-bold text-green-700" title="Permitido">✓</span>
                                        @else
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-300" title="Sin acceso">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
