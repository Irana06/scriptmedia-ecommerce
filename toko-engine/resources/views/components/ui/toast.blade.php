@props([
    'variant' => 'success',
    'duration' => 4500,
])

@php
    $isError = $variant === 'error';
@endphp

<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, {{ (int) $duration }})"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-5 opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-5 opacity-0"
    role="{{ $isError ? 'alert' : 'status' }}"
    {{ $attributes->class([
        'pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white p-4 text-sm shadow-xl',
        'border-red-200 text-red-900' => $isError,
        'border-tosca/30 text-navy' => ! $isError,
    ]) }}
>
    <span @class([
        'mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full text-sm font-bold',
        'bg-red-100 text-red-700' => $isError,
        'bg-tosca-tint text-tosca' => ! $isError,
    ]) aria-hidden="true">
        {{ $isError ? '!' : '✓' }}
    </span>

    <div class="min-w-0 flex-1 leading-6">{{ $slot }}</div>

    <button type="button" x-on:click="show = false" class="flex size-7 shrink-0 cursor-pointer items-center justify-center rounded-full text-lg text-ink-soft transition hover:bg-offwhite hover:text-navy" aria-label="Tutup notifikasi">×</button>
</div>
