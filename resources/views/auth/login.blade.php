@php
    $nombreCorto = configInstitucional('nombre_corto', 'IDEJ');

    $nombreInstitucion = configInstitucional(
        'nombre_institucion',
        'Instituto de Altos Estudios Jurídicos de Jalisco'
    );

    $logoInstitucional = logoInstitucionalUrl();

    $colorPrimario = configInstitucional(
        'color_primario',
        '#1E3A8A'
    );

    $colorSecundario = configInstitucional(
        'color_secundario',
        '#0D173D'
    );

    $colorAcento = configInstitucional(
        'color_acento',
        '#C9A646'
    );
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="{{ $colorSecundario }}"
    >

    <title>
        Acceso institucional | {{ $nombreCorto }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: {{ $colorPrimario }};
            --secondary: {{ $colorSecundario }};
            --accent: {{ $colorAcento }};

            --background: #f4f5f7;
            --surface: #ffffff;
            --text: #172033;
            --muted: #687184;
            --border: #dce1e8;
            --danger: #b42318;
            --success: #166534;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            color: var(--text);
            background: var(--background);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        button {
            -webkit-tap-highlight-color: transparent;
        }

        [hidden] {
            display: none !important;
        }

        /* Estructura principal */

        .login-layout {
            display: grid;
            min-height: 100vh;
            grid-template-columns: minmax(430px, 46%) 1fr;
            overflow: hidden;
        }

        /* Panel institucional */

        .institution-panel {
            position: relative;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 46px 74px 42px;
            color: #ffffff;
            background:
                radial-gradient(
                    circle at 12% 18%,
                    rgba(255, 255, 255, 0.1),
                    transparent 28%
                ),
                linear-gradient(
                    145deg,
                    var(--primary) 0%,
                    var(--secondary) 62%,
                    #080d24 100%
                );
        }

        .institution-panel::after {
            position: absolute;
            z-index: 2;
            top: -2%;
            right: -1px;
            width: 80px;
            height: 104%;
            content: "";
            background: var(--background);
            clip-path: polygon(100% 0, 100% 100%, 0 100%, 72% 0);
        }

        .institution-grid {
            position: absolute;
            inset: 0;
            opacity: 0.09;
            background-image:
                linear-gradient(
                    rgba(255, 255, 255, 0.2) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.2) 1px,
                    transparent 1px
                );
            background-size: 48px 48px;
            mask-image: linear-gradient(
                to bottom,
                #000000,
                transparent 92%
            );
        }

        .institution-line {
            position: absolute;
            z-index: 1;
            top: -30%;
            right: 37px;
            width: 2px;
            height: 160%;
            overflow: hidden;
            transform: rotate(3.5deg);
            background: rgba(255, 255, 255, 0.08);
        }

        .institution-line::after {
            position: absolute;
            top: -18%;
            left: 0;
            width: 100%;
            height: 18%;
            content: "";
            background: linear-gradient(
                to bottom,
                transparent,
                var(--accent),
                transparent
            );
            animation: institutionalLine 6s ease-in-out infinite;
        }

        .institution-watermark {
            position: absolute;
            right: 42px;
            bottom: 72px;
            color: transparent;
            font-size: clamp(9rem, 18vw, 17rem);
            font-weight: 900;
            line-height: 0.8;
            letter-spacing: -0.09em;
            user-select: none;
            -webkit-text-stroke: 1px rgba(255, 255, 255, 0.07);
        }

        @keyframes institutionalLine {
            from {
                transform: translateY(0);
            }

            to {
                transform: translateY(760%);
            }
        }

        .institution-header,
        .institution-content,
        .institution-footer {
            position: relative;
            z-index: 3;
        }

        .institution-header {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .institution-logo {
            display: flex;
            width: 82px;
            height: 82px;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            padding: 7px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 21px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }

        .institution-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .institution-name {
            max-width: 330px;
        }

        .institution-abbreviation {
            margin: 0 0 5px;
            color: var(--accent);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.25em;
            text-transform: uppercase;
        }

        .institution-full-name {
            margin: 0;
            color: rgba(255, 255, 255, 0.91);
            font-size: 0.97rem;
            font-weight: 650;
            line-height: 1.45;
        }

        .institution-content {
            max-width: 580px;
            padding: 70px 28px 70px 0;
        }

        .institution-category {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 27px;
            color: rgba(255, 255, 255, 0.67);
            font-size: 0.71rem;
            font-weight: 750;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        .institution-category::before {
            width: 39px;
            height: 2px;
            content: "";
            background: var(--accent);
        }

        .institution-title {
            max-width: 550px;
            margin: 0;
            font-size: clamp(2.8rem, 4.5vw, 5rem);
            font-weight: 780;
            line-height: 0.99;
            letter-spacing: -0.055em;
        }

        .institution-title span {
            display: block;
            margin-top: 8px;
            color: var(--accent);
        }

        .institution-description {
            max-width: 500px;
            margin: 30px 0 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            line-height: 1.8;
        }

        .institution-areas {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 35px;
        }

        .institution-area {
            padding: 8px 13px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.77);
            background: rgba(255, 255, 255, 0.05);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .institution-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.52);
            font-size: 0.74rem;
        }

        .institution-footer::before {
            width: 7px;
            height: 7px;
            flex-shrink: 0;
            content: "";
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 5px rgba(201, 166, 70, 0.12);
        }

        /* Área del formulario */

        .access-panel {
            position: relative;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 62px 7vw 52px 8vw;
        }

        .access-meta {
            position: absolute;
            top: 32px;
            right: 42px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: var(--muted);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .access-meta svg {
            width: 16px;
            height: 16px;
            color: var(--primary);
        }

        .access-container {
            width: 100%;
            max-width: 435px;
            animation: accessEntrance 650ms cubic-bezier(.2, .8, .2, 1) both;
        }

        @keyframes accessEntrance {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-brand {
            display: none;
        }

        .access-category {
            margin: 0 0 13px;
            color: var(--primary);
            font-size: 0.71rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .access-title {
            margin: 0;
            color: var(--secondary);
            font-size: clamp(2.1rem, 3vw, 2.8rem);
            font-weight: 780;
            line-height: 1.08;
            letter-spacing: -0.045em;
        }

        .access-description {
            margin: 16px 0 34px;
            color: var(--muted);
            font-size: 0.93rem;
            line-height: 1.68;
        }

        /* Mensajes */

        .system-message {
            margin-bottom: 24px;
            padding: 13px 15px;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            color: var(--success);
            background: #f0fdf4;
            font-size: 0.84rem;
            line-height: 1.5;
        }

        /* Formulario */

        .form-group {
            margin-bottom: 23px;
        }

        .form-label {
            display: block;
            margin-bottom: 9px;
            color: #414a5c;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .input-container {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            width: 19px;
            height: 19px;
            color: #8790a0;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            height: 55px;
            padding: 0 48px;
            border: 1px solid var(--border);
            border-radius: 13px;
            outline: none;
            color: var(--text);
            background: rgba(255, 255, 255, 0.82);
            font-size: 0.91rem;
            transition:
                border-color 180ms ease,
                box-shadow 180ms ease,
                background 180ms ease;
        }

        .form-input::placeholder {
            color: #9da5b2;
        }

        .form-input:hover {
            border-color: #c2c9d4;
            background: var(--surface);
        }

        .form-input:focus {
            border-color: var(--primary);
            background: var(--surface);
            box-shadow:
                0 0 0 4px rgba(30, 58, 138, 0.1),
                inset 3px 0 0 var(--accent);
        }

        .form-input[aria-invalid="true"] {
            border-color: var(--danger);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            display: flex;
            width: 39px;
            height: 39px;
            cursor: pointer;
            transform: translateY(-50%);
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 10px;
            color: #778194;
            background: transparent;
            transition:
                color 160ms ease,
                background 160ms ease;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            color: var(--primary);
            background: #e9edf4;
            outline: none;
        }

        .password-toggle svg {
            width: 19px;
            height: 19px;
        }

        .input-error {
            margin: 8px 0 0;
            color: var(--danger);
            font-size: 0.77rem;
            line-height: 1.45;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin: 4px 0 29px;
        }

        .remember-option {
            display: inline-flex;
            cursor: pointer;
            align-items: center;
            gap: 9px;
            color: var(--muted);
            font-size: 0.79rem;
        }

        .remember-option input {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .recovery-link {
            color: var(--primary);
            font-size: 0.79rem;
            font-weight: 700;
            text-decoration: none;
        }

        .recovery-link:hover,
        .recovery-link:focus-visible {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        /* Botón de acceso */

        .login-button {
            position: relative;
            display: flex;
            width: 100%;
            height: 57px;
            cursor: pointer;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            padding: 0 9px 0 22px;
            border: 0;
            border-radius: 13px;
            color: #ffffff;
            background: var(--secondary);
            box-shadow: 0 14px 34px rgba(13, 23, 61, 0.18);
            font-size: 0.85rem;
            font-weight: 750;
            letter-spacing: 0.025em;
            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                background 180ms ease;
        }

        .login-button::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            content: "";
            background: var(--accent);
            transition: width 220ms ease;
        }

        .login-button:hover:not(:disabled) {
            transform: translateY(-2px);
            background: var(--primary);
            box-shadow: 0 18px 42px rgba(13, 23, 61, 0.25);
        }

        .login-button:hover:not(:disabled)::before {
            width: 9px;
        }

        .login-button:focus-visible {
            outline: 3px solid rgba(30, 58, 138, 0.2);
            outline-offset: 3px;
        }

        .login-button:disabled {
            cursor: wait;
            opacity: 0.78;
        }

        .button-action {
            position: relative;
            z-index: 1;
        }

        .button-icon {
            position: relative;
            z-index: 1;
            display: flex;
            width: 39px;
            height: 39px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: var(--secondary);
            background: var(--accent);
        }

        .button-icon svg {
            width: 18px;
            height: 18px;
        }

        .button-loader {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(13, 23, 61, 0.25);
            border-top-color: var(--secondary);
            border-radius: 50%;
            animation: buttonLoader 700ms linear infinite;
        }

        @keyframes buttonLoader {
            to {
                transform: rotate(360deg);
            }
        }

        /* Nota de seguridad */

        .security-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 28px;
            padding-top: 23px;
            border-top: 1px solid var(--border);
            color: #858e9e;
            font-size: 0.71rem;
            line-height: 1.58;
        }

        .security-notice svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            margin-top: 1px;
            color: var(--primary);
        }

        .access-footer {
            margin-top: 32px;
            color: #959daa;
            font-size: 0.67rem;
            line-height: 1.7;
        }

        /* Adaptación para tabletas y móviles */

        @media (max-width: 960px) {
            .login-layout {
                display: block;
            }

            .institution-panel {
                display: none;
            }

            .access-panel {
                min-height: 100vh;
                padding: 82px 24px 40px;
                background:
                    radial-gradient(
                        circle at top right,
                        rgba(30, 58, 138, 0.08),
                        transparent 38%
                    ),
                    var(--background);
            }

            .access-meta {
                top: 24px;
                right: 24px;
            }

            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 42px;
            }

            .mobile-logo {
                display: flex;
                width: 64px;
                height: 64px;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                padding: 6px;
                border-radius: 18px;
                background: var(--secondary);
                box-shadow: 0 12px 28px rgba(13, 23, 61, 0.18);
            }

            .mobile-logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }

            .mobile-name strong,
            .mobile-name span {
                display: block;
            }

            .mobile-name strong {
                color: var(--secondary);
                font-size: 0.96rem;
            }

            .mobile-name span {
                margin-top: 4px;
                color: var(--muted);
                font-size: 0.69rem;
                line-height: 1.4;
            }
        }

        @media (max-width: 520px) {
            .access-panel {
                align-items: flex-start;
                padding-right: 20px;
                padding-left: 20px;
            }

            .access-meta {
                font-size: 0.61rem;
            }

            .current-date {
                display: none;
            }

            .mobile-brand {
                margin-bottom: 34px;
            }

            .access-title {
                font-size: 2rem;
            }

            .access-description {
                margin-bottom: 29px;
            }

            .form-options {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>

<main class="login-layout">

    {{-- Identidad institucional --}}
    <section
        class="institution-panel"
        aria-label="Identidad institucional"
    >

        <div class="institution-grid" aria-hidden="true"></div>
        <div class="institution-line" aria-hidden="true"></div>

        <div
            class="institution-watermark"
            aria-hidden="true"
        >
            {{ $nombreCorto }}
        </div>

        <header class="institution-header">

            <div class="institution-logo">
                <img
                    src="{{ $logoInstitucional }}"
                    alt="Escudo de {{ $nombreCorto }}"
                >
            </div>

            <div class="institution-name">
                <p class="institution-abbreviation">
                    {{ $nombreCorto }}
                </p>

                <p class="institution-full-name">
                    {{ $nombreInstitucion }}
                </p>
            </div>

        </header>

        <div class="institution-content">

            <p class="institution-category">
                Sistema institucional de gestión
            </p>

            <h1 class="institution-title">
                Gestión académica
                <span>y administrativa.</span>
            </h1>

            <p class="institution-description">
                Plataforma interna para la administración integral de los
                procesos académicos, escolares, financieros y operativos del
                Instituto de Altos Estudios Jurídicos de Jalisco.
            </p>

            <div
                class="institution-areas"
                aria-label="Áreas integradas en el sistema"
            >
                <span class="institution-area">
                    Dirección
                </span>

                <span class="institution-area">
                    Control escolar
                </span>

                <span class="institution-area">
                    Administración
                </span>

                <span class="institution-area">
                    Finanzas
                </span>
            </div>

        </div>

        <footer class="institution-footer">
            Sistema de uso interno para personal autorizado
        </footer>

    </section>

    {{-- Acceso al sistema --}}
    <section class="access-panel">

        <div
            class="access-meta"
            aria-label="Información del acceso"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <rect
                    x="5"
                    y="10"
                    width="14"
                    height="11"
                    rx="3"
                ></rect>

                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
            </svg>

            <span>Portal interno</span>

            <span
                id="currentDate"
                class="current-date"
            ></span>
        </div>

        <div class="access-container">

            {{-- Identidad visible en dispositivos móviles --}}
            <div class="mobile-brand">

                <div class="mobile-logo">
                    <img
                        src="{{ $logoInstitucional }}"
                        alt="Escudo de {{ $nombreCorto }}"
                    >
                </div>

                <div class="mobile-name">
                    <strong>{{ $nombreCorto }}</strong>

                    <span>
                        {{ $nombreInstitucion }}
                    </span>
                </div>

            </div>

            <p class="access-category">
                Personal académico y administrativo
            </p>

            <h2 class="access-title">
                Acceso institucional
            </h2>

            <p class="access-description">
                Ingrese con la cuenta institucional que le fue asignada para
                acceder a las funciones correspondientes a su perfil.
            </p>

            @if (session('status'))
                <div
                    class="system-message"
                    role="status"
                    aria-live="polite"
                >
                    {{ session('status') }}
                </div>
            @endif

            <form
                id="loginForm"
                method="POST"
                action="{{ route('login') }}"
            >
                @csrf

                {{-- Correo institucional --}}
                <div class="form-group">

                    <label
                        for="email"
                        class="form-label"
                    >
                        Correo institucional
                    </label>

                    <div class="input-container">

                        <svg
                            class="input-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="3"
                                y="5"
                                width="18"
                                height="14"
                                rx="3"
                            ></rect>

                            <path d="m4 7 8 6 8-6"></path>
                        </svg>

                        <input
                            id="email"
                            class="form-input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="usuario@idej.edu.mx"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            @if ($errors->has('email'))
                                aria-describedby="email-error"
                            @endif
                        >

                    </div>

                    @error('email')
                        <p
                            id="email-error"
                            class="input-error"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Contraseña --}}
                <div class="form-group">

                    <label
                        for="password"
                        class="form-label"
                    >
                        Contraseña
                    </label>

                    <div class="input-container">

                        <svg
                            class="input-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="5"
                                y="10"
                                width="14"
                                height="11"
                                rx="3"
                            ></rect>

                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        </svg>

                        <input
                            id="password"
                            class="form-input"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Ingrese su contraseña"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            @if ($errors->has('password'))
                                aria-describedby="password-error"
                            @endif
                        >

                        <button
                            id="togglePassword"
                            class="password-toggle"
                            type="button"
                            aria-label="Mostrar contraseña"
                            aria-controls="password"
                        >
                            <svg
                                id="eyeOpen"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                ></path>

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.7"
                                ></circle>
                            </svg>

                            <svg
                                id="eyeClosed"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                                hidden
                            >
                                <path d="m3 3 18 18"></path>

                                <path
                                    d="M10.6 6.2A8.9 8.9 0 0 1 12 6c6 0 9.5 6 9.5 6a16.2 16.2 0 0 1-2.1 2.8"
                                ></path>

                                <path
                                    d="M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 6 9.5 6a9.7 9.7 0 0 0 3.4-.6"
                                ></path>
                            </svg>
                        </button>

                    </div>

                    @error('password')
                        <p
                            id="password-error"
                            class="input-error"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div class="form-options">

                    <label
                        for="remember_me"
                        class="remember-option"
                    >
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                        >

                        <span>
                            Mantener la sesión iniciada
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            class="recovery-link"
                            href="{{ route('password.request') }}"
                        >
                            Recuperar acceso
                        </a>
                    @endif

                </div>

                <button
                    id="loginButton"
                    class="login-button"
                    type="submit"
                >
                    <span
                        id="buttonText"
                        class="button-action"
                    >
                        Ingresar al sistema
                    </span>

                    <span class="button-icon">

                        <svg
                            id="buttonArrow"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"></path>
                            <path d="m14 7 5 5-5 5"></path>
                        </svg>

                        <span
                            id="buttonLoader"
                            class="button-loader"
                            aria-hidden="true"
                            hidden
                        ></span>

                    </span>
                </button>

            </form>

            <div class="security-notice">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path
                        d="M12 3 5 6v5c0 4.6 2.8 8.5 7 10 4.2-1.5 7-5.4 7-10V6l-7-3Z"
                    ></path>

                    <path d="m9.5 12 1.7 1.7 3.5-4"></path>
                </svg>

                <span>
                    El acceso está restringido al personal autorizado. Las
                    operaciones realizadas dentro de la plataforma pueden ser
                    registradas en la bitácora institucional para fines de
                    seguridad, seguimiento y control administrativo.
                </span>

            </div>

            <footer class="access-footer">
                © {{ date('Y') }} {{ $nombreInstitucion }}.
                Todos los derechos reservados.
            </footer>

        </div>

    </section>

</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const password = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const buttonText = document.getElementById('buttonText');
        const buttonArrow = document.getElementById('buttonArrow');
        const buttonLoader = document.getElementById('buttonLoader');

        const currentDate = document.getElementById('currentDate');

        togglePassword?.addEventListener('click', () => {
            const isVisible = password.type === 'text';

            password.type = isVisible ? 'password' : 'text';

            eyeOpen.hidden = !isVisible;
            eyeClosed.hidden = isVisible;

            togglePassword.setAttribute(
                'aria-label',
                isVisible
                    ? 'Mostrar contraseña'
                    : 'Ocultar contraseña'
            );
        });

        loginForm?.addEventListener('submit', () => {
            loginButton.disabled = true;
            loginButton.setAttribute('aria-busy', 'true');

            buttonText.textContent = 'Validando credenciales';

            buttonArrow.hidden = true;
            buttonLoader.hidden = false;
        });

        if (currentDate) {
            currentDate.textContent = new Intl.DateTimeFormat('es-MX', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(new Date());
        }
    });
</script>

</body>
</html>
