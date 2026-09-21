<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStockService;
use App\Support\StorefrontContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $orders = Order::query()
            ->forAdminStore()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('placed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'managedStore' => StorefrontContext::adminStore(),
        ]);
    }

    public function show(Order $order): View
    {
        $this->ensureManageable($order);
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order, OrderStockService $stock): RedirectResponse
    {
        $this->ensureManageable($order);
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
        ]);
        $order->update($validated);

        // Cancelling by hand has to free the reserved stock too, otherwise the
        // catalogue keeps holding items for an order nobody will ever pay for.
        $released = $validated['status'] === 'cancelled' && $stock->release($order);

        return back()->with('success', $released
            ? 'Status pesanan diperbarui dan stok dikembalikan ke katalog.'
            : 'Status pesanan berhasil diperbarui.');
    }

    /** A store-bound account may only open orders placed at its own store. */
    private function ensureManageable(Order $order): void
    {
        $slug = StorefrontContext::adminSlug();
        abort_unless($slug === null || $order->demo_store === $slug, 404);
    }
}
