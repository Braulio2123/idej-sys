<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ configInstitucional('nombre_corto', 'IDEJ') }}-SYS</title>

    {{-- Fuentes + Estilos --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#1E3A8A] via-[#162860] to-[#0D133A]
             flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- LOGO SUPERIOR --}}
        <div class="flex flex-col items-center mb-6">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 shadow-lg">
                <img src="{{ logoInstitucionalUrl() }}" class="h-20 w-auto">
            </div>

            <h2 class="text-white font-semibold text-xl mt-4 tracking-wide">
                {{ configInstitucional('nombre_corto', 'IDEJ') }} · Sistema Integral
            </h2>
        </div>

        {{-- CARD DEL FORMULARIO --}}
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-200">

            {{-- Slot de Breeze --}}
            {{ $slot }}

        </div>

        {{-- PIE DE PÁGINA --}}
        <p class="text-center text-white/80 text-xs mt-6 tracking-wide">
            © {{ date('Y') }} {{ configInstitucional('nombre_corto', 'IDEJ') }} — Sistema Académico y Administrativo
        </p>
    </div>

</body>
</html>
