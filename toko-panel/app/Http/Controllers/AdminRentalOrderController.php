<?php

namespace App\Http\Controllers;

use App\Jobs\ProvisionTenantJob;
use App\Models\RentalOrder;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminRentalOrderController extends Controller
{
    public function index(): View
    {
        return view('admin.rental-orders.index', [
            'orders' => RentalOrder::query()->with(['user', 'plan', 'tenant'])->latest()->paginate(20),
        ]);
    }

    public function provision(RentalOrder $rentalOrder): RedirectResponse
    {
        abort_unless($rentalOrder->status === 'paid' && $rentalOrder->tenant_id === null, Response::HTTP_UNPROCESSABLE_ENTITY);

        $tenant = DB::transaction(function () use ($rentalOrder): Tenant {
            $locked = RentalOrder::query()->with('plan')->lockForUpdate()->findOrFail($rentalOrder->id);
            abort_unless($locked->status === 'paid' && $locked->tenant_id === null, Response::HTTP_UNPROCESSABLE_ENTITY);

            $tenant = Tenant::create([
                'name' => $locked->business_name,
                'subdomain' => $locked->desired_subdomain,
                'custom_domain' => $locked->custom_domain,
                'owner_user_id' => $locked->user_id,
                'database_name' => 'tenant_'.Str::of($locked->desired_subdomain)->replace('-', '_'),
                'provisioning_status' => 'pending',
                'store_status' => 'active',
            ]);

            $periodStart = $locked->paid_at?->copy()->startOfDay() ?? today();
            $periodEnd = $locked->billing_cycle === 'annual'
                ? $periodStart->copy()->addYear()->subDay()
                : $periodStart->copy()->addMonth()->subDay();

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $locked->plan_id,
                'billing_cycle' => $locked->billing_cycle,
                'status' => 'active',
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_billing_date' => $periodEnd->copy()->addDay(),
                'pending_plan_id' => null,
            ]);

            $locked->update(['tenant_id' => $tenant->id, 'status' => 'provisioning']);

            return $tenant;
        });

        if (app()->isLocal()) {
            ProvisionTenantJob::dispatchSync($tenant->id);
        } else {
            ProvisionTenantJob::dispatch($tenant->id)->afterCommit();
        }

        return back()->with('status', 'Provisioning toko dimulai.');
    }

    public function simulatePayment(RentalOrder $rentalOrder): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), Response::HTTP_NOT_FOUND);

        DB::transaction(function () use ($rentalOrder): void {
            $locked = RentalOrder::query()->lockForUpdate()->findOrFail($rentalOrder->id);
            abort_unless(in_array($locked->status, ['awaiting_payment', 'cancelled'], true) && $locked->tenant_id === null, Response::HTTP_UNPROCESSABLE_ENTITY);

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => 'LOCAL-'.Str::upper(Str::random(10)),
                'payment_metadata' => [
                    'source' => 'local_admin_simulation',
                    'simulated_at' => now()->toIso8601String(),
                ],
            ]);
        });

        return back()->with('status', 'Pembayaran berhasil disimulasikan untuk pengujian lokal.');
    }
}
