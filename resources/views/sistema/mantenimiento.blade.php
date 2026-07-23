@extends('layouts.app')

@section('title', 'Mantenimiento del Sistema')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Mantenimiento del sistema</h1>
            <p class="text-sm text-slate-500 mt-1">
                Panel para revisar el estado del sistema, respaldar información y ejecutar tareas de soporte. Úsalo solo con autorización de Sistemas.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sistema.mantenimiento.backup-db') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class='bx bx-data'></i>
                Respaldo de base de datos
            </a>
            <a href="{{ route('sistema.mantenimiento.backup-archivos') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class='bx bx-archive'></i>
                Respaldo de archivos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($checks as $check)
            <div class="rounded-2xl border {{ $check['estado'] ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }} p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center {{ $check['estado'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        <i class='bx {{ $check['estado'] ? 'bx-check' : 'bx-error' }} text-2xl'></i>
                    </div>
                    <div>
                        <h2 class="font-semibold {{ $check['estado'] ? 'text-green-900' : 'text-amber-900' }}">{{ $check['titulo'] }}</h2>
                        <p class="text-sm {{ $check['estado'] ? 'text-green-700' : 'text-amber-700' }} mt-1">{{ $check['detalle'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Estado del sistema</h2>
                <p class="text-xs text-slate-500">Indicadores de soporte para revisar instalación, archivos, base de datos y operación general.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($diagnostico as $bloque => $datos)
                    <div class="p-5">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500 mb-3">{{ str_replace('_', ' ', $bloque) }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($datos as $etiqueta => $valor)
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $etiqueta }}</p>
                                    @if($etiqueta === 'Detalle')
                                        <pre class="mt-1 text-xs text-slate-700 whitespace-pre-wrap max-h-48 overflow-y-auto">{{ $valor }}</pre>
                                    @else
                                        <p class="mt-1 text-sm font-semibold text-slate-800 break-words">{{ $valor }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                    <h2 class="font-semibold text-slate-800">Acciones de soporte</h2>
                    <p class="text-xs text-slate-500">Usar solo cuando sea necesario. Todas quedan registradas en bitácora.</p>
                </div>

                <div class="p-5 space-y-3">
                    <form method="POST" action="{{ route('sistema.mantenimiento.limpiar-cache') }}">
                        @csrf
                        <button class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">
                            <i class='bx bx-brush'></i>
                            Actualizar configuración del sistema
                        </button>
                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">Útil después de cambiar configuración, rutas o vistas. No elimina información de alumnos ni pagos.</p>
                    </form>

                    <form method="POST" action="{{ route('sistema.mantenimiento.storage-link') }}">
                        @csrf
                        <button class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            <i class='bx bx-link'></i>
                            Reparar acceso a archivos públicos
                        </button>
                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">Usar si logos o archivos públicos no se muestran después de instalar o mover el sistema. No expone documentos privados.</p>
                    </form>

                    <form method="POST" action="{{ route('sistema.mantenimiento.limpiar-logs') }}" onsubmit="return confirm('¿Seguro que deseas vaciar el registro técnico principal? La bitácora institucional no se borra.');">
                        @csrf
                        <button class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                            <i class='bx bx-trash'></i>
                            Vaciar registro técnico principal
                        </button>
                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">Reduce el tamaño del archivo de eventos técnicos. La bitácora institucional no se borra.</p>
                    </form>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                    <h2 class="font-semibold text-slate-800">Respaldos manuales</h2>
                    <p class="text-xs text-slate-500">Descargas inmediatas para resguardo local.</p>
                </div>

                <div class="p-5 space-y-3 text-sm text-slate-600">
                    <a href="{{ route('sistema.mantenimiento.backup-db') }}" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 font-semibold text-white hover:bg-green-700">
                        <i class='bx bx-download'></i>
                        Descargar respaldo de base de datos
                    </a>

                    <a href="{{ route('sistema.mantenimiento.backup-archivos') }}" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 font-semibold text-white hover:bg-purple-700">
                        <i class='bx bx-folder-open'></i>
                        Descargar respaldo de documentos y archivos
                    </a>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        El respaldo de archivos incluye documentos privados, comprobantes, logos institucionales y demás archivos cargados al sistema. Debe resguardarse como información sensible.
                    </p>
                </div>
            </div>

            <div class="bg-slate-900 text-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-white/10">
                    <h2 class="font-semibold">Checklist antes de producción</h2>
                </div>
                <div class="p-5 text-sm space-y-2 text-slate-300">
                    <p>• El modo de errores detallados debe estar desactivado.</p>
                    <p>• Cambiar contraseñas de usuarios semilla.</p>
                    <p>• Confirmar la dirección oficial del sistema.</p>
                    <p>• Validar respaldos de BD y archivos.</p>
                    <p>• Confirmar permisos de carpetas de archivos.</p>
                    <p>• Revisar correos institucionales si se usarán recordatorios.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
