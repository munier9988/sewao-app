<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'checkout_group',
        'buyer_id',
        'product_id',
        'quantity',
        'duration_days',
        'total_price',
        'payment_method',
        'payment_status',
        'bank_name',
        'payment_note',
        'payment_proof',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'duration_days' => 'integer',
            'total_price' => 'integer',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'menunggu_wa' => 'Menunggu Konfirmasi WA',
            'disewa' => 'Aktif Disewa',
            'selesai' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'lunas' => 'Lunas Terverifikasi',
            'menunggu_verifikasi' => 'Menunggu Verifikasi Transfer',
            'ditolak' => 'Pembayaran Ditolak',
            default => 'Belum Bayar',
        };
    }

    public function whatsappUrl(): ?string
    {
        $this->loadMissing(['product.seller', 'buyer']);

        $seller = $this->product?->seller;
        $waPhone = User::normalizeWaPhone($seller?->wa_phone);

        if (! $waPhone || ! $this->product) {
            return null;
        }

        $sellerName = $seller->name ?? 'Seller';
        $buyerName = $this->buyer?->name ?? 'Pembeli';
        $dailyPrice = number_format($this->product->price_per_day, 0, ',', '.');
        $totalPrice = number_format($this->total_price, 0, ',', '.');
        $qty = max(1, (int) ($this->quantity ?? 1));

        if ($this->payment_method === 'transfer') {
            $bankInfo = $this->bank_name ?: 'Rekening Sewao';
            $noteInfo = $this->payment_note ?: '-';
            $groupCode = $this->checkout_group ?: $this->order_code;

            $message = "Halo {$sellerName}, saya {$buyerName} ingin konfirmasi pembayaran TRANSFER sewa di Sewao:\n"
                ."- Kode Order / Invoice: {$this->order_code} ({$groupCode})\n"
                ."- Perangkat: {$this->product->name} ({$qty} unit x {$this->duration_days} hari)\n"
                ."- Total Transfer: Rp {$totalPrice}\n"
                ."- Bank Tujuan: {$bankInfo}\n"
                ."- Pengirim / Catatan: {$noteInfo}\n\n"
                .'Mohon verifikasi pembayaran saya. Terima kasih!';
        } else {
            $message = "Halo {$sellerName}, saya {$buyerName} ingin menyewa unit di Sewao:\n"
                ."- Perangkat: {$this->product->name}\n"
                ."- Tarif Sewa: Rp {$dailyPrice}/hari\n"
                ."- Durasi: {$this->duration_days} hari (Total Rp {$totalPrice})\n"
                ."- Kode Order: {$this->order_code}\n\n"
                .'Apakah unit tersedia untuk disewa?';
        }

        return 'https://wa.me/'.$waPhone.'?text='.rawurlencode($message);
    }
}
