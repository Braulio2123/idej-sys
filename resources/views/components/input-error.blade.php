@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1.5 space-y-1 text-sm font-medium text-red-600']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5">
                <i class="bx bx-error-circle mt-0.5 text-base"></i>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
