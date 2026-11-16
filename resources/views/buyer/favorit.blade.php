@extends('layouts.storefront')

@section('title', 'Produk Favorit')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-10 sm:py-12 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-700">WISHLIST PENYEWA</div>
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900">Perangkat Favorit Saya</h1>
        </div>
        <a href="{{ route('marketplace') }}"
           class="px-5 py-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm sm:text-base rounded-xl shadow-xs transition self-start">
            Buka Marketplace
        </a>
    </div>

    @if ($favorites->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-3 shadow-sm">
            <div class="text-4xl">♡</div>
            <h2 class="font-display text-2xl font-extrabold text-slate-900">Belum Ada Produk Favorit</h2>
            <p class="text-base text-slate-500">Tekan ikon hati pada kartu produk di halaman Marketplace untuk menyimpannya di sini.</p>
            <a href="{{ route('marketplace') }}" class="inline-block px-6 py-3 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm sm:text-base rounded-xl">
                Temukan Gadget
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($favorites as $fav)
                @if ($fav->product)
                    <x-product-card
                        :product="$fav->product"
                        :is-favorited="true"
                        :remove-on-unfavorite="true"
                    />
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
