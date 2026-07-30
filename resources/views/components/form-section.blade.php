@props([
    'title',
    'description' => null,
])

<section {{ $attributes->class(['idej-form-section']) }}>
    <div class="mb-4">
        <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
        @if($description)
            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $description }}</p>
        @endif
    </div>
    <div class="space-y-4">
        {{ $slot }}
    </div>
</section>
