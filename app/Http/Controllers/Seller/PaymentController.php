<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $sellerId = $request->user()->id;
        $statusFilter = (string) $request->query('payment_status', '');
        $methodFilter = (string) $request->query('payment_method', '');
        $search = trim((string) $request->query('q', ''));

        $baseQuery = Order::with(['buyer', 'product.category'])
            ->whereHas('product', fn ($q) => $q->where('seller_id', $sellerId));

        $orders = (clone $baseQuery)
            ->when(in_array($statusFilter, ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'], true), fn ($q) => $q->where('payment_status', $statusFilter))
            ->when(in_array($methodFilter, ['transfer', 'wa_langsung'], true), fn ($q) => $q->where('payment_method', $methodFilter))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('order_code', 'like', "%{$search}%")
                        ->orWhere('checkout_group', 'like', "%{$search}%")
                        ->orWhere('payment_note', 'like', "%{$search}%")
                        ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->get();

        $stats = [
            'revenue_lunas' => (int) (clone $baseQuery)->where('payment_status', 'lunas')->sum('total_price'),
            'pending_verification' => (clone $baseQuery)->where('payment_status', 'menunggu_verifikasi')->count(),
            'lunas_count' => (clone $baseQuery)->where('payment_status', 'lunas')->count(),
            'unpaid_count' => (clone $baseQuery)->whereIn('payment_status', ['belum_bayar', 'ditolak'])->count(),
        ];

        return view('seller.payments.index', compact('orders', 'stats', 'statusFilter', 'methodFilter', 'search'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $order->loadMissing('product');
        abort_unless($order->product && $order->product->seller_id === $request->user()->id, 403, 'Anda tidak berhak mengelola pembayaran pesanan ini.');

        $validated = $request->validate([
            'payment_status' => ['required', Rule::in(['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'])],
            'status' => ['required', Rule::in(['menunggu_wa', 'disewa', 'selesai'])],
        ]);

        $order->update($validated);

        return redirect()->route('seller.payments.index')
            ->with('success', "Status pembayaran & sewa untuk pesanan {$order->order_code} berhasil diperbarui.");
    }
}
