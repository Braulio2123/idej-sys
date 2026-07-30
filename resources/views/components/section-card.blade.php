@props([
    'title' => null,
    'description' => null,
    'padding' => true,
])

<section {{ $attributes->class(['idej-panel' => $padding, 'idej-surface overflow-hidden' => ! $padding]) }}>
    @if($title || $description || isset($actions))
        <div @class(['idej-section-heading', 'mb-5' => $padding, 'border-b border-slate-200 px-5 py-4 sm:px-6' => ! $padding])>
            <div>
                @if($title)
                    <h2 class="idej-section-title">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="idej-section-description">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['px-5 py-5 sm:px-6' => ! $padding])>
        {{ $slot }}
    </div>
</section>
