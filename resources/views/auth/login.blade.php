<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sewao</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        burgundy: {
                            50: '#FDF2F4',
                            100: '#FCE7EB',
                            200: '#F8CCD5',
                            500: '#B91C47',
                            600: '#8E1034',
                            700: '#720926',
                            800: '#5A071E',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700;800&family=JetBrains+Mono:wght@500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #0F172A; font-size: 16px; }
        h1, h2 { font-family: 'Bricolage Grotesque', sans-serif; letter-spacing: -0.02em; }
        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-b from-burgundy-50 via-slate-50 to-slate-50">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-7 sm:p-8 shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('jelajah') }}" class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-burgundy-700 text-white flex items-center justify-center text-lg">📸</span>
                <span class="text-2xl font-extrabold tracking-tight text-slate-900">Sewao</span>
            </a>
            <span class="mono text-xs px-3 py-1 bg-burgundy-50 border border-burgundy-200 text-burgundy-700 rounded-full font-semibold">LOGIN</span>
        </div>

        <h1 class="text-3xl font-extrabold mb-1.5 text-slate-900">Masuk ke Akun Sewao</h1>
        <p class="text-base text-slate-500 mb-6">Kelola sewa perangkat elektronik atau lanjutkan booking via WhatsApp.</p>

        @if ($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold mb-1.5 text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 border border-slate-300 focus:border-burgundy-700 rounded-xl outline-none text-base">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold mb-1.5 text-slate-700">Password</label>
                <x-password-input id="password" name="password" />
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-burgundy-700">
                Ingat sesi saya
            </label>
            <button type="submit"
                class="w-full py-3 px-4 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-base rounded-xl shadow-sm transition cursor-pointer">
                Masuk Sekarang
            </button>
        </form>

        <p class="mt-5 text-sm text-center text-slate-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-burgundy-700 hover:underline">Daftar Pembeli</a>
        </p>

        <div class="mt-6 pt-4 border-t border-slate-200 text-xs sm:text-sm text-slate-500 space-y-1">
            <div class="font-semibold text-slate-800">Akun Demo (password: <span class="mono">password123</span>)</div>
            <div>Admin: <span class="mono">admin@sewao.id</span></div>
            <div>Seller: <span class="mono">seller@sewao.id</span></div>
            <div>Pembeli: <span class="mono">pembeli@sewao.id</span></div>
        </div>
    </div>
</body>
</html>
