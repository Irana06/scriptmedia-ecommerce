@props([
    'href' => null,
    'variant' => 'orange',
    'pill' => true,
    'type' => 'button',
])

@php
    $variantClasses = match ($variant) {
        'navy' => 'bg-navy text-white hover:bg-navy-mid focus-visible:outline-navy',
        default => 'bg-orange text-navy hover:bg-orange-light focus-visible:outline-orange',
    };

    $classes = "inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes, 'rounded-full' => $pill, 'rounded-xl' => ! $pill]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes, 'rounded-full' => $pill, 'rounded-xl' => ! $pill]) }}>
        {{ $slot }}
    </button>
@endif
