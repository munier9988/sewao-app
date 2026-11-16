@extends('layouts.storefront')

@section('title', 'Pesanan Saya')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-10 sm:py-12 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-700">RIWAYAT KLAIM & PEMBAYARAN SEWA</div>
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900">Pesanan & Unit Sewa Saya</h1>
        </div>
        <div class="flex items-center gap-2.5 self-start">
            <a href="{{ route('cart.index') }}"
               class="px-4 py-2.5 bg-white hover:bg-burgundy-50 text-burgundy-800 font-bold text-sm border border-burgundy-200 rounded-xl transition">
                🛒 Lihat Keranjang
            </a>
            <a href="{{ route('marketplace') }}"
               class="px-5 py-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm sm:text-base rounded-xl shadow-xs transition">
                + Cari Gadget Lain
            </a>
        </div>
    </div>

    @if ($orders->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-3 shadow-sm">
            <div class="text-4xl">📦</div>
            <h2 class="font-display text-2xl font-extrabold text-slate-900">Belum Ada Pesanan Sewa</h2>
            <p class="text-base text-slate-500">Pilih perangkat di halaman Marketplace lalu masukkan ke Keranjang (Transfer) atau klik <strong>Sewa via WhatsApp</strong>.</p>
            <a href="{{ route('marketplace') }}" class="inline-block px-6 py-3 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm sm:text-base rounded-xl">
                Buka Marketplace
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                @php
                    $waUrl = $order->whatsappUrl();
                    $statusStyle = match ($order->status) {
                        'disewa' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                        'selesai' => 'bg-slate-100 border-slate-200 text-slate-600',
                        default => 'bg-burgundy-50 border-burgundy-200 text-burgundy-800',
                    };
                    $payBadge = match ($order->payment_status) {
                        'lunas' => 'bg-emerald-100 border-emerald-300 text-emerald-800',
                        'menunggu_verifikasi' => 'bg-amber-50 border-amber-300 text-amber-800',
                        'ditolak' => 'bg-red-50 border-red-300 text-red-700',
                        default => 'bg-slate-100 border-slate-200 text-slate-600',
                    };
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm hover:border-burgundy-300 transition">
                    <div class="flex items-start sm:items-center gap-4 min-w-0">
                        <div class="w-28 h-22 shrink-0 rounded-xl border border-slate-200 overflow-hidden bg-burgundy-50/40">
                            @if ($order->product)
                                <x-product-image :product="$order->product" class="w-full h-20" />
                            @endif
                        </div>

                        <div class="min-w-0 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono-tech text-xs sm:text-sm font-bold px-3 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-slate-800">
                                    {{ $order->order_code }}
                                </span>
                                @if ($order->checkout_group)
                                    <span class="font-mono-tech text-xs font-semibold px-2.5 py-0.5 rounded-full bg-burgundy-50 border border-burgundy-200 text-burgundy-700">
                                        {{ $order->checkout_group }}
                                    </span>
                                @endif
                                <span class="font-mono-tech text-xs font-bold uppercase px-3 py-0.5 rounded-full border {{ $statusStyle }}">
                                    {{ $order->statusLabel() }}
                                </span>
                                <span class="font-mono-tech text-xs font-bold uppercase px-3 py-0.5 rounded-full border {{ $payBadge }}">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                            </div>

                            <h2 class="font-display text-xl font-extrabold text-slate-900 truncate">
                                {{ $order->product->name ?? 'Perangkat' }}
                            </h2>

                            <div class="text-sm text-slate-500 flex flex-wrap gap-x-5 gap-y-1">
                                <span>Metode: <strong class="text-slate-700">{{ $order->payment_method === 'transfer' ? 'Transfer Bank (Keranjang)' : 'WhatsApp Langsung' }}</strong></span>
                                <span class="font-mono-tech">Unit: <strong class="text-slate-700">{{ $order->quantity ?? 1 }} unit × {{ $order->duration_days }} hari</strong></span>
                                <span class="font-mono-tech text-burgundy-700 font-bold">
                                    Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </span>
                            </div>

                            @if ($order->payment_method === 'transfer')
                                <div class="text-xs text-slate-500 font-mono-tech">
                                    Rekening: {{ $order->bank_name ?: '-' }} · Pengirim: {{ $order->payment_note ?: '-' }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        @if ($waUrl)
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                               class="w-full md:w-auto text-center px-5 py-3 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm sm:text-base rounded-xl shadow-xs transition">
                                {{ $order->payment_method === 'transfer' ? 'Konfirmasi Transfer via WA ↗' : 'Chat Ulang WhatsApp ↗' }}
                            </a>
                        @else
                            <span class="px-4 py-2.5 text-sm font-semibold text-slate-500 bg-slate-100 border border-slate-200 rounded-xl">
                                WA Seller Belum Tersedia
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
