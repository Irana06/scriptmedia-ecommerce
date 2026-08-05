<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'productCount' => Product::query()->count(),
            'pendingOrderCount' => Order::query()->where('status', 'pending')->count(),
            'orderCount' => Order::query()->count(),
            'revenue' => (float) Order::query()->where('payment_status', 'paid')->sum('total'),
            'recentOrders' => Order::query()->latest('placed_at')->limit(5)->get(),
        ]);
    }
}
