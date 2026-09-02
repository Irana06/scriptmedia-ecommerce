<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
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

        $orders = $user->rentalOrders()
            ->with(['plan', 'tenant'])
            ->latest()
            ->get();

        return view('portal.dashboard', compact('tenants', 'orders'));
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
}
