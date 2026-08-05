<?php

namespace App\Livewire\Admin\ContentRequests;

use App\Models\ContentChangeRequest;
use App\Models\Tenant;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tiket Konten')]
class ManageContentRequests extends Component
{
    public string $tenantId = '';

    public string $status = '';

    /** @var array<string, list<string>> */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['in_progress', 'rejected'],
        'in_progress' => ['done', 'rejected'],
        'done' => [],
        'rejected' => [],
    ];

    public function mount(): void
    {
        $this->authorizeAdmin();
    }

    public function updateStatus(int $contentRequestId, string $status): void
    {
        $this->authorizeAdmin();

        $contentRequest = ContentChangeRequest::query()->findOrFail($contentRequestId);

        if (! in_array($status, self::ALLOWED_TRANSITIONS[$contentRequest->status], true)) {
            throw ValidationException::withMessages([
                'statusUpdate' => 'Perubahan status tiket tidak valid.',
            ]);
        }

        $contentRequest->update(['status' => $status]);
        Flux::toast(variant: 'success', text: 'Status tiket berhasil diperbarui.');
    }

    public function render(): View
    {
        $this->authorizeAdmin();

        $tenantId = $this->tenantId !== '' ? (int) $this->tenantId : null;

        return view('livewire.admin.content-requests.manage-content-requests', [
            'tenants' => Tenant::query()->orderBy('name')->get(),
            'contentRequests' => ContentChangeRequest::query()
                ->with(['tenant', 'requestedBy'])
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(100)
                ->get(),
        ]);
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->hasRole('admin'), 403);
    }
}
