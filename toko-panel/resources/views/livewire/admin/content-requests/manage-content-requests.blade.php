<div class="space-y-8">
    <x-ui.section-header
        eyebrow="Dukungan konten"
        title="Tiket Perubahan Konten"
        description="Pantau permintaan tenant dan lanjutkan tiket dari pending hingga selesai atau ditolak."
    />

    @error('statusUpdate')
        <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-900">{{ $message }}</div>
    @enderror

    <x-ui.card>
        <div class="grid gap-5 sm:grid-cols-2">
            <flux:select wire:model.live="tenantId" label="Tenant">
                <flux:select.option value="">Semua tenant</flux:select.option>
                @foreach ($tenants as $tenant)
                    <flux:select.option :value="$tenant->id">{{ $tenant->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="status" label="Status">
                <flux:select.option value="">Semua status</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="in_progress">In progress</flux:select.option>
                <flux:select.option value="done">Done</flux:select.option>
                <flux:select.option value="rejected">Rejected</flux:select.option>
            </flux:select>
        </div>
    </x-ui.card>

    <x-ui.card :padding="false" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-5xl text-left text-sm">
                <thead class="bg-offwhite text-ink-soft">
                    <tr>
                        <th class="px-6 py-3 font-normal">Dibuat</th>
                        <th class="px-6 py-3 font-normal">Tenant</th>
                        <th class="px-6 py-3 font-normal">Permintaan</th>
                        <th class="px-6 py-3 font-normal">Status</th>
                        <th class="px-6 py-3 text-right font-normal">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse ($contentRequests as $contentRequest)
                        <tr wire:key="admin-content-request-{{ $contentRequest->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-ink-soft">{{ $contentRequest->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-navy">{{ $contentRequest->tenant->name }}</p>
                                <p class="mt-1 text-xs text-ink-soft">{{ $contentRequest->requestedBy->name }}</p>
                            </td>
                            <td class="max-w-xl px-6 py-4 leading-6 text-ink">{{ $contentRequest->description }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="match ($contentRequest->status) { 'done' => 'tosca', 'rejected' => 'danger', 'in_progress' => 'navy', default => 'orange' }">
                                    {{ str($contentRequest->status)->replace('_', ' ')->title() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($contentRequest->status === 'pending')
                                        <button type="button" wire:click="updateStatus({{ $contentRequest->id }}, 'in_progress')" class="rounded-full border border-navy px-3 py-2 text-xs font-semibold text-navy hover:bg-offwhite">Mulai</button>
                                        <button type="button" wire:click="updateStatus({{ $contentRequest->id }}, 'rejected')" wire:confirm="Tolak tiket ini?" class="rounded-full border border-red-300 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Tolak</button>
                                    @elseif ($contentRequest->status === 'in_progress')
                                        <button type="button" wire:click="updateStatus({{ $contentRequest->id }}, 'done')" class="rounded-full border border-tosca px-3 py-2 text-xs font-semibold text-navy hover:bg-tosca-tint">Selesai</button>
                                        <button type="button" wire:click="updateStatus({{ $contentRequest->id }}, 'rejected')" wire:confirm="Tolak tiket ini?" class="rounded-full border border-red-300 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Tolak</button>
                                    @else
                                        <span class="text-xs text-ink-soft">Tidak ada aksi</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-ink-soft">Tidak ada tiket untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
