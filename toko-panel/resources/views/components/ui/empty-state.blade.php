@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('mx-auto flex max-w-md flex-col items-center px-5 py-8 text-center') }}>
    <span class="flex size-12 items-center justify-center rounded-2xl bg-tosca-tint text-tosca" aria-hidden="true">
        <svg class="size-6" viewBox="0 0 24 24" fill="none">
            <path d="M5 6h14v12H5zM8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
    <h3 class="mt-4 text-lg text-navy">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 text-sm leading-6 text-ink-soft">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
