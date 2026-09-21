<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'productCount' => StorefrontContext::scopeAdminBySlug(Product::query())->count(),
            'pendingOrderCount' => Order::query()->forAdminStore()->where('status', 'pending')->count(),
            'orderCount' => Order::query()->forAdminStore()->count(),
            'revenue' => (float) Order::query()->forAdminStore()->where('payment_status', 'paid')->sum('total'),
            'recentOrders' => Order::query()->forAdminStore()->latest('placed_at')->limit(5)->get(),
            'managedStore' => StorefrontContext::adminStore(),
        ]);
    }
}
