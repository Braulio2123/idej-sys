@props([
    'status',
    'label' => null,
])

@php
    $normalized = strtolower(trim((string) $status));
    $classes = match (true) {
        in_array($normalized, ['activo', 'activa', 'aprobado', 'aprobada', 'pagado', 'pagada', 'completado', 'completada'], true) => 'idej-badge-success',
        in_array($normalized, ['pendiente', 'programado', 'programada', 'en_revision', 'en revisión', 'observada', 'tentativa'], true) => 'idej-badge-warning',
        in_array($normalized, ['rechazado', 'rechazada', 'cancelado', 'cancelada', 'vencido', 'vencida', 'inactivo', 'inactiva'], true) => 'idej-badge-danger',
        default => 'idej-badge-info',
    };
@endphp

<span {{ $attributes->class([$classes]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $label ?? $status }}
</span>
