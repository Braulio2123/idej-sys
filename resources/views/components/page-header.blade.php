@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class(['idej-page-header']) }}>
    <div class="min-w-0">
        @if($eyebrow)
            <p class="idej-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="idej-page-title">{{ $title }}</h1>
        @if($description)
            <p class="idej-page-subtitle">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</header>
