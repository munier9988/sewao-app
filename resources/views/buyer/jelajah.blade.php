@extends('layouts.storefront')

@section('title', 'Beranda — Sewao')

@section('content')
<div>
    {{-- Section 1: Beranda / Hero Banner --}}
    <section id="beranda" class="relative overflow-hidden bg-gradient-to-b from-burgundy-50/90 via-white to-white border-b border-slate-100">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 pt-14 pb-16 sm:pt-20 sm:pb-24 text-center space-y-7">
            <span class="inline-flex items-center gap-2 font-mono-tech text-xs sm:text-sm font-semibold px-4 py-1.5 rounded-full bg-burgundy-50 border border-burgundy-200 text-burgundy-700">
                <span class="w-2 h-2 rounded-full bg-burgundy-600"></span>
                PLATFORM KATALOG & RENTAL ELEKTRONIK
            </span>

            <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 max-w-4xl mx-auto leading-[1.1]">
                Butuh Gadget Dadakan?
                <span class="text-burgundy-700">Sewa Aja di Sewao!</span>
            </h1>

            <p class="text-base sm:text-xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
                Temukan unit iPhone, Android, Laptop, hingga Kamera siap pakai. Lihat detail spesifikasi unit secara bebas di Marketplace, lalu masuk akun untuk melakukan penyewaan langsung via WhatsApp toko.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3.5 pt-1">
                <a href="{{ route('marketplace') }}"
                   class="px-7 py-3.5 rounded-xl bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-base shadow-sm transition">
                    Buka Marketplace
                </a>
                <a href="#pemesanan"
                   @click="$dispatch('nav-select', 'pemesanan')"
                   class="px-7 py-3.5 rounded-xl bg-white hover:bg-burgundy-50 text-slate-800 hover:text-burgundy-800 font-bold text-base border border-slate-300 shadow-xs transition">
                    Lihat Tahapan Pemesanan
                </a>
                <a href="https://wa.me/6285216114321?text={{ rawurlencode('Halo CS Sewao, saya ingin bertanya mengenai penyewaan perangkat elektronik.') }}"
                   target="_blank" rel="noopener"
                   class="px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base shadow-xs transition inline-flex items-center gap-2">
                    <span>CS WA: 085216114321</span>
                    <span>↗</span>
                </a>
            </div>

            {{-- Highlight Strip 4 Kolom Presisi Kanan-Kiri --}}
            <div class="pt-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-md grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 text-left">
                    <div class="flex items-center gap-3.5">
                        <span class="w-12 h-12 rounded-xl bg-burgundy-50 border border-burgundy-100 flex items-center justify-center text-2xl shrink-0">📱</span>
                        <div>
                            <div class="text-base font-bold text-slate-900">iPhone & Android</div>
                            <div class="text-sm text-slate-500">Siap liputan & harian</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5">
                        <span class="w-12 h-12 rounded-xl bg-burgundy-50 border border-burgundy-100 flex items-center justify-center text-2xl shrink-0">💻</span>
                        <div>
                            <div class="text-base font-bold text-slate-900">Laptop & MacBook</div>
                            <div class="text-sm text-slate-500">Kerja & rendering</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5">
                        <span class="w-12 h-12 rounded-xl bg-burgundy-50 border border-burgundy-100 flex items-center justify-center text-2xl shrink-0">📸</span>
                        <div>
                            <div class="text-base font-bold text-slate-900">Kamera & Audio</div>
                            <div class="text-sm text-slate-500">Dokumentasi acara</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5">
                        <span class="w-12 h-12 rounded-xl bg-burgundy-700 text-white flex items-center justify-center text-xl shrink-0">💬</span>
                        <div>
                            <div class="text-base font-bold text-slate-900">{{ $readyCount }} Unit Ready</div>
                            <div class="text-sm text-burgundy-700 font-semibold">CS: 085216114321</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: Pemesanan — Informasi Tahapan Penyewaan --}}
    <section id="pemesanan" class="border-b border-slate-100 bg-slate-50/70 py-16">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 space-y-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="space-y-2 max-w-2xl">
                    <span class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-700">INFORMASI PEMESANAN</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900">
                        Tahapan Penyewaan Perangkat di Sewao
                    </h2>
                    <p class="text-base text-slate-600">
                        Anda dapat melihat katalog serta detail spesifikasi produk terlebih dahulu di halaman Marketplace. Untuk melakukan penyewaan, pelanggan wajib masuk ke akun Pembeli.
                    </p>
                </div>
                @guest
                    <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm sm:text-base shadow-xs transition self-start">
                        Masuk untuk Mulai Sewa →
                    </a>
                @endguest
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                    <span class="inline-flex w-10 h-10 rounded-xl bg-burgundy-50 border border-burgundy-200 text-burgundy-700 font-mono-tech text-base font-bold items-center justify-center">01</span>
                    <h3 class="font-display text-xl font-extrabold text-slate-900">Jelajah & Cek Detail</h3>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">Buka menu <strong>Marketplace</strong> untuk melihat stok, tarif harian, dan klik <strong>Detail</strong> guna membaca spesifikasi lengkap unit.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                    <span class="inline-flex w-10 h-10 rounded-xl bg-burgundy-50 border border-burgundy-200 text-burgundy-700 font-mono-tech text-base font-bold items-center justify-center">02</span>
                    <h3 class="font-display text-xl font-extrabold text-slate-900">Login Akun Pembeli</h3>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">Masuk atau daftar akun terlebih dahulu sebelum menyewa agar riwayat klaim unit dan favorit tersimpan aman di akun Anda.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                    <span class="inline-flex w-10 h-10 rounded-xl bg-burgundy-50 border border-burgundy-200 text-burgundy-700 font-mono-tech text-base font-bold items-center justify-center">03</span>
                    <h3 class="font-display text-xl font-extrabold text-slate-900">Klik Sewa via WhatsApp</h3>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">Pilih durasi sewa lalu klik <strong>Sewa via WhatsApp</strong>. Sistem mencatat kode order dan langsung membuka chat WA ke toko.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                    <span class="inline-flex w-10 h-10 rounded-xl bg-burgundy-700 text-white font-mono-tech text-base font-bold items-center justify-center">04</span>
                    <h3 class="font-display text-xl font-extrabold text-slate-900">Konfirmasi & Ambil Unit</h3>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">Sepakati jadwal serah-terima unit dengan Seller di WhatsApp. Status sewa dapat dipantau kapan saja di menu Pesanan.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3: Preview Marketplace di Beranda (4 Kartu Unggulan + Pengantar + Tombol ke Marketplace) --}}
    <section class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-16 space-y-8 border-b border-slate-100">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="inline-flex items-center gap-2 font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider px-3.5 py-1 rounded-full bg-burgundy-50 border border-burgundy-200 text-burgundy-700">
                SEWAO MARKETPLACE
            </span>
            <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight">
                Sewao Marketplace: Etalasemu Ditemukan Ribuan Calon Penyewa
            </h2>
            <p class="text-base sm:text-lg text-slate-600 leading-relaxed">
                Bukan sekadar sistem internal — setiap perangkat elektronik yang dikelola Seller di Sewao otomatis tampil di direktori katalog publik siap sewa, lengkap dengan detail spesifikasi, stok real-time, dan tombol sewa langsung via WhatsApp.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($featuredProducts as $product)
                <x-product-card
                    :product="$product"
                    :is-favorited="in_array($product->id, $favoriteIds, true)"
                />
            @endforeach
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('marketplace') }}"
               class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-base shadow-sm transition">
                <span>Jelajahi Semua Produk di Marketplace</span>
                <span>→</span>
            </a>
        </div>
    </section>

    {{-- Section 4: Ulasan Penyewa --}}
    <section id="ulasan" class="bg-slate-50/70 py-16 border-b border-slate-100">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 space-y-10">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-700">ULASAN PENYEWA</span>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900">
                    Pengalaman Nyata Penyewa di Sewao
                </h2>
                <p class="text-base text-slate-600">
                    Kualitas unit terawat dan proses konfirmasi WhatsApp yang langsung terhubung ke toko.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-amber-500 text-base tracking-wider">★★★★★</span>
                        <span class="font-mono-tech text-xs font-semibold px-2.5 py-1 rounded-full bg-burgundy-50 text-burgundy-700">iPhone 15 Pro</span>
                    </div>
                    <p class="text-base text-slate-700 leading-relaxed">
                        “Butuh iPhone mendadak buat liputan event kampus. Cek detailnya jelas, klik Sewa via WhatsApp langsung direspons admin toko dan unitnya mulus banget.”
                    </p>
                    <div class="pt-2 border-t border-slate-100">
                        <div class="font-bold text-slate-900 text-sm">Raka Pratama</div>
                        <div class="text-xs text-slate-500">Content Creator · Jakarta</div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-amber-500 text-base tracking-wider">★★★★★</span>
                        <span class="font-mono-tech text-xs font-semibold px-2.5 py-1 rounded-full bg-burgundy-50 text-burgundy-700">MacBook Air M2</span>
                    </div>
                    <p class="text-base text-slate-700 leading-relaxed">
                        “Laptop utama masuk servis 3 hari, untung nemu Sewao. MacBook Air M2 RAM 16GB baterainya awet dan langsung siap kerja tanpa drama.”
                    </p>
                    <div class="pt-2 border-t border-slate-100">
                        <div class="font-bold text-slate-900 text-sm">Nadia Kusuma</div>
                        <div class="text-xs text-slate-500">UI/UX Designer · Bandung</div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-amber-500 text-base tracking-wider">★★★★★</span>
                        <span class="font-mono-tech text-xs font-semibold px-2.5 py-1 rounded-full bg-burgundy-50 text-burgundy-700">Sony A7 III</span>
                    </div>
                    <p class="text-base text-slate-700 leading-relaxed">
                        “Kelengkapan kamera sesuai deskripsi, sudah include 2 baterai dan SD card. Kode pesanannya juga langsung tersimpan rapi di menu Pesanan.”
                    </p>
                    <div class="pt-2 border-t border-slate-100">
                        <div class="font-bold text-slate-900 text-sm">Dimas Wijaya</div>
                        <div class="text-xs text-slate-500">Videografer Freelance · Surabaya</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 5: Dark Burgundy CTA & Layanan CS WhatsApp --}}
    <section class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-14">
        <div class="rounded-3xl bg-gradient-to-br from-burgundy-950 via-burgundy-900 to-slate-900 text-white p-8 sm:p-12 flex flex-col lg:flex-row lg:items-center justify-between gap-6 shadow-lg">
            <div class="space-y-2.5 max-w-2xl">
                <div class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-200">BUTUH BANTUAN ATAU SIAP SEWA?</div>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold leading-tight">
                    Konsultasi Unit atau Mulai Booking Sekarang!
                </h2>
                <p class="text-base text-burgundy-100/90">
                    Tim Customer Service Sewao siap membantu pertanyaan seputar ketersediaan unit & jadwal sewa di nomor WhatsApp <strong class="font-mono-tech text-white">085216114321</strong>.
                </p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="https://wa.me/6285216114321?text={{ rawurlencode('Halo CS Sewao, saya ingin bertanya mengenai penyewaan perangkat elektronik.') }}"
                   target="_blank" rel="noopener"
                   class="px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-base transition inline-flex items-center gap-2">
                    <span>Chat CS: 085216114321</span>
                    <span>↗</span>
                </a>
                <a href="{{ route('marketplace') }}" class="px-6 py-3.5 rounded-xl bg-white hover:bg-burgundy-50 text-burgundy-900 font-bold text-base transition">
                    Lihat Katalog Marketplace
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
