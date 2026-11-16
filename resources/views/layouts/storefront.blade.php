<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sewao — Rental Elektronik Terpercaya')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        burgundy: {
                            50: '#FDF2F4',
                            100: '#FCE7EB',
                            200: '#F8CCD5',
                            300: '#F2A3B5',
                            400: '#E36B88',
                            500: '#B91C47',
                            600: '#8E1034',
                            700: '#720926',
                            800: '#5A071E',
                            900: '#420516',
                            950: '#2A020D',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FFFFFF;
            color: #0F172A;
            font-size: 16px;
        }
        h1, h2, h3, .font-display {
            font-family: 'Bricolage Grotesque', sans-serif;
            letter-spacing: -0.02em;
        }
        .font-mono-tech {
            font-family: 'JetBrains Mono', monospace;
        }
        @keyframes sewaoWave {
            0%, 100% { transform: translateY(0px) scale(1) rotate(0deg); }
            35% { transform: translateY(-14px) scale(1.12) rotate(-3deg); }
            70% { transform: translateY(4px) scale(0.97) rotate(2deg); }
        }
        @keyframes sewaoBar {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        @keyframes sewaoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .sewao-char {
            display: inline-block;
            animation: sewaoWave 0.9s cubic-bezier(0.34, 1.56, 0.64, 1) infinite;
        }
        .sewao-progress {
            animation: sewaoBar 1.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .sewao-float {
            animation: sewaoFloat 2.4s ease-in-out infinite;
        }
    </style>
</head>
@php
    $buyerOrderCount = auth()->check() && auth()->user()->isPembeli()
        ? auth()->user()->orders()->count()
        : 0;
    $buyerFavoriteCount = auth()->check() && auth()->user()->isPembeli()
        ? auth()->user()->favorites()->count()
        : 0;
    $buyerCartCount = auth()->check() && auth()->user()->isPembeli()
        ? (int) auth()->user()->cartItems()->sum('quantity')
        : 0;
    $buyerCartTotal = auth()->check() && auth()->user()->isPembeli()
        ? auth()->user()->cartItems()->with('product')->get()->sum(fn ($item) => $item->subtotal())
        : 0;
@endphp
<body
    class="min-h-screen flex flex-col bg-white text-slate-900 antialiased text-base"
    x-data="{
        showIntro: {{ request()->routeIs('jelajah') ? 'true' : 'false' }},
        favCount: {{ $buyerFavoriteCount }},
        cartCount: {{ $buyerCartCount }},
        cartTotalFormatted: '{{ number_format($buyerCartTotal, 0, ',', '.') }}',
        cartPop: false,
        lastAddedName: '',
        detailOpen: false,
        detailProduct: null,
        detailDuration: 1,
        detailQty: 1,
        activeNav: (() => {
            @if (request()->routeIs('marketplace'))
                return 'marketplace';
            @elseif (request()->routeIs('cart.*'))
                return 'keranjang';
            @elseif (request()->routeIs('pesanan'))
                return 'pesanan';
            @elseif (request()->routeIs('favorit'))
                return 'favorit';
            @else
                const h = window.location.hash;
                if (h === '#pemesanan') return 'pemesanan';
                if (h === '#ulasan') return 'ulasan';
                return 'beranda';
            @endif
        })(),
        init() {
            if (this.showIntro) {
                setTimeout(() => { this.showIntro = false; }, 1650);
            }
        },
        syncHash() {
            if (window.location.pathname === '/jelajah' || window.location.pathname === '/') {
                const h = window.location.hash;
                this.activeNav = h === '#pemesanan' ? 'pemesanan' : (h === '#ulasan' ? 'ulasan' : 'beranda');
            }
        },
        handleCartUpdate(detail) {
            this.cartCount = detail.count ?? this.cartCount;
            if (detail.total_formatted) {
                this.cartTotalFormatted = detail.total_formatted;
            }
            this.lastAddedName = detail.product_name ?? 'Perangkat';
            this.cartPop = true;
            setTimeout(() => { this.cartPop = false; }, 1400);
        },
        async addModalToCart() {
            if (!this.detailProduct) return;
            const res = await fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: this.detailProduct.id,
                    quantity: this.detailQty,
                    duration_days: this.detailDuration,
                }),
            });
            if (res.status === 401) {
                window.location.href = '{{ route('login') }}';
                return;
            }
            if (res.ok) {
                const data = await res.json();
                this.detailOpen = false;
                this.handleCartUpdate(data);
            }
        }
    }"
    @favorite-updated.window="favCount = $event.detail.count"
    @cart-updated.window="handleCartUpdate($event.detail)"
    @open-product-detail.window="detailProduct = $event.detail; detailDuration = 1; detailQty = 1; detailOpen = true"
    @nav-select.window="activeNav = $event.detail"
    @hashchange.window="syncHash()"
>
    {{-- Animasi Gaul "Sewaooooo" Saat Website Diakses --}}
    <div
        x-show="showIntro"
        x-transition:leave="transition ease-in-out duration-500"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-105 -translate-y-4"
        @click="showIntro = false"
        class="fixed inset-0 z-50 bg-gradient-to-br from-burgundy-950 via-burgundy-800 to-slate-950 text-white flex flex-col items-center justify-center p-6 select-none cursor-pointer overflow-hidden"
    >
        <div class="flex flex-wrap items-center justify-center gap-2 mb-5">
            <span class="sewao-float px-3.5 py-1 rounded-full bg-white/10 border border-white/20 font-mono-tech text-xs font-bold tracking-wider text-amber-300">
                ⚡ RENTAL GADGET SAT-SET
            </span>
            <span class="sewao-float px-3.5 py-1 rounded-full bg-burgundy-600/50 border border-burgundy-300/30 font-mono-tech text-xs font-bold text-white" style="animation-delay: 0.2s">
                📱 💻 📸 READY
            </span>
        </div>

        <div class="font-display text-5xl sm:text-7xl lg:text-8xl font-extrabold tracking-tight text-center leading-none">
            @foreach (str_split('Sewaooooo!') as $idx => $char)
                <span
                    class="sewao-char {{ $idx >= 4 ? 'text-amber-300' : 'text-white' }}"
                    style="animation-delay: {{ $idx * 0.06 }}s"
                >{{ $char }}</span>
            @endforeach
        </div>

        <p class="mt-4 text-sm sm:text-lg text-burgundy-100 font-medium tracking-wide text-center">
            Butuh Gadget Dadakan? Gas Sewa Sekarang! 🚀
        </p>

        <div class="mt-7 w-56 sm:w-64 h-1.5 rounded-full bg-white/15 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-amber-300 via-white to-burgundy-300 sewao-progress rounded-full"></div>
        </div>
        <span class="mt-3 font-mono-tech text-[11px] text-burgundy-200/75">Klik di mana saja untuk lewati</span>
    </div>

    {{-- Top Sticky Navbar: Lebar Penuh, Font Besar, Tema Burgundy --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 h-20 flex items-center justify-between gap-4">
            {{-- Brand Logo --}}
            <a href="{{ route('jelajah') }}#beranda" @click="activeNav = 'beranda'" class="flex items-center gap-3 shrink-0 group">
                <span class="w-10 h-10 rounded-xl bg-burgundy-700 text-white flex items-center justify-center text-xl shadow-sm group-hover:rotate-6 transition">📸</span>
                <div>
                    <span class="font-display text-2xl font-extrabold tracking-tight text-slate-900">Sewao</span>
                    <span class="hidden sm:inline-block ml-1 font-mono-tech text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-burgundy-50 text-burgundy-700 border border-burgundy-200">Sewaooooo!</span>
                </div>
            </a>

            {{-- Navigasi Utama --}}
            @guest
                {{-- Saat belum ada yang login: tepat Beranda - Pemesanan - Marketplace - Ulasan --}}
                <nav aria-label="Menu Utama" class="hidden md:flex items-center gap-2 lg:gap-4">
                    <a href="{{ route('jelajah') }}#beranda"
                       @click="activeNav = 'beranda'"
                       :class="activeNav === 'beranda' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                       class="px-4 py-2 rounded-full text-base font-semibold transition">
                        Beranda
                    </a>
                    <a href="{{ route('jelajah') }}#pemesanan"
                       @click="activeNav = 'pemesanan'"
                       :class="activeNav === 'pemesanan' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                       class="px-4 py-2 rounded-full text-base font-semibold transition">
                        Pemesanan
                    </a>
                    <a href="{{ route('marketplace') }}"
                       @click="activeNav = 'marketplace'"
                       :class="activeNav === 'marketplace' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                       class="px-4 py-2 rounded-full text-base font-semibold transition">
                        Marketplace
                    </a>
                    <a href="{{ route('jelajah') }}#ulasan"
                       @click="activeNav = 'ulasan'"
                       :class="activeNav === 'ulasan' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                       class="px-4 py-2 rounded-full text-base font-semibold transition">
                        Ulasan
                    </a>
                </nav>
            @else
                {{-- Saat sudah login (Admin, Seller, Pembeli): tanpa menu Beranda & Ulasan --}}
                <nav aria-label="Menu Utama" class="hidden md:flex items-center gap-1.5 lg:gap-2.5">
                    <a href="{{ route('marketplace') }}"
                       @click="activeNav = 'marketplace'"
                       :class="activeNav === 'marketplace' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                       class="px-3.5 py-2 rounded-full text-base font-semibold transition">
                        Marketplace
                    </a>
                    @if (auth()->user()->isPembeli())
                        <a href="{{ route('cart.index') }}"
                           :class="activeNav === 'keranjang' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                           class="px-3.5 py-2 rounded-full text-base font-semibold transition inline-flex items-center gap-1.5">
                            <span>🛒 Keranjang</span>
                            <span x-show="cartCount > 0" x-text="cartCount" class="font-mono-tech text-xs px-2 py-0.5 rounded-full bg-burgundy-700 text-white font-bold"></span>
                        </a>
                        <a href="{{ route('pesanan') }}"
                           :class="activeNav === 'pesanan' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                           class="px-3.5 py-2 rounded-full text-base font-semibold transition inline-flex items-center gap-1.5">
                            <span>Pesanan</span>
                            @if ($buyerOrderCount > 0)
                                <span class="font-mono-tech text-xs px-2 py-0.5 rounded-full bg-burgundy-700 text-white font-bold">{{ $buyerOrderCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('favorit') }}"
                           :class="activeNav === 'favorit' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                           class="px-3.5 py-2 rounded-full text-base font-semibold transition inline-flex items-center gap-1.5">
                            <span>Favorit</span>
                            <span x-show="favCount > 0" x-text="favCount" class="font-mono-tech text-xs px-2 py-0.5 rounded-full bg-burgundy-700 text-white font-bold"></span>
                        </a>
                    @else
                        <a href="{{ route('jelajah') }}#pemesanan"
                           @click="activeNav = 'pemesanan'"
                           :class="activeNav === 'pemesanan' ? 'bg-burgundy-50 text-burgundy-700 border border-burgundy-200' : 'text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 border border-transparent'"
                           class="px-3.5 py-2 rounded-full text-base font-semibold transition">
                            Pemesanan
                        </a>
                    @endif
                </nav>
            @endguest

            {{-- Account Controls --}}
            <div class="flex items-center gap-2.5 shrink-0">
                @auth
                    @if (! auth()->user()->isPembeli())
                        <a href="{{ auth()->user()->dashboardRoute() }}"
                           class="inline-flex px-4 py-2 rounded-full bg-burgundy-50 border border-burgundy-200 text-sm font-bold text-burgundy-800 hover:bg-burgundy-100 transition">
                            Panel {{ ucfirst(auth()->user()->role) }}
                        </a>
                    @else
                        <span class="hidden xl:inline-flex px-3.5 py-2 rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                            {{ auth()->user()->name }}
                        </span>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 rounded-full bg-white hover:bg-red-50 text-sm font-semibold text-slate-700 hover:text-red-700 border border-slate-300 hover:border-red-300 transition cursor-pointer">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 rounded-full text-sm sm:text-base font-semibold text-slate-700 hover:text-burgundy-700 hover:bg-burgundy-50 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-5 py-2.5 rounded-full bg-burgundy-700 hover:bg-burgundy-800 text-sm sm:text-base font-bold text-white shadow-sm transition">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>

        {{-- Mobile Quick Nav --}}
        <div class="md:hidden border-t border-slate-100 px-4 py-2 flex items-center justify-around gap-1 overflow-x-auto text-sm font-semibold">
            @guest
                <a href="{{ route('jelajah') }}#beranda" @click="activeNav = 'beranda'" :class="activeNav === 'beranda' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700 hover:text-burgundy-700'" class="px-3 py-1.5 rounded-full">Beranda</a>
                <a href="{{ route('jelajah') }}#pemesanan" @click="activeNav = 'pemesanan'" :class="activeNav === 'pemesanan' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700 hover:text-burgundy-700'" class="px-3 py-1.5 rounded-full">Pemesanan</a>
                <a href="{{ route('marketplace') }}" @click="activeNav = 'marketplace'" :class="activeNav === 'marketplace' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700 hover:text-burgundy-700'" class="px-3 py-1.5 rounded-full">Marketplace</a>
                <a href="{{ route('jelajah') }}#ulasan" @click="activeNav = 'ulasan'" :class="activeNav === 'ulasan' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700 hover:text-burgundy-700'" class="px-3 py-1.5 rounded-full">Ulasan</a>
            @else
                <a href="{{ route('marketplace') }}" @click="activeNav = 'marketplace'" :class="activeNav === 'marketplace' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700'" class="px-3 py-1.5 rounded-full">Marketplace</a>
                @if (auth()->user()->isPembeli())
                    <a href="{{ route('cart.index') }}" :class="activeNav === 'keranjang' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700'" class="px-3 py-1.5 rounded-full">Keranjang</a>
                    <a href="{{ route('pesanan') }}" :class="activeNav === 'pesanan' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700'" class="px-3 py-1.5 rounded-full">Pesanan</a>
                    <a href="{{ route('favorit') }}" :class="activeNav === 'favorit' ? 'bg-burgundy-50 text-burgundy-700' : 'text-slate-700'" class="px-3 py-1.5 rounded-full">Favorit</a>
                @endif
            @endguest
        </div>
    </header>

    {{-- Flash Alerts --}}
    @if (session('success') || session('error'))
        <div class="max-w-[1400px] w-full mx-auto px-4 sm:px-8 lg:px-12 pt-5 space-y-2">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-base font-medium">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-base font-medium">
                    ⚠ {{ session('error') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Popup Animasi Bulat Keranjang ala ShopeeFood (Klik langsung ke halaman Keranjang) --}}
    @if (! request()->routeIs('cart.*'))
        <div
            x-show="cartCount > 0"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-10 scale-50"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-2"
        >
            {{-- Toast Bubble saat baru ditambahkan --}}
            <div
                x-show="cartPop"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-90"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="px-4 py-2 rounded-2xl bg-slate-900 text-white text-xs font-bold shadow-xl border border-white/15 flex items-center gap-2"
            >
                <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[11px]">✓</span>
                <span x-text="lastAddedName + ' masuk keranjang!'"></span>
            </div>

            {{-- Floating Circular + Pill Button menuju /keranjang --}}
            <a
                href="{{ route('cart.index') }}"
                :class="cartPop ? 'scale-110 ring-8 ring-burgundy-500/30' : 'hover:scale-105'"
                class="group relative flex items-center gap-3 p-2 pr-5 rounded-full bg-burgundy-700 hover:bg-burgundy-800 text-white shadow-2xl border-2 border-white transition-all duration-300 cursor-pointer"
                title="Klik untuk membuka Keranjang Belanja"
            >
                {{-- Bulatan Ikon Keranjang dengan Badge --}}
                <div class="relative w-13 h-13 w-[52px] h-[52px] rounded-full bg-amber-400 text-burgundy-950 flex items-center justify-center text-2xl shadow-md">
                    <span :class="cartPop ? 'animate-bounce' : ''">🛒</span>
                    <span
                        x-text="cartCount"
                        class="absolute -top-1.5 -right-1.5 min-w-[24px] h-6 px-1.5 rounded-full bg-slate-900 text-amber-300 font-mono-tech text-xs font-extrabold flex items-center justify-center border-2 border-white shadow"
                    ></span>
                </div>

                {{-- Info Total & CTA --}}
                <div class="text-left leading-tight">
                    <div class="text-[11px] font-semibold text-burgundy-100 uppercase tracking-wider">Keranjang Sewa</div>
                    <div class="font-mono-tech text-sm sm:text-base font-extrabold text-white">
                        Rp <span x-text="cartTotalFormatted"></span>
                    </div>
                </div>

                <span class="ml-1 text-lg font-extrabold text-amber-300 group-hover:translate-x-0.5 transition">→</span>
            </a>
        </div>
    @endif

    {{-- Modal Detail Produk (Dapat dilihat sebelum login, serta memilih Keranjang Transfer atau WA Langsung) --}}
    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-xs">
        <div @click.outside="detailOpen = false" class="w-full max-w-2xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-2xl max-h-[90vh] overflow-y-auto">
            <template x-if="detailProduct">
                <div>
                    <div class="relative h-64 sm:h-72 bg-burgundy-50 border-b border-slate-200 flex items-center justify-center overflow-hidden">
                        <template x-if="detailProduct.image_url">
                            <img :src="detailProduct.image_url" :alt="detailProduct.name" referrerpolicy="no-referrer" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!detailProduct.image_url">
                            <div class="text-center p-6 space-y-2">
                                <div class="text-5xl">📸</div>
                                <div class="font-mono-tech text-sm font-bold uppercase text-burgundy-700" x-text="detailProduct.category"></div>
                            </div>
                        </template>
                        <button type="button" @click="detailOpen = false"
                                class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/95 border border-slate-200 text-slate-700 hover:text-slate-900 font-bold shadow-sm cursor-pointer">
                            ✕
                        </button>
                        <span class="absolute bottom-4 left-4 font-mono-tech text-xs font-bold uppercase px-3 py-1 rounded-full bg-white/95 border border-slate-200 text-slate-800" x-text="detailProduct.category"></span>
                    </div>

                    <div class="p-6 sm:p-8 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div>
                                <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900" x-text="detailProduct.name"></h2>
                                <p class="text-sm text-slate-500 mt-1">Disediakan oleh <strong class="text-slate-800" x-text="detailProduct.seller"></strong></p>
                            </div>
                            <div class="text-left sm:text-right shrink-0">
                                <div class="font-mono-tech text-2xl font-bold text-burgundy-700" x-text="'Rp ' + detailProduct.price_formatted + '/hari'"></div>
                                <span
                                    class="inline-block mt-1 font-mono-tech text-xs font-bold px-3 py-1 rounded-full"
                                    :class="detailProduct.stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'"
                                    x-text="detailProduct.stock > 0 ? 'Stok Ready: ' + detailProduct.stock + ' unit' : 'Stok Habis'"
                                ></span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Spesifikasi & Kelengkapan Unit</div>
                            <p class="text-base text-slate-700 leading-relaxed" x-text="detailProduct.description"></p>
                        </div>

                        @guest
                            <div class="p-4 rounded-2xl bg-burgundy-50 border border-burgundy-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="text-sm text-burgundy-900">
                                    <strong>Ingin menyewa atau masukkan ke Keranjang?</strong> Silakan masuk terlebih dahulu untuk memilih pembayaran via Keranjang Transfer atau WhatsApp.
                                </div>
                                <a href="{{ route('login') }}"
                                   class="px-5 py-3 rounded-xl bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm text-center shrink-0 transition">
                                    Masuk untuk Sewa →
                                </a>
                            </div>
                        @else
                            @if (auth()->user()->isPembeli())
                                <template x-if="detailProduct.stock > 0">
                                    <div class="space-y-3 pt-1">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Jumlah Unit</label>
                                                <input type="number" min="1" :max="detailProduct.stock" x-model="detailQty"
                                                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50 font-mono-tech text-sm font-bold text-slate-800 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Durasi Sewa</label>
                                                <select x-model="detailDuration"
                                                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-slate-50 font-mono-tech text-sm font-bold text-slate-800 outline-none">
                                                    <option value="1">1 hari</option>
                                                    <option value="2">2 hari</option>
                                                    <option value="3">3 hari</option>
                                                    <option value="7">7 hari</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            {{-- Pilihan 1: Masukkan ke Keranjang (Memicu Animasi Popup Bulat) --}}
                                            <button type="button"
                                                @click="addModalToCart()"
                                                class="w-full py-3 px-4 bg-amber-50 hover:bg-amber-100 text-burgundy-900 font-bold text-sm border border-burgundy-300 rounded-xl transition cursor-pointer">
                                                🛒 + Keranjang (Transfer)
                                            </button>

                                            {{-- Pilihan 2: Sewa Langsung via WhatsApp --}}
                                            <form method="POST" action="{{ route('orders.store') }}">
                                                @csrf
                                                <input type="hidden" name="product_id" :value="detailProduct.id">
                                                <input type="hidden" name="duration_days" :value="detailDuration">
                                                <button type="submit"
                                                    class="w-full py-3 px-4 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm rounded-xl shadow-sm transition cursor-pointer">
                                                    💬 Sewa via WhatsApp
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="detailProduct.stock <= 0">
                                    <button type="button" disabled class="w-full py-3 px-6 bg-slate-100 text-slate-400 font-bold text-base rounded-xl border border-slate-200 cursor-not-allowed">
                                        Stok Sedang Habis
                                    </button>
                                </template>
                            @endif
                        @endguest
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Clean Multi-Column Footer --}}
    <footer class="border-t border-slate-200 bg-slate-50/70">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-12 grid grid-cols-1 sm:grid-cols-3 gap-8 text-base">
            <div class="space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-burgundy-700 text-white flex items-center justify-center text-lg">📸</span>
                    <span class="font-display text-xl font-extrabold text-slate-900">Sewao</span>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed max-w-sm">
                    Platform katalog & rental perangkat elektronik siap pakai. Kumpulkan unit di Keranjang untuk pembayaran Transfer, atau booking langsung ke WhatsApp toko.
                </p>
            </div>

            <div class="space-y-2.5">
                <div class="text-sm font-bold uppercase tracking-wider text-slate-900">Navigasi</div>
                <ul class="space-y-2 text-sm text-slate-600">
                    @guest
                        <li><a href="{{ route('jelajah') }}#beranda" class="hover:text-burgundy-700 transition">Beranda</a></li>
                    @endguest
                    <li><a href="{{ route('jelajah') }}#pemesanan" class="hover:text-burgundy-700 transition">Tahapan Pemesanan</a></li>
                    <li><a href="{{ route('marketplace') }}" class="hover:text-burgundy-700 transition">Marketplace Unit</a></li>
                    @guest
                        <li><a href="{{ route('jelajah') }}#ulasan" class="hover:text-burgundy-700 transition">Ulasan Penyewa</a></li>
                    @endguest
                </ul>
            </div>

            <div class="space-y-2.5">
                <div class="text-sm font-bold uppercase tracking-wider text-slate-900">Akun & Layanan CS</div>
                <ul class="space-y-2 text-sm text-slate-600">
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-burgundy-700 transition">Masuk Akun</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-burgundy-700 transition">Daftar Penyewa</a></li>
                    @else
                        <li>Masuk sebagai <strong class="text-slate-900">{{ auth()->user()->name }}</strong></li>
                        @if (auth()->user()->isPembeli())
                            <li><a href="{{ route('cart.index') }}" class="text-burgundy-700 font-semibold hover:underline">Keranjang Belanja</a></li>
                            <li><a href="{{ route('pesanan') }}" class="text-burgundy-700 font-semibold hover:underline">Pesanan Saya</a></li>
                            <li><a href="{{ route('favorit') }}" class="text-burgundy-700 font-semibold hover:underline">Favorit Saya</a></li>
                        @else
                            <li><a href="{{ auth()->user()->dashboardRoute() }}" class="text-burgundy-700 font-semibold hover:underline">Buka Panel Pengelola</a></li>
                        @endif
                    @endguest
                    <li class="pt-1">
                        <a href="https://wa.me/6285216114321?text={{ rawurlencode('Halo CS Sewao, saya ingin bertanya mengenai penyewaan perangkat elektronik.') }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 font-mono-tech text-burgundy-700 font-semibold hover:underline">
                            <span>CS WhatsApp: 085216114321</span>
                            <span>↗</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-200 py-5 text-center text-sm text-slate-500">
            © {{ date('Y') }} Sewao · Platform Katalog Rental Elektronik
        </div>
    </footer>
</body>
</html>
