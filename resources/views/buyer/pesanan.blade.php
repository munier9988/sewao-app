@extends('layouts.storefront')

@section('title', 'Pesanan Saya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="font-mono-tech text-xs font-bold uppercase tracking-wider text-[#B45309]">RIWAYAT KLAIM SEWA</div>
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-[#1C1917]">Pesanan & Unit Sewa Saya</h1>
        </div>
        <a href="{{ route('jelajah') }}"
           class="px-4 py-2 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition self-start">
            + Cari Gadget Lain
        </a>
    </div>

    @if ($orders->isEmpty())
        <div class="bg-white border-[1.5px] border-[#1C1917] rounded-2xl p-10 text-center space-y-3">
            <div class="text-3xl">📦</div>
            <h2 class="font-display text-xl font-extrabold">Belum Ada Pesanan Sewa</h2>
            <p class="text-sm text-[#57534E]">Pilih perangkat di halaman Jelajah lalu klik <strong>Sewa via WhatsApp</strong>.</p>
            <a href="{{ route('jelajah') }}" class="inline-block px-4 py-2 bg-[#FACC15] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg">
                Jelajah Katalog
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                @php
                    $waUrl = $order->whatsappUrl();
                    $statusStyle = match ($order->status) {
                        'disewa' => 'bg-emerald-50 border-emerald-400 text-[#15803D]',
                        'selesai' => 'bg-stone-100 border-stone-300 text-[#57534E]',
                        default => 'bg-[#FEF9C3] border-[#1C1917] text-[#422006]',
                    };
                @endphp
                <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-[2px_2px_0px_#1C1917]">
                    <div class="flex items-start sm:items-center gap-4 min-w-0">
                        <div class="w-24 h-20 shrink-0 rounded-lg border-[1.5px] border-[#1C1917] overflow-hidden">
                            @if ($order->product)
                                <x-product-image :product="$order->product" class="w-full h-full" />
                            @endif
                        </div>

                        <div class="min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono-tech text-xs font-bold px-2 py-0.5 rounded bg-[#FFFDF5] border border-[#1C1917] text-[#B45309]">
                                    {{ $order->order_code }}
                                </span>
                                <span class="font-mono-tech text-[11px] font-bold uppercase px-2.5 py-0.5 rounded border {{ $statusStyle }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </div>

                            <h2 class="font-display text-lg font-extrabold text-[#1C1917] truncate">
                                {{ $order->product->name ?? 'Perangkat' }}
                            </h2>

                            <div class="text-xs text-[#57534E] flex flex-wrap gap-x-4 gap-y-1">
                                <span>Kategori: <strong>{{ $order->product->category->name ?? '—' }}</strong></span>
                                <span class="font-mono-tech">Durasi: <strong>{{ $order->duration_days }} hari</strong></span>
                                <span class="font-mono-tech text-[#B45309] font-bold">
                                    Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        @if ($waUrl)
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                               class="w-full md:w-auto text-center px-4 py-2.5 bg-[#15803D] hover:bg-[#166534] text-white font-bold text-xs sm:text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition">
                                Chat Ulang WhatsApp ↗
                            </a>
                        @else
                            <span class="px-3 py-2 text-xs font-semibold text-[#57534E] bg-stone-100 border border-stone-300 rounded-lg">
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
