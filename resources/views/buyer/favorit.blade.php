@extends('layouts.storefront')

@section('title', 'Produk Favorit')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="font-mono-tech text-xs font-bold uppercase tracking-wider text-[#B45309]">WISHLIST PENYEWA</div>
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-[#1C1917]">Perangkat Favorit Saya</h1>
        </div>
        <a href="{{ route('jelajah') }}"
           class="px-4 py-2 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition self-start">
            Jelajah Katalog
        </a>
    </div>

    @if ($favorites->isEmpty())
        <div class="bg-white border-[1.5px] border-[#1C1917] rounded-2xl p-10 text-center space-y-3">
            <div class="text-3xl">♡</div>
            <h2 class="font-display text-xl font-extrabold">Belum Ada Produk Favorit</h2>
            <p class="text-sm text-[#57534E]">Tekan ikon hati pada kartu produk di halaman Jelajah untuk menyimpannya di sini.</p>
            <a href="{{ route('jelajah') }}" class="inline-block px-4 py-2 bg-[#FACC15] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg">
                Temukan Gadget
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
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
