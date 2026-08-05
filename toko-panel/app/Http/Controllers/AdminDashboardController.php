<?php

namespace App\Http\Controllers;

use App\Models\ContentChangeRequest;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'metrics' => [
                'tenants' => Tenant::query()->count(),
                'active_plans' => Plan::query()->where('is_active', true)->count(),
                'unpaid_invoices' => Invoice::query()->whereIn('status', ['unpaid', 'overdue'])->count(),
                'pending_requests' => ContentChangeRequest::query()->whereIn('status', ['pending', 'in_progress'])->count(),
            ],
        ]);
    }
}
