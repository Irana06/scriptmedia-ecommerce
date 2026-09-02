<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\RentalOrder;
use App\Models\User;
use App\Services\MidtransRentalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RentalOrderController extends Controller
{
    public function create(Plan $plan): View
    {
        abort_unless($plan->is_active, Response::HTTP_NOT_FOUND);

        return view('onboarding.create', compact('plan'));
    }

    public function store(Request $request, Plan $plan, MidtransRentalService $midtrans): RedirectResponse
    {
        abort_unless($plan->is_active, Response::HTTP_NOT_FOUND);

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'desired_subdomain' => ['required', 'string', 'min:3', 'max:63', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('rental_orders'), Rule::unique('tenants', 'subdomain')],
            'custom_domain' => ['nullable', 'string', 'max:253', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', Rule::unique('rental_orders'), Rule::unique('tenants')],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s-]{7,28}$/'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (filled($validated['custom_domain'] ?? null) && ! $plan->custom_domain_allowed) {
            return back()->withErrors(['custom_domain' => 'Custom domain tersedia mulai plan Standard.'])->withInput();
        }

        $user = $request->user();
        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $order = RentalOrder::create([
            'number' => 'RENT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $validated['billing_cycle'],
            'business_name' => $validated['business_name'],
            'desired_subdomain' => strtolower($validated['desired_subdomain']),
            'custom_domain' => filled($validated['custom_domain'] ?? null) ? strtolower($validated['custom_domain']) : null,
            'whatsapp' => preg_replace('/[^0-9+]/', '', $validated['whatsapp']),
            'notes' => $validated['notes'] ?? null,
            'status' => 'awaiting_payment',
            'amount' => $validated['billing_cycle'] === 'annual' ? $plan->annualTotal() : $plan->monthlyTotal(),
            'payment_gateway' => 'midtrans',
        ]);

        if ($midtrans->isConfigured()) {
            try {
                $midtrans->createSnapTransaction($order);
            } catch (Throwable $exception) {
                Log::warning('Gagal membuat transaksi Midtrans untuk rental order.', [
                    'rental_order_id' => $order->id,
                    'exception' => $exception,
                ]);
            }
        }

        return redirect()->route('portal.orders.show', $order);
    }

    public function show(Request $request, RentalOrder $rentalOrder): View
    {
        abort_unless($request->user()?->getKey() === $rentalOrder->user_id, Response::HTTP_FORBIDDEN);
        $rentalOrder->load(['plan', 'tenant']);

        if ($rentalOrder->status === 'ready' && filled($rentalOrder->engine_temporary_password) && $rentalOrder->credentials_viewed_at === null) {
            $rentalOrder->update(['credentials_viewed_at' => now()]);
        }

        return view('portal.orders.show', ['order' => $rentalOrder]);
    }

    public function retry(Request $request, RentalOrder $rentalOrder, MidtransRentalService $midtrans): RedirectResponse
    {
        abort_unless($request->user()?->getKey() === $rentalOrder->user_id, Response::HTTP_FORBIDDEN);
        abort_unless($rentalOrder->status === 'awaiting_payment', Response::HTTP_UNPROCESSABLE_ENTITY);

        $midtrans->createSnapTransaction($rentalOrder);

        return redirect()->route('portal.orders.show', $rentalOrder);
    }
}
