<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pembeli — Sewao</title>
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
            <span class="mono text-xs px-2.5 py-1 bg-[#FEF9C3] border border-[#FDE68A] text-[#422006] rounded-md">REGISTER</span>
        </div>

        <h1 class="text-2xl font-extrabold mb-1">Buat Akun Pembeli</h1>
        <p class="text-sm text-[#57534E] mb-6">Daftar untuk menyimpan favorit dan mencatat pesanan sewa gadget.</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-300 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold mb-1">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm">
            </div>
            <div>
                <label for="wa_phone" class="block text-sm font-semibold mb-1">Nomor WhatsApp (Opsional)</label>
                <input id="wa_phone" name="wa_phone" type="text" value="{{ old('wa_phone') }}" placeholder="0812xxxx atau 62812xxxx"
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm mono">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold mb-1">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold mb-1">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg outline-none text-sm">
            </div>
            <button type="submit"
                class="w-full py-2.5 px-4 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold border-[1.5px] border-[#1C1917] rounded-lg transition cursor-pointer">
                Daftar & Mulai Jelajah
            </button>
        </form>

        <p class="mt-5 text-sm text-center text-[#57534E]">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-[#B45309] hover:underline">Masuk</a>
        </p>
    </div>
</body>
</html>
