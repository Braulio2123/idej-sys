<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f2a5f">
    <title>Acceso Alumno | IDEJ</title>
    <link rel="manifest" href="{{ asset('portal-alumno/manifest.json') }}">
    <link rel="icon" href="{{ asset('portal-alumno/icons/icon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .login-bg {
            background:
                radial-gradient(circle at 20% 20%, rgba(245,158,11,.22), transparent 24rem),
                radial-gradient(circle at 80% 10%, rgba(59,130,246,.22), transparent 26rem),
                linear-gradient(135deg, #0f2a5f 0%, #101827 100%);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    <main class="w-full max-w-6xl grid md:grid-cols-[1.1fr_.9fr] gap-6 items-center">
        <section class="hidden md:block text-white pr-8">
            <div class="inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/15 mb-8">
                <i class='bx bxs-graduation text-2xl text-amber-300'></i>
                <span class="font-bold">IDEJ · Portal Alumno PWA</span>
            </div>
            <h1 class="text-5xl font-extrabold leading-tight mb-5">Tu informacion academica en un solo lugar.</h1>
            <p class="text-blue-100 text-lg max-w-xl">Consulta horario, materias, avisos, calificaciones y datos del plantel desde celular o computadora.</p>
        </section>

        <section class="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl">
            <div class="text-center mb-7">
                <div class="mx-auto h-16 w-16 rounded-3xl bg-[#0f2a5f] text-white flex items-center justify-center mb-4">
                    <i class='bx bxs-graduation text-3xl'></i>
                </div>
                <p class="text-xs uppercase tracking-[.25em] font-bold text-[#0f2a5f]">IDEJ</p>
                <h2 class="text-2xl font-extrabold text-slate-900">Acceso de alumno</h2>
                <p class="text-sm text-slate-500 mt-1">Ingresa con tu matricula o correo registrado.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('portal.alumno.login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="login" class="block text-sm font-bold text-slate-700 mb-2">Matricula o correo</label>
                    <div class="relative">
                        <i class='bx bx-id-card absolute left-4 top-1/2 -translate-y-1/2 text-xl text-slate-400'></i>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm font-semibold outline-none transition focus:border-[#0f2a5f] focus:bg-white focus:ring-4 focus:ring-blue-100"
                               placeholder="Ej. A001 o alumno@correo.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Contrasena</label>
                    <div class="relative">
                        <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-xl text-slate-400'></i>
                        <input id="password" name="password" type="password" required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm font-semibold outline-none transition focus:border-[#0f2a5f] focus:bg-white focus:ring-4 focus:ring-blue-100"
                               placeholder="Contrasena del portal">
                    </div>
                </div>

                <button class="w-full rounded-2xl bg-[#0f2a5f] py-4 text-sm font-extrabold text-white shadow-lg shadow-blue-950/20 hover:bg-[#123879] transition">
                    Ingresar al portal
                </button>
            </form>

            <div class="mt-6 rounded-2xl bg-amber-50 border border-amber-100 p-4 text-xs text-amber-900">
                <strong>Acceso de prueba local:</strong> matricula A001 y contrasena alumno123, despues de ejecutar la migracion.
            </div>
        </section>
    </main>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('portal-alumno/sw.js') }}').catch(() => {});
            });
        }
    </script>
</body>
</html>
