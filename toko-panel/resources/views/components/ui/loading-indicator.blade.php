@props([
    'label' => 'Memproses data...',
    'target' => null,
])

<div
    wire:loading.flex
    @if ($target) wire:target="{{ $target }}" @endif
    class="fixed right-5 bottom-5 z-50 hidden items-center gap-3 rounded-full bg-navy px-5 py-3 text-sm font-semibold text-white shadow-xl shadow-navy/20"
    role="status"
    aria-live="polite"
>
    <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
    </svg>
    {{ $label }}
</div>
