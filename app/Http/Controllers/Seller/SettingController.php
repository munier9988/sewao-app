<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(Request $request): View
    {
        $seller = $request->user();

        $incomingOrders = Order::with(['buyer', 'product'])
            ->whereHas('product', fn ($q) => $q->where('seller_id', $seller->id))
            ->latest('id')
            ->take(10)
            ->get();

        return view('seller.settings', compact('seller', 'incomingOrders'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'wa_phone' => ['required', 'string', 'max:20'],
        ]);

        $normalized = User::normalizeWaPhone($request->input('wa_phone'));

        if ($normalized === null || ! preg_match('/^628\d{7,15}$/', $normalized)) {
            throw ValidationException::withMessages([
                'wa_phone' => 'Nomor WhatsApp harus valid dan diawali 08 atau 628 (contoh: 081234567890 atau 6281234567890).',
            ]);
        }

        $request->user()->update([
            'wa_phone' => $normalized,
        ]);

        return redirect()->route('seller.settings.edit')
            ->with('success', "Nomor WhatsApp toko berhasil diperbarui menjadi {$normalized}.");
    }
}
