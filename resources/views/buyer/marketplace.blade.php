@extends('layouts.storefront')

@section('title', 'Marketplace — Sewao')

@section('content')
@php
    $catalogItems = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => strtolower($p->name),
        'description' => strtolower((string) $p->description),
        'category_slug' => $p->category->slug ?? 'lainnya',
        'category_name' => strtolower($p->category->name ?? ''),
        'price' => (int) $p->price_per_day,
        'is_featured' => (bool) $p->is_featured,
    ])->values();
@endphp

<div
    x-data="{
        search: @js($initialSearch),
        category: @js($initialCategory),
        sortBy: @js($initialSort),
        items: @js($catalogItems),
        matches(item) {
            const q = this.search.trim().toLowerCase();
            const catOk = this.category === 'all' || item.category_slug === this.category;
            const searchOk = q === '' || item.name.includes(q) || item.description.includes(q) || item.category_name.includes(q);
            return catOk && searchOk;
        },
        get sortedIds() {
            const filtered = this.items.filter(i => this.matches(i));
            return filtered.sort((a, b) => {
                if (this.sortBy === 'price_asc') return a.price - b.price;
                if (this.sortBy === 'price_desc') return b.price - a.price;
                if (this.sortBy === 'name_asc') return a.name.localeCompare(b.name);
                if (a.is_featured !== b.is_featured) return a.is_featured ? -1 : 1;
                return b.id - a.id;
            }).map(i => i.id);
        },
        orderIndex(id) {
            const idx = this.sortedIds.indexOf(id);
            return idx === -1 ? 9999 : idx;
        },
        isVisible(id) {
            return this.sortedIds.includes(id);
        }
    }"
>
    <section class="max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-12 py-10 sm:py-12 space-y-7">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="font-mono-tech text-xs sm:text-sm font-bold uppercase tracking-wider text-burgundy-700">MARKETPLACE RENTAL</div>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 mt-1">
                    Pilih Perangkat Sesuai Kebutuhan Anda
                </h1>
                <p class="text-base text-slate-600 mt-1">
                    Klik <strong>Detail</strong> untuk melihat spesifikasi unit, atau masuk sebagai Pembeli untuk menyewa langsung via WhatsApp.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-2.5">
                <label for="sortBy" class="text-sm font-bold uppercase tracking-wider text-slate-500">Urutkan:</label>
                <select id="sortBy" x-model="sortBy"
                        class="px-4 py-2.5 bg-white border border-slate-300 focus:border-burgundy-600 rounded-xl text-sm sm:text-base font-semibold text-slate-800 outline-none cursor-pointer">
                    <option value="featured">Unggulan Dulu</option>
                    <option value="price_asc">Harga Terendah</option>
                    <option value="price_desc">Harga Tertinggi</option>
                    <option value="name_asc">Nama A–Z</option>
                </select>
            </div>
        </div>

        {{-- Search & Category Pills --}}
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 space-y-3.5">
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base pointer-events-none">🔍</span>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari iPhone, MacBook, Sony A7..."
                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 focus:border-burgundy-600 rounded-xl text-base outline-none"
                >
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button
                    type="button"
                    @click="category = 'all'"
                    :class="category === 'all' ? 'bg-burgundy-700 text-white border-burgundy-700 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:border-burgundy-400'"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition cursor-pointer"
                >
                    Semua
                </button>

                @foreach ($categories as $cat)
                    <button
                        type="button"
                        @click="category = '{{ $cat->slug }}'"
                        :class="category === '{{ $cat->slug }}' ? 'bg-burgundy-700 text-white border-burgundy-700 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:border-burgundy-400'"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition cursor-pointer"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Grid 4 Kolom Presisi Kanan-Kiri --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($products as $product)
                <div
                    x-show="isVisible({{ $product->id }})"
                    :style="'order: ' + orderIndex({{ $product->id }})"
                    class="flex flex-col"
                >
                    <x-product-card
                        :product="$product"
                        :is-favorited="in_array($product->id, $favoriteIds, true)"
                        class="flex-1"
                    />
                </div>
            @endforeach
        </div>

        {{-- Empty State --}}
        <div
            x-show="sortedIds.length === 0"
            x-cloak
            class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-3"
        >
            <div class="text-4xl">🔎</div>
            <h2 class="font-display text-2xl font-extrabold text-slate-900">Perangkat Tidak Ditemukan</h2>
            <p class="text-base text-slate-500">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
            <button
                type="button"
                @click="search = ''; category = 'all'; sortBy = 'featured'"
                class="px-5 py-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm rounded-xl cursor-pointer"
            >
                Tampilkan Semua Unit
            </button>
        </div>
    </section>
</div>
@endsection
