<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Pengelola') — Sewao</title>
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
<body class="min-h-screen bg-[#FFFDF5] text-[#1C1917]" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-[#1C1917]/50 lg:hidden"
        ></div>

        {{-- Left Sidebar Navbar (250px fixed on desktop, slide-over drawer on mobile) --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-[250px] shrink-0 bg-white border-r-[1.5px] border-[#1C1917] flex flex-col justify-between transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen"
        >
            <div>
                {{-- Brand Header --}}
                <div class="px-5 py-5 border-b-[1.5px] border-[#E7E5E4] bg-[#FEF9C3]/60 flex items-center justify-between">
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-lg bg-[#FACC15] border-[1.5px] border-[#1C1917] flex items-center justify-center text-lg shadow-[2px_2px_0px_#1C1917]">📸</span>
                        <div>
                            <div class="font-display text-lg font-extrabold leading-none text-[#1C1917]">Sewao</div>
                            <div class="font-mono-tech text-[11px] uppercase tracking-wider text-[#B45309] font-semibold mt-1">
                                Back-Office · {{ auth()->user()->role }}
                            </div>
                        </div>
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-md border border-[#E7E5E4] text-[#57534E]">
                        ✕
                    </button>
                </div>

                {{-- Navigation Links --}}
                <nav class="p-4 space-y-1.5">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:bg-[#FEF9C3]/60 hover:text-[#1C1917] border-[1.5px] border-transparent' }}">
                            <span>👥</span>
                            <span>Kelola Pengguna</span>
                        </a>
                    @endif

                    @if (auth()->user()->isSeller())
                        <a href="{{ route('seller.products.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('seller.products.*') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:bg-[#FEF9C3]/60 hover:text-[#1C1917] border-[1.5px] border-transparent' }}">
                            <span>📦</span>
                            <span>Kelola Produk</span>
                        </a>

                        <a href="{{ route('seller.categories.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('seller.categories.*') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:bg-[#FEF9C3]/60 hover:text-[#1C1917] border-[1.5px] border-transparent' }}">
                            <span>🏷️</span>
                            <span>Kelola Kategori</span>
                        </a>

                        <a href="{{ route('seller.settings.edit') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('seller.settings.*') ? 'bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'text-[#57534E] hover:bg-[#FEF9C3]/60 hover:text-[#1C1917] border-[1.5px] border-transparent' }}">
                            <span>📱</span>
                            <span>Pengaturan WhatsApp</span>
                        </a>
                    @endif

                    <div class="pt-3 mt-3 border-t border-[#E7E5E4]">
                        <a href="{{ route('jelajah') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold text-[#57534E] hover:bg-[#FEF9C3]/60 hover:text-[#1C1917] border-[1.5px] border-transparent transition">
                            <span>🌐</span>
                            <span>Lihat Katalog Publik</span>
                        </a>
                    </div>
                </nav>
            </div>

            {{-- Footer / Logout --}}
            <div class="p-4 border-t-[1.5px] border-[#E7E5E4] bg-[#FFFDF5]">
                <div class="mb-3 px-2">
                    <div class="text-xs font-semibold text-[#1C1917] truncate">{{ auth()->user()->name }}</div>
                    <div class="font-mono-tech text-[11px] text-[#57534E] truncate">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold text-[#1C1917] bg-white hover:bg-red-50 hover:text-red-700 border-[1.5px] border-[#E7E5E4] hover:border-red-400 transition cursor-pointer">
                        <span>🚪</span>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 min-w-0 flex flex-col">
            {{-- Top Panel Header --}}
            <header class="sticky top-0 z-20 bg-[#FFFDF5]/95 backdrop-blur border-b-[1.5px] border-[#E7E5E4] px-4 sm:px-8 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" @click="sidebarOpen = true"
                        class="lg:hidden px-2.5 py-1.5 rounded-lg bg-white border-[1.5px] border-[#1C1917] text-sm font-bold">
                        ☰
                    </button>
                    <div class="min-w-0">
                        <div class="font-mono-tech text-[11px] uppercase tracking-wider text-[#B45309] font-semibold">
                            PANEL PENGELOLA ({{ strtoupper(auth()->user()->role) }})
                        </div>
                        <h1 class="font-display text-xl sm:text-2xl font-extrabold text-[#1C1917] truncate">
                            @yield('page_title', 'Dashboard')
                        </h1>
                    </div>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-lg bg-[#FEF9C3] border-[1.5px] border-[#FDE68A] text-xs font-semibold text-[#422006]">
                        Halo, {{ auth()->user()->name }}
                    </span>
                </div>
            </header>

            {{-- Page Body --}}
            <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto space-y-6">
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border-[1.5px] border-emerald-600 text-emerald-900 text-sm font-medium flex items-center justify-between gap-3">
                        <span>✓ {{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-red-50 border-[1.5px] border-red-600 text-red-900 text-sm font-medium flex items-center justify-between gap-3">
                        <span>⚠ {{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border-[1.5px] border-red-500 text-red-900 text-sm">
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
