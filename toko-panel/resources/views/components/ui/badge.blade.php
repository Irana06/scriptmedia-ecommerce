@props([
    'variant' => 'tosca',
])

@php
    $variantClasses = match ($variant) {
        'orange' => 'bg-orange text-navy',
        'navy' => 'bg-navy text-white',
        'danger' => 'bg-red-100 text-red-800',
        default => 'bg-tosca text-white',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold tracking-wide {$variantClasses}") }}>
    {{ $slot }}
</span>
