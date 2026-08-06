@props([
    'title',
    'description',
    'icon' => 'box',
    'actionHref' => null,
    'actionLabel' => null,
    'compact' => false,
])

<div {{ $attributes->class([
    'rounded-card border border-dashed border-line bg-linear-to-br from-white to-offwhite text-center',
    'px-6 py-9 sm:px-8' => $compact,
    'px-6 py-12 sm:px-10 sm:py-16' => ! $compact,
]) }}>
    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-tosca-tint text-tosca ring-1 ring-tosca/15">
        @switch($icon)
            @case('cart')
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                @break
            @case('order')
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/></svg>
                @break
            @case('report')
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                @break
            @case('payment')
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M2.5 9.5h19M7 15h3"/></svg>
                @break
            @default
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg>
        @endswitch
    </span>
    <h3 class="mt-5 text-xl font-semibold text-navy">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-ink-soft">{{ $description }}</p>

    @if ($actionHref && $actionLabel)
        <div class="mt-6"><x-ui.button :href="$actionHref">{{ $actionLabel }}</x-ui.button></div>
    @endif
</div>
