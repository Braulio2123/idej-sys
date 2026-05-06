<nav x-data="{ open: false }"
     class="bg-[#0D133A] border-b border-blue-900 shadow-lg text-white">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- LOGO + NOMBRE --}}
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ logoInstitucionalUrl() }}" class="h-10 w-auto drop-shadow-lg">
                    <span class="text-lg font-semibold tracking-wide hidden sm:inline">
                        {{ configInstitucional('nombre_corto', 'IDEJ') }}-SYS
                    </span>
                </a>

                {{-- NAV LINKS --}}
                <div class="hidden sm:flex sm:ms-10 space-x-6">
                    <a href="{{ route('dashboard') }}"
                       class="text-sm font-medium tracking-wide {{ request()->routeIs('dashboard') ? 'text-blue-300' : 'text-white/80 hover:text-blue-300' }}">
                        Dashboard
                    </a>
                </div>
            </div>

            {{-- SETTINGS DROPDOWN --}}
            <div class="hidden sm:flex sm:items-center">

                {{-- BOTÓN USUARIO --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 rounded-md
                                   text-white bg-white/10 hover:bg-white/20
                                   font-medium text-sm transition">

                            {{-- Avatar redondo con inicial --}}
                            <div class="h-8 w-8 bg-blue-600 text-white rounded-full
                                        flex items-center justify-center font-semibold shadow">
                                {{ strtoupper(substr(Auth::user()->nombre, 0, 1)) }}
                            </div>

                            <span class="ml-3">{{ Auth::user()->nombre }}</span>

                            <svg class="ml-2 h-4 w-4 text-blue-200"
                                 xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20">
                                <path fill="currentColor"
                                      d="M5.23 7.21a1 1 0 011.41 0L10 10.56l3.36-3.35a1 1 0 011.41 1.42l-4.06 4.06a1 1 0 01-1.41 0L5.23 8.63a1 1 0 010-1.42z"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link class="text-gray-700"
                            :href="route('profile.edit')">
                            Perfil
                        </x-dropdown-link>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link class="text-gray-700"
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- MOBILE HAMBURGER --}}
            <div class="sm:hidden">
                <button @click="open = !open"
                    class="p-2 rounded-md bg-white/10 text-white hover:bg-white/20">

                    <svg class="h-6 w-6" stroke="currentColor" fill="none">
                        <path :class="{'hidden': open, 'inline-flex': ! open}"
                              class="inline-flex" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open}"
                              class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden bg-[#141A4F]">

        <div class="pt-2 pb-3 space-y-1 border-t border-blue-800">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="block px-4 py-2 text-sm font-medium
                      {{ request()->routeIs('dashboard') ? 'text-blue-300' : 'text-white/80 hover:text-blue-300' }}">
                Dashboard
            </a>
        </div>

        {{-- USUARIO RESPONSIVE --}}
        <div class="pt-4 pb-1 border-t border-blue-900">

            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->nombre }}</div>
                <div class="font-medium text-sm text-blue-200">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">

                <a href="{{ route('profile.edit') }}"
                   class="block px-4 py-2 text-sm text-white/80 hover:text-blue-300">
                    Perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       class="block px-4 py-2 text-sm text-white/80 hover:text-blue-300"
                       onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar sesión
                    </a>
                </form>

            </div>
        </div>
    </div>
</nav>
