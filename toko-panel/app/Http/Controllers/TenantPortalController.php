<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TenantPortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $tenants = $user->ownedTenants()
            ->with('currentSubscription.plan')
            ->withCount(['invoices', 'contentChangeRequests'])
            ->orderBy('name')
            ->get();

        return view('portal.dashboard', compact('tenants'));
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load([
            'currentSubscription.plan',
            'invoices' => fn ($query) => $query->latest('due_date'),
            'contentChangeRequests' => fn ($query) => $query->latest(),
        ]);

        return view('portal.tenant', compact('tenant'));
    }

    public function storeContentRequest(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $subscription = $tenant->currentSubscription()->with('plan')->first();

        abort_unless($subscription instanceof Subscription, Response::HTTP_UNPROCESSABLE_ENTITY, 'Tenant belum memiliki subscription aktif.');

        $periodStart = $subscription->current_period_start->startOfMonth();
        $quota = $subscription->plan->content_request_quota;

        DB::transaction(function () use ($tenant, $user, $validated, $periodStart, $quota): void {
            $usage = $tenant->planFeatureUsages()->firstOrCreate(
                ['period_start' => $periodStart],
                ['products_count' => 0, 'content_requests_used' => 0],
            );

            if ($usage->content_requests_used >= $quota) {
                throw ValidationException::withMessages([
                    'description' => 'Kuota request konten untuk periode ini sudah habis.',
                ]);
            }

            $tenant->contentChangeRequests()->create([
                'requested_by_user_id' => $user->getKey(),
                'description' => (string) $validated['description'],
                'status' => 'pending',
                'usage_period_start' => $periodStart,
            ]);

            $usage->increment('content_requests_used');
        });

        return back()->with('status', 'Request perubahan konten berhasil dikirim.');
    }
}
