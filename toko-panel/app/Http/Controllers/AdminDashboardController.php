<?php

namespace App\Http\Controllers;

use App\Models\ContentChangeRequest;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $mrr = Subscription::query()
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('subscriptions.status', 'active')
            ->selectRaw("COALESCE(SUM(CASE WHEN subscriptions.billing_cycle = 'annual' THEN plans.price_care_annual / 12 ELSE plans.price_care_monthly END), 0) AS mrr")
            ->value('mrr');

        return view('dashboard', [
            'metrics' => [
                'tenants' => Tenant::query()->count(),
                'active_tenants' => Tenant::query()->where('store_status', 'active')->count(),
                'tenant_statuses' => [
                    'active' => Tenant::query()->where('store_status', 'active')->count(),
                    'grace_period' => Tenant::query()->where('store_status', 'grace_period')->count(),
                    'suspended' => Tenant::query()->where('store_status', 'suspended')->count(),
                    'cancelled' => Tenant::query()->where('store_status', 'cancelled')->count(),
                ],
                'active_plans' => Plan::query()->where('is_active', true)->count(),
                'unpaid_invoices' => Invoice::query()->whereIn('status', ['unpaid', 'overdue'])->count(),
                'pending_requests' => ContentChangeRequest::query()->whereIn('status', ['pending', 'in_progress'])->count(),
                'mrr' => (float) $mrr,
            ],
        ]);
    }
}
