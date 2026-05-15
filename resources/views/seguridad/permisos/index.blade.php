@extends('layouts.app')

@section('title', 'Matriz de Permisos')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Matriz de permisos internos</h1>
            <p class="text-sm text-slate-500 mt-1">
                Consulta operativa de roles y permisos del sistema administrativo IDEJ-SYS. No incluye Portal Alumno.
            </p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 max-w-xl">
            <strong>Nota:</strong> esta pantalla es de control y auditoría. Los permisos se administran desde
            <code class="text-xs bg-white/70 px-1 py-0.5 rounded">config/idej_permisos.php</code>
            para evitar cambios accidentales desde producción.
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($roles as $rol)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-slate-800">{{ $rol }}</h2>
                    @if(in_array($rol, $rolesCriticos, true))
                        <span class="rounded-full bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 text-[11px] font-semibold">Crítico</span>
                    @else
                        <span class="rounded-full bg-slate-50 text-slate-600 border border-slate-200 px-2 py-0.5 text-[11px] font-semibold">Operativo</span>
                    @endif
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $resumenPorRol[$rol] ?? 0 }}</p>
                <p class="text-xs text-slate-500">permisos documentados</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
        <table class="min-w-full text-sm bg-white">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                <tr>
                    <th class="py-3 px-4 text-left min-w-[260px]">Permiso</th>
                    <th class="py-3 px-4 text-left min-w-[180px]">Clave</th>
                    <th class="py-3 px-4 text-center">Sensible</th>
                    @foreach($roles as $rol)
                        <th class="py-3 px-3 text-center whitespace-nowrap">{{ $rol }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($permisosPorModulo as $modulo => $permisos)
                    <tr class="bg-blue-50/60">
                        <td colspan="{{ 3 + count($roles) }}" class="py-2 px-4 text-xs font-bold uppercase tracking-wide text-blue-900">
                            {{ $modulo }}
                        </td>
                    </tr>

                    @foreach($permisos as $permiso)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $permiso['nombre'] }}</td>
                            <td class="py-3 px-4">
                                <code class="text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded-lg">{{ $permiso['clave'] }}</code>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($permiso['sensible'])
                                    <span class="inline-flex items-center rounded-full bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 text-[11px] font-semibold">Sí</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-50 text-slate-500 border border-slate-200 px-2 py-0.5 text-[11px]">No</span>
                                @endif
                            </td>

                            @foreach($roles as $rol)
                                @php
                                    $permitido = $rol === \App\Models\Rol::ADMIN || in_array($rol, $permiso['roles'], true);
                                @endphp
                                <td class="py-3 px-3 text-center">
                                    @if($permitido)
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-green-50 text-green-700 border border-green-200 font-bold">✓</span>
                                    @else
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-50 text-slate-300 border border-slate-200">—</span>
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
@endsection
