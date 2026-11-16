@extends('layouts.storefront')

@section('title', 'Keranjang Belanja & Pembayaran Transfer — Sewao')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-10 sm:py-12 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-700">CHECKOUT & PEMBAYARAN TRANSFER</div>
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900">Keranjang Belanja Saya</h1>
            <p class="text-base text-slate-600 mt-1">
                Kumpulkan seluruh perangkat yang ingin disewa untuk dihitung harga totalnya, bayar melalui Transfer Bank, lalu konfirmasi lewat WhatsApp.
            </p>
        </div>
        <a href="{{ route('marketplace') }}"
           class="px-5 py-2.5 bg-white hover:bg-burgundy-50 text-burgundy-800 font-bold text-sm sm:text-base border border-burgundy-200 rounded-xl shadow-xs transition self-start">
            + Tambah Unit Lain
        </a>
    </div>

    @if ($cartItems->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-4 shadow-sm">
            <div class="text-5xl">🛒</div>
            <h2 class="font-display text-2xl font-extrabold text-slate-900">Keranjang Belanja Masih Kosong</h2>
            <p class="text-base text-slate-500 max-w-md mx-auto">
                Pilih perangkat elektronik di Marketplace lalu tekan tombol <strong>🛒 +Keranjang</strong> untuk menggabungkan sewa dan membayar via Transfer.
            </p>
            <a href="{{ route('marketplace') }}" class="inline-block px-6 py-3 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-base rounded-xl">
                Buka Marketplace Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Kolom Kiri: Daftar Item di Keranjang --}}
            <div class="lg:col-span-7 space-y-4">
                @foreach ($cartItems as $item)
                    @php
                        $product = $item->product;
                        $subtotal = $item->subtotal();
                    @endphp
                    @if ($product)
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start sm:items-center gap-4 min-w-0">
                                <div class="w-24 h-20 shrink-0 rounded-xl border border-slate-200 overflow-hidden bg-burgundy-50/40">
                                    <x-product-image :product="$product" class="w-full h-full" />
                                </div>
                                <div class="min-w-0 space-y-1">
                                    <span class="font-mono-tech text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-burgundy-50 text-burgundy-700 border border-burgundy-200">
                                        {{ $product->category->name ?? 'Elektronik' }}
                                    </span>
                                    <h2 class="font-display text-xl font-extrabold text-slate-900 truncate">{{ $product->name }}</h2>
                                    <div class="font-mono-tech text-sm text-slate-600">
                                        Tarif: <strong class="text-burgundy-700">Rp {{ number_format($product->price_per_day, 0, ',', '.') }}/hari</strong>
                                        · Stok maks: {{ $product->stock }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:items-end gap-2.5 shrink-0">
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Unit</label>
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ max(1, $product->stock) }}"
                                               class="w-16 px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono-tech text-sm font-bold text-center">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-slate-500">Hari</label>
                                        <input type="number" name="duration_days" value="{{ $item->duration_days }}" min="1" max="30"
                                               class="w-16 px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono-tech text-sm font-bold text-center">
                                    </div>
                                    <button type="submit" class="mt-4 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg cursor-pointer">
                                        Hitung
                                    </button>
                                </form>

                                <div class="flex items-center justify-between sm:justify-end gap-3 w-full">
                                    <span class="font-mono-tech text-base font-extrabold text-burgundy-700">
                                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                                    </span>

                                    <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Kolom Kanan: Total & Mekanisme Pembayaran Transfer + Konfirmasi WA --}}
            <div class="lg:col-span-5 bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-md space-y-6 sticky top-24">
                <div class="border-b border-slate-200 pb-4">
                    <span class="font-mono-tech text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-burgundy-50 text-burgundy-700 border border-burgundy-200">
                        PEMBAYARAN TRANSFER BANK
                    </span>
                    <h2 class="font-display text-2xl font-extrabold text-slate-900 mt-2">
                        Ringkasan & Transfer Sewa
                    </h2>
                </div>

                {{-- Rincian Harga Total --}}
                <div class="space-y-2 text-sm">
                    @foreach ($cartItems as $item)
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="truncate pr-2">{{ $item->product?->name }} ({{ $item->quantity }}x · {{ $item->duration_days }} hr)</span>
                            <span class="font-mono-tech font-semibold text-slate-900 shrink-0">Rp {{ number_format($item->subtotal(), 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                        <span class="font-bold text-base text-slate-900">Total Pembayaran</span>
                        <span class="font-mono-tech text-2xl font-extrabold text-burgundy-700">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Info Rekening Tujuan --}}
                <div class="p-4 rounded-2xl bg-burgundy-50/70 border border-burgundy-200 space-y-2">
                    <div class="text-xs font-bold uppercase tracking-wider text-burgundy-800">Daftar Rekening Resmi Sewao</div>
                    <ul class="space-y-1.5 font-mono-tech text-xs sm:text-sm text-slate-800">
                        @foreach ($bankAccounts as $acc)
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-burgundy-700"></span>
                                <span>{{ $acc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Form Checkout Transfer & Konfirmasi WA --}}
                <form method="POST" action="{{ route('cart.checkout') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="bank_name" class="block text-sm font-semibold mb-1 text-slate-700">Pilih Rekening Tujuan Transfer</label>
                        <select id="bank_name" name="bank_name" required
                                class="w-full px-3.5 py-2.5 border border-slate-300 focus:border-burgundy-700 rounded-xl text-sm bg-white outline-none">
                            @foreach ($bankAccounts as $acc)
                                <option value="{{ $acc }}">{{ $acc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="payment_note" class="block text-sm font-semibold mb-1 text-slate-700">Nama Rekening Pengirim / Nomor Referensi</label>
                        <input id="payment_note" type="text" name="payment_note" required maxlength="150"
                               placeholder="Contoh: Transfer BCA a.n. Budi Penyewa"
                               class="w-full px-3.5 py-2.5 border border-slate-300 focus:border-burgundy-700 rounded-xl text-sm outline-none">
                    </div>

                    <div>
                        <label for="payment_proof_file" class="block text-sm font-semibold mb-1 text-slate-700">
                            Upload Bukti Transfer <span class="font-normal text-slate-500">(Opsional — JPG/PNG maks 2MB)</span>
                        </label>
                        <input id="payment_proof_file" type="file" name="payment_proof_file" accept="image/*"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-slate-50">
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-base rounded-xl shadow-sm transition cursor-pointer">
                        Bayar Transfer & Konfirmasi via WhatsApp ↗
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
