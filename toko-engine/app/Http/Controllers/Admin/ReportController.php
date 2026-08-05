<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = Carbon::parse($validated['from'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now()->toDateString())->endOfDay();
        $query = Order::query()->whereBetween('placed_at', [$from, $to]);

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'orderCount' => (clone $query)->count(),
            'completedOrderCount' => (clone $query)->where('status', 'completed')->count(),
            'revenue' => (float) (clone $query)->where('payment_status', 'paid')->sum('total'),
            'orders' => $query->latest('placed_at')->paginate(20)->withQueryString(),
        ]);
    }
}
