<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Pengelola') — Sewao</title>
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
                            500: '#B91C47',
                            600: '#8E1034',
                            700: '#720926',
                            800: '#5A071E',
                            900: '#420516',
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
            background-color: #F8FAFC;
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
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 text-base" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-xs lg:hidden"
        ></div>

        {{-- Left Sidebar Navbar (260px fixed on desktop, slide-over drawer on mobile) --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-[260px] shrink-0 bg-white border-r border-slate-200 flex flex-col justify-between transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen"
        >
            <div>
                {{-- Brand Header --}}
                <div class="px-5 py-5 border-b border-slate-200 bg-burgundy-50/70 flex items-center justify-between">
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-burgundy-700 text-white flex items-center justify-center text-xl shadow-sm">📸</span>
                        <div>
                            <div class="font-display text-xl font-extrabold leading-none text-slate-900">Sewao</div>
                            <div class="font-mono-tech text-[11px] uppercase tracking-wider text-burgundy-700 font-semibold mt-1">
                                Back-Office · {{ auth()->user()->role }}
                            </div>
                        </div>
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900">
                        ✕
                    </button>
                </div>

                {{-- Navigation Links --}}
                <nav class="p-4 space-y-1.5">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm sm:text-base font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-burgundy-700 text-white shadow-sm' : 'text-slate-600 hover:bg-burgundy-50 hover:text-burgundy-700' }}">
                            <span>👥</span>
                            <span>Kelola Pengguna</span>
                        </a>
                    @endif

                    @if (auth()->user()->isSeller())
                        <a href="{{ route('seller.products.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm sm:text-base font-semibold transition {{ request()->routeIs('seller.products.*') ? 'bg-burgundy-700 text-white shadow-sm' : 'text-slate-600 hover:bg-burgundy-50 hover:text-burgundy-700' }}">
                            <span>📦</span>
                            <span>Kelola Produk</span>
                        </a>

                        <a href="{{ route('seller.categories.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm sm:text-base font-semibold transition {{ request()->routeIs('seller.categories.*') ? 'bg-burgundy-700 text-white shadow-sm' : 'text-slate-600 hover:bg-burgundy-50 hover:text-burgundy-700' }}">
                            <span>🏷️</span>
                            <span>Kelola Kategori</span>
                        </a>

                        <a href="{{ route('seller.payments.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm sm:text-base font-semibold transition {{ request()->routeIs('seller.payments.*') ? 'bg-burgundy-700 text-white shadow-sm' : 'text-slate-600 hover:bg-burgundy-50 hover:text-burgundy-700' }}">
                            <span>💳</span>
                            <span>Manajemen Pembayaran</span>
                        </a>

                        <a href="{{ route('seller.settings.edit') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm sm:text-base font-semibold transition {{ request()->routeIs('seller.settings.*') ? 'bg-burgundy-700 text-white shadow-sm' : 'text-slate-600 hover:bg-burgundy-50 hover:text-burgundy-700' }}">
                            <span>📱</span>
                            <span>Pengaturan WhatsApp</span>
                        </a>
                    @endif

                    <div class="pt-3 mt-3 border-t border-slate-200">
                        <a href="{{ route('jelajah') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm sm:text-base font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                            <span>🌐</span>
                            <span>Lihat Katalog Publik</span>
                        </a>
                    </div>
                </nav>
            </div>

            {{-- Footer / Logout --}}
            <div class="p-4 border-t border-slate-200 bg-slate-50/70">
                <div class="mb-3 px-2">
                    <div class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                    <div class="font-mono-tech text-xs text-slate-500 truncate">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 hover:border-red-300 transition cursor-pointer">
                        <span>🚪</span>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 min-w-0 flex flex-col">
            {{-- Top Panel Header --}}
            <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200 px-4 sm:px-8 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" @click="sidebarOpen = true"
                        class="lg:hidden px-3 py-2 rounded-lg bg-white border border-slate-300 text-sm font-bold text-slate-700">
                        ☰
                    </button>
                    <div class="min-w-0">
                        <div class="font-mono-tech text-xs uppercase tracking-wider text-burgundy-700 font-semibold">
                            PANEL PENGELOLA ({{ strtoupper(auth()->user()->role) }})
                        </div>
                        <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 truncate">
                            @yield('page_title', 'Dashboard')
                        </h1>
                    </div>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="px-3.5 py-1.5 rounded-full bg-burgundy-50 border border-burgundy-200 text-sm font-semibold text-burgundy-800">
                        Halo, {{ auth()->user()->name }}
                    </span>
                </div>
            </header>

            {{-- Page Body --}}
            <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto space-y-6">
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-base font-medium flex items-center justify-between gap-3">
                        <span>✓ {{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-base font-medium flex items-center justify-between gap-3">
                        <span>⚠ {{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-sm">
                        <div class="font-bold mb-1">Periksa kembali input Anda:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
