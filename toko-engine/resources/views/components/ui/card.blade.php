@props([
    'padding' => true,
])

<div {{ $attributes->class([
    'rounded-card border border-line bg-white shadow-card',
    'p-6 sm:p-7' => $padding,
]) }}>
    {{ $slot }}
</div>
