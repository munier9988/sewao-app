<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public const BANK_ACCOUNTS = [
        'BCA — 8720192831 a.n. Toko Sewao Pusat',
        'Mandiri — 1370098123456 a.n. Toko Sewao Pusat',
        'BRI — 002901092831502 a.n. Toko Sewao Pusat',
    ];

    public function index(Request $request): View
    {
        $cartItems = CartItem::with(['product.category', 'product.seller'])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->get();

        $grandTotal = $cartItems->sum(fn (CartItem $item) => $item->subtotal());
        $bankAccounts = self::BANK_ACCOUNTS;

        return view('buyer.keranjang', compact('cartItems', 'grandTotal', 'bankAccounts'));
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock <= 0) {
            if ($request->expectsJson()) {
                return response()->json(['error' => "Stok {$product->name} sedang habis."], 422);
            }

            return back()->with('error', "Maaf, stok {$product->name} sedang habis.");
        }

        $userId = $request->user()->id;
        $qty = min((int) ($validated['quantity'] ?? 1), (int) $product->stock);
        $days = (int) ($validated['duration_days'] ?? 1);

        $existing = CartItem::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $newQty = min((int) $product->stock, $existing->quantity + $qty);
            $existing->update([
                'quantity' => $newQty,
                'duration_days' => $days,
            ]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $product->id,
                'quantity' => $qty,
                'duration_days' => $days,
            ]);
        }

        $allItems = CartItem::with('product')->where('user_id', $userId)->get();
        $cartCount = $allItems->sum('quantity');
        $cartTotal = $allItems->sum(fn (CartItem $item) => $item->subtotal());
        $message = "{$product->name} ditambahkan ke Keranjang Belanja.";

        if ($request->expectsJson()) {
            return response()->json([
                'count' => (int) $cartCount,
                'total' => (int) $cartTotal,
                'total_formatted' => number_format($cartTotal, 0, ',', '.'),
                'product_name' => $product->name,
                'message' => $message,
            ]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);

        $maxStock = max(1, (int) ($cartItem->product?->stock ?? 1));

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', "max:{$maxStock}"],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $cartItem->update($validated);

        return redirect()->route('cart.index')
            ->with('success', 'Jumlah dan durasi sewa di keranjang berhasil diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);

        $cartItem->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Perangkat dihapus dari keranjang belanja.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:80'],
            'payment_note' => ['required', 'string', 'max:150'],
            'payment_proof_file' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();
        $cartItems = CartItem::with(['product.seller'])
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        foreach ($cartItems as $item) {
            if (! $item->product || $item->product->stock < $item->quantity) {
                return redirect()->route('cart.index')
                    ->with('error', "Stok {$item->product?->name} tidak mencukupi untuk jumlah yang dipilih.");
            }
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof_file')) {
            $file = $request->file('payment_proof_file');
            $dir = public_path('uploads/payments');
            File::ensureDirectoryExists($dir);
            $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $proofPath = '/uploads/payments/'.$filename;
        }

        $checkoutGroup = 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        $grandTotal = 0;
        $lines = [];
        $sellerPhone = null;
        $sellerName = 'Toko Sewao Pusat';

        foreach ($cartItems as $idx => $item) {
            $subtotal = $item->subtotal();
            $grandTotal += $subtotal;

            $order = Order::create([
                'order_code' => $this->generateOrderCode(),
                'checkout_group' => $checkoutGroup,
                'buyer_id' => $user->id,
                'product_id' => $item->product->id,
                'quantity' => $item->quantity,
                'duration_days' => $item->duration_days,
                'total_price' => $subtotal,
                'payment_method' => 'transfer',
                'payment_status' => 'menunggu_verifikasi',
                'bank_name' => $validated['bank_name'],
                'payment_note' => $validated['payment_note'],
                'payment_proof' => $proofPath,
                'status' => 'menunggu_wa',
            ]);

            if (! $sellerPhone && $item->product->seller?->wa_phone) {
                $sellerPhone = User::normalizeWaPhone($item->product->seller->wa_phone);
                $sellerName = $item->product->seller->name ?? $sellerName;
            }

            $num = $idx + 1;
            $subFormatted = number_format($subtotal, 0, ',', '.');
            $lines[] = "{$num}. {$item->product->name} ({$item->quantity} unit x {$item->duration_days} hr) = Rp {$subFormatted} [{$order->order_code}]";
        }

        CartItem::where('user_id', $user->id)->delete();

        $totalFormatted = number_format($grandTotal, 0, ',', '.');
        $waPhone = $sellerPhone ?: '6285216114321';

        $message = "Halo {$sellerName}, saya {$user->name} ingin konfirmasi PEMBAYARAN TRANSFER Keranjang Sewao:\n"
            ."- Kode Invoice: {$checkoutGroup}\n"
            ."- Daftar Perangkat:\n  ".implode("\n  ", $lines)."\n"
            ."- Total Transfer: Rp {$totalFormatted}\n"
            ."- Rekening Tujuan: {$validated['bank_name']}\n"
            ."- Pengirim / Catatan: {$validated['payment_note']}\n\n"
            .'Mohon verifikasi pembayaran saya. Terima kasih!';

        $waUrl = 'https://wa.me/'.$waPhone.'?text='.rawurlencode($message);

        if ($request->boolean('redirect_wa', true)) {
            return redirect()->away($waUrl);
        }

        return redirect()->route('pesanan')
            ->with('success', "Checkout Transfer {$checkoutGroup} (Total Rp {$totalFormatted}) berhasil tercatat! Silakan konfirmasi via WhatsApp.");
    }

    private function generateOrderCode(): string
    {
        $date = now()->format('Ymd');
        $nextSeq = (int) (Order::max('id') ?? 0) + 1;

        do {
            $code = sprintf('SW-%s-%03d', $date, $nextSeq);
            if (Order::where('order_code', $code)->exists()) {
                $code = sprintf('SW-%s-%s', $date, strtoupper(Str::random(4)));
            }
            $nextSeq++;
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
