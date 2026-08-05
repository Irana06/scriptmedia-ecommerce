@props([
    'eyebrow',
    'title',
    'description' => null,
])

<header {{ $attributes->class('mx-auto max-w-2xl text-center') }}>
    <p class="text-xs font-semibold tracking-[0.24em] text-tosca uppercase">{{ $eyebrow }}</p>
    <h2 class="mt-3 text-3xl text-navy sm:text-4xl">{{ $title }}</h2>

    @if ($description)
        <p class="mt-4 text-base leading-7 text-ink-soft">{{ $description }}</p>
    @endif
</header>
