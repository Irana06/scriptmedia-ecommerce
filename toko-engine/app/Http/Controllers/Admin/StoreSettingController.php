<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    public function edit(): View
    {
        $storeSetting = StoreSetting::query()->firstOrCreate([], ['store_name' => 'Toko Senja']);
        $storeSetting->load('media');

        return view('admin.store-settings.edit', compact('storeSetting'));
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
}
