@props([
    'loadingLabel' => 'Memproses...',
    'variant' => 'orange',
    'disabled' => false,
])

<x-ui.button
    type="submit"
    :variant="$variant"
    :disabled="$disabled"
    :data-blocked="$disabled ? 'true' : null"
    x-data="{ loading: false }"
    x-on:click="if ($el.dataset.blocked !== 'true' && $el.form?.checkValidity()) loading = true"
    x-bind:disabled="loading || $el.dataset.blocked === 'true'"
    {{ $attributes->class('disabled:cursor-not-allowed disabled:opacity-55') }}
>
    <span x-show="! loading">{{ $slot }}</span>
    <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-80" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg>
        {{ $loadingLabel }}
    </span>
</x-ui.button>
