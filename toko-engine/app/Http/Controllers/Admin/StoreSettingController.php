<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\StoreSetting;
use App\Services\MidtransService;
use App\Services\StoreLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    public function __construct(
        private readonly StoreLimitService $storeLimits,
        private readonly MidtransService $midtrans,
    ) {}

    public function edit(): View
    {
        $storeSetting = StoreSetting::query()->firstOrCreate([], ['store_name' => 'Toko Senja']);
        $storeSetting->load('media');

        return view('admin.store-settings.edit', [
            'storeSetting' => $storeSetting,
            'gateways' => PaymentGateway::query()->orderBy('name')->get(),
            'gatewayLimit' => $this->storeLimits->gatewayLimit(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        $storeSetting = StoreSetting::query()->firstOrCreate([], ['store_name' => 'Toko Senja']);
        $storeSetting->update($validated);

        foreach (['logo', 'banner'] as $collection) {
            if ($request->hasFile($collection)) {
                $storeSetting->addMediaFromRequest($collection)->toMediaCollection($collection);
            }
        }

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }

    public function updateGateway(Request $request, PaymentGateway $paymentGateway): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $shouldActivate = (bool) $validated['is_active'];

        if ($shouldActivate && $paymentGateway->code === MidtransService::GATEWAY_CODE && ! $this->midtrans->isConfigured()) {
            return back()->withErrors([
                'gateway_limit' => 'Kredensial Midtrans belum lengkap di environment toko.',
            ]);
        }

        if ($shouldActivate && ! $paymentGateway->is_active && ! $this->storeLimits->canUseGateway()) {
            $limit = $this->storeLimits->gatewayLimit();

            return back()->withErrors([
                'gateway_limit' => "Batas paket tercapai ({$limit} payment gateway aktif). Nonaktifkan gateway lain atau tingkatkan paket.",
            ]);
        }

        $paymentGateway->update(['is_active' => $shouldActivate]);

        return back()->with('success', 'Status payment gateway berhasil diperbarui.');
    }
}
