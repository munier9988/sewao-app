<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jelajah Katalog') — Sewao</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-canvas: #FFFDF5;
            --bg-surface: #FFFFFF;
            --bg-subtle: #FEF9C3;
            --accent-yellow: #FACC15;
            --accent-yellow-hover: #EAB308;
            --accent-amber-dark: #B45309;
            --text-primary: #1C1917;
            --text-on-yellow: #422006;
            --text-muted: #57534E;
            --wa-primary: #15803D;
            --border-crisp: #E7E5E4;
        }
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-primary);
        }
        h1, h2, h3, .font-display {
            font-family: 'Bricolage Grotesque', sans-serif;
            letter-spacing: -0.02em;
        }
        .font-mono-tech {
            font-family: 'JetBrains Mono', monospace;
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
@endphp
<body
    class="min-h-screen flex flex-col bg-[#FFFDF5] text-[#1C1917]"
    x-data="{ favCount: {{ $buyerFavoriteCount }} }"
    @favorite-updated.window="favCount = $event.detail.count"
>
    {{-- Top Sticky Navbar: Tepat 3 Menu Utama (Jelajah, Pesanan, Favorit) --}}
    <header class="sticky top-0 z-30 bg-[#FFFDF5]/95 backdrop-blur border-b-[1.5px] border-[#1C1917]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            {{-- Brand Logo --}}
            <a href="{{ route('jelajah') }}" class="flex items-center gap-2.5 shrink-0">
                <span class="w-9 h-9 rounded-lg bg-[#FACC15] border-[1.5px] border-[#1C1917] flex items-center justify-center text-lg shadow-[2px_2px_0px_#1C1917]">📸</span>
                <span class="font-display text-xl font-extrabold tracking-tight text-[#1C1917]">Sewao</span>
            </a>

            {{-- 3 Menu Utama Pembeli --}}
            <nav aria-label="Menu Utama" class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('jelajah') }}"
                   class="px-3 sm:px-4 py-1.5 rounded-lg text-xs sm:text-sm font-bold transition {{ request()->routeIs('jelajah') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:text-[#1C1917] hover:bg-[#FEF9C3]/70 border-[1.5px] border-transparent' }}">
                    Jelajah
                </a>

                <a href="{{ route('pesanan') }}"
                   class="px-3 sm:px-4 py-1.5 rounded-lg text-xs sm:text-sm font-bold transition inline-flex items-center gap-1.5 {{ request()->routeIs('pesanan') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:text-[#1C1917] hover:bg-[#FEF9C3]/70 border-[1.5px] border-transparent' }}">
                    <span>Pesanan</span>
                    @if ($buyerOrderCount > 0)
                        <span class="font-mono-tech text-[10px] px-1.5 py-0.5 rounded bg-[#1C1917] text-[#FACC15]">{{ $buyerOrderCount }}</span>
                    @endif
                </a>

                <a href="{{ route('favorit') }}"
                   class="px-3 sm:px-4 py-1.5 rounded-lg text-xs sm:text-sm font-bold transition inline-flex items-center gap-1.5 {{ request()->routeIs('favorit') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:text-[#1C1917] hover:bg-[#FEF9C3]/70 border-[1.5px] border-transparent' }}">
                    <span>Favorit</span>
                    @if (auth()->check() && auth()->user()->isPembeli())
                        <span x-show="favCount > 0" x-text="favCount" class="font-mono-tech text-[10px] px-1.5 py-0.5 rounded bg-[#1C1917] text-[#FACC15]"></span>
                    @endif
                </a>
            </nav>

            {{-- Account Controls --}}
            <div class="flex items-center gap-2 shrink-0">
                @auth
                    @if (! auth()->user()->isPembeli())
                        <a href="{{ auth()->user()->dashboardRoute() }}"
                           class="hidden sm:inline-flex px-3 py-1.5 rounded-lg bg-[#FEF9C3] border-[1.5px] border-[#1C1917] text-xs font-bold text-[#422006] hover:bg-[#FACC15] transition">
                            Panel {{ ucfirst(auth()->user()->role) }}
                        </a>
                    @else
                        <span class="hidden md:inline-flex px-2.5 py-1 rounded-lg bg-white border border-[#E7E5E4] text-xs font-semibold text-[#57534E]">
                            {{ auth()->user()->name }}
                        </span>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-white hover:bg-red-50 text-xs font-bold text-[#1C1917] hover:text-red-700 border-[1.5px] border-[#1C1917] transition cursor-pointer">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="px-3.5 py-1.5 rounded-lg bg-[#FACC15] hover:bg-[#EAB308] text-xs sm:text-sm font-bold text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917] transition">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border-[1.5px] border-emerald-600 text-emerald-900 text-sm font-medium">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-50 border-[1.5px] border-red-600 text-red-900 text-sm font-medium">
                ⚠ {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Minimal Footer --}}
    <footer class="border-t border-[#E7E5E4] py-5 text-center text-xs text-[#57534E]">
        <span class="font-display font-bold text-[#1C1917]">Sewao</span> · Katalog Rental Perangkat Elektronik · Booking Cepat via WhatsApp
    </footer>
</body>
</html>
