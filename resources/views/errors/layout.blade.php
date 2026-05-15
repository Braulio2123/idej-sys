<!DOCTYPE html>
<html lang="es" class="bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') · IDEJ-SYS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-blue-50 flex items-center justify-center p-6">
    @php
        $logoError = asset('images/logo.png');
        $destino = auth()->check() ? route('dashboard') : route('login');
        $textoBoton = auth()->check() ? 'Volver al dashboard' : 'Iniciar sesión';
    @endphp

    <main class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white shadow-2xl p-8 md:p-10 text-center">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-50 border border-slate-200">
            <img src="{{ $logoError }}" alt="IDEJ" class="max-h-14 max-w-14 object-contain">
        </div>

        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-blue-700">IDEJ-SYS</p>
        <h1 class="mt-4 text-6xl font-black text-slate-900">@yield('code')</h1>
        <h2 class="mt-3 text-2xl font-bold text-slate-800">@yield('title')</h2>
        <p class="mt-4 text-slate-600 leading-relaxed">@yield('message')</p>

        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ $destino }}" class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-blue-800">
                {{ $textoBoton }}
            </a>
            <button type="button" onclick="history.back()" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Regresar
            </button>
        </div>

        @hasSection('hint')
            <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-left text-sm text-amber-900">
                @yield('hint')
            </div>
        @endif
    </main>
</body>
</html>
