@extends('layouts.app')

@section('title', 'Registrar Pago Docente')

@section('content')
<div class="max-w-3xl mx-auto mt-6">
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-slate-200">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bx bx-money text-green-600 text-3xl"></i>
                Registrar pago docente
            </h1>
            <p class="text-sm text-slate-500">Solicitud {{ $solicitud->folio ?? '#'.$solicitud->id }} autorizada por {{ $solicitud->autorizadoPor->nombre ?? 'Administración' }}.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-5 p-4 bg-slate-50 border border-slate-200 rounded-xl grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <p><strong>Docente:</strong><br>{{ $solicitud->docente->nombre_completo }}</p>
            <p><strong>Monto:</strong><br><span class="text-xl text-green-700 font-bold">${{ number_format($solicitud->monto, 2) }}</span></p>
            <p><strong>Servicio:</strong><br>{{ $solicitud->materia_actividad }}</p>
            <p><strong>Programa / grupo:</strong><br>{{ $solicitud->programa_grupo ?? '—' }}</p>
        </div>

        <form method="POST" action="{{ route('solicitudes_pago.pagar', $solicitud) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Fecha de pago *</label>
                    <input type="date" name="fecha_pago" required value="{{ old('fecha_pago', date('Y-m-d')) }}" class="w-full p-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Método de pago *</label>
                    <select name="metodo_pago" required class="w-full p-2 border rounded-lg">
                        <option value="">Selecciona método</option>
                        @foreach($metodosPago as $metodo)
                            <option value="{{ $metodo }}" @selected(old('metodo_pago') === $metodo)>{{ $metodo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Referencia / folio bancario</label>
                    <input type="text" name="referencia_pago" value="{{ old('referencia_pago') }}" class="w-full p-2 border rounded-lg" placeholder="Clave de rastreo, transferencia, cheque, etc.">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Banco / cuenta</label>
                    <input type="text" name="banco_pago" value="{{ old('banco_pago') }}" class="w-full p-2 border rounded-lg" placeholder="Banco origen/destino o cuenta usada">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-1">Comprobante de pago</label>
                    <input type="file" name="comprobante_pago" accept=".pdf,.jpg,.jpeg,.png" class="w-full p-2 border rounded-lg bg-slate-50">
                    <p class="text-xs text-slate-500 mt-1">PDF, JPG o PNG. Máximo 5 MB.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-1">Observaciones administrativas</label>
                    <textarea name="observaciones_administracion" class="w-full p-2 border rounded-lg" rows="4">{{ old('observaciones_administracion', $solicitud->observaciones_administracion) }}</textarea>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('solicitudes_pago.show', $solicitud) }}" class="text-gray-700 hover:underline">← Cancelar</a>
                <button class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 shadow font-semibold">Registrar pago</button>
            </div>
        </form>
    </div>
</div>
@endsection
