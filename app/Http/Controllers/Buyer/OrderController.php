<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['product.category', 'product.seller'])
            ->where('buyer_id', $request->user()->id)
            ->latest('id')
            ->get();

        return view('buyer.pesanan', compact('orders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $product = Product::with('seller')->findOrFail($validated['product_id']);

        if ($product->stock <= 0) {
            return back()->with('error', "Maaf, stok {$product->name} sedang habis.");
        }

        $durationDays = (int) ($validated['duration_days'] ?? 1);
        $totalPrice = $product->price_per_day * $durationDays;

        $order = Order::create([
            'order_code' => $this->generateOrderCode(),
            'buyer_id' => $request->user()->id,
            'product_id' => $product->id,
            'duration_days' => $durationDays,
            'total_price' => $totalPrice,
            'status' => 'menunggu_wa',
        ]);

        $waUrl = $order->whatsappUrl();

        if ($waUrl) {
            return redirect()->away($waUrl);
        }

        return redirect()->route('pesanan')
            ->with('success', "Pesanan {$order->order_code} tercatat! Namun nomor WhatsApp Seller belum diatur.");
    }

    private function generateOrderCode(): string
    {
        $date = now()->format('Ymd');
        $nextSeq = (int) (Order::max('id') ?? 0) + 1;

        do {
            $code = sprintf('SW-%s-%03d', $date, $nextSeq);
            $exists = Order::where('order_code', $code)->exists();
            if ($exists) {
                $code = sprintf('SW-%s-%s', $date, strtoupper(Str::random(4)));
            }
            $nextSeq++;
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
