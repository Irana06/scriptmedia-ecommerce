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

    $classes = "inline-flex cursor-pointer items-center justify-center gap-2 px-5 py-3 text-sm font-semibold transition duration-200 hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-none {$variantClasses}";
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
