@props([
    'title' => 'Sin registros',
    'description' => 'No hay información disponible para mostrar.',
    'icon' => 'bx-inbox',
])

<div {{ $attributes->class(['idej-empty-state']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200">
        <i class="bx {{ $icon }} text-2xl"></i>
    </span>
    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mt-1 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
