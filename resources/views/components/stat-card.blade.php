@props([
    'label',
    'value',
    'hint' => null,
    'href' => null,
    'icon' => 'bx-bar-chart-alt-2',
    'tone' => 'blue',
])

@php
    $toneClasses = [
        'blue' => 'bg-blue-50 text-blue-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'red' => 'bg-red-50 text-red-700',
        'violet' => 'bg-violet-50 text-violet-700',
        'slate' => 'bg-slate-100 text-slate-700',
    ];
    $iconClasses = $toneClasses[$tone] ?? $toneClasses['blue'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['idej-stat-card block']) }}>
        <div class="relative z-10 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="idej-stat-label">{{ $label }}</p>
                <p class="idej-stat-value">{{ $value }}</p>
                @if($hint)
                    <p class="idej-stat-hint">{{ $hint }}</p>
                @endif
            </div>
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconClasses }}">
                <i class="bx {{ $icon }} text-xl"></i>
            </span>
        </div>
    </a>
@else
    <article {{ $attributes->class(['idej-stat-card']) }}>
        <div class="relative z-10 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="idej-stat-label">{{ $label }}</p>
                <p class="idej-stat-value">{{ $value }}</p>
                @if($hint)
                    <p class="idej-stat-hint">{{ $hint }}</p>
                @endif
            </div>
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconClasses }}">
                <i class="bx {{ $icon }} text-xl"></i>
            </span>
        </div>
    </article>
@endif
