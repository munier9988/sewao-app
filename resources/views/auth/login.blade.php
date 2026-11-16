<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sewao</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700;800&family=JetBrains+Mono:wght@500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF5; color: #1C1917; }
        h1, h2 { font-family: 'Bricolage Grotesque', sans-serif; letter-spacing: -0.02em; }
        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 sm:p-8">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('jelajah') }}" class="text-xl font-extrabold tracking-tight text-[#1C1917]">📸 Sewao</a>
            <span class="mono text-xs px-2.5 py-1 bg-[#FEF9C3] border border-[#FDE68A] text-[#422006] rounded-md">LOGIN</span>
        </div>

        <h1 class="text-2xl font-extrabold mb-1">Masuk ke Akun Sewao</h1>
        <p class="text-sm text-[#57534E] mb-6">Kelola sewa perangkat elektronik atau lanjutkan booking via WhatsApp.</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-300 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold mb-1">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold mb-1">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm text-[#57534E]">
                <input type="checkbox" name="remember" class="rounded border-[#E7E5E4]">
                Ingat sesi saya
            </label>
            <button type="submit"
                class="w-full py-2.5 px-4 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold border-[1.5px] border-[#1C1917] rounded-lg transition cursor-pointer">
                Masuk Sekarang
            </button>
        </form>

        <p class="mt-5 text-sm text-center text-[#57534E]">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-[#B45309] hover:underline">Daftar Pembeli</a>
        </p>

        <div class="mt-6 pt-4 border-t border-[#E7E5E4] text-xs text-[#57534E] space-y-1">
            <div class="font-semibold text-[#1C1917]">Akun Demo (password: <span class="mono">password123</span>)</div>
            <div>Admin: <span class="mono">admin@sewao.id</span></div>
            <div>Seller: <span class="mono">seller@sewao.id</span></div>
            <div>Pembeli: <span class="mono">pembeli@sewao.id</span></div>
        </div>
    </div>
</body>
</html>
