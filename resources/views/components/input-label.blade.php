@props(['value'])

<label {{ $attributes->merge(['class' => 'idej-field-label']) }}>
    {{ $value ?? $slot }}
</label>
