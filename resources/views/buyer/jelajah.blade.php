@extends('layouts.storefront')

@section('title', 'Jelajah Katalog')

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
    class="space-y-6"
>
    {{-- Hero Banner: Warm Amber (#FEF9C3) + Crisp Border --}}
    <section class="bg-[#FEF9C3] border-[1.5px] border-[#1C1917] rounded-2xl p-6 sm:p-8 shadow-[4px_4px_0px_#1C1917] flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <span class="inline-block font-mono-tech text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded bg-[#FACC15] border border-[#1C1917] text-[#422006]">
                KATALOG RENTAL ELEKTRONIK
            </span>
            <h1 class="font-display text-2xl sm:text-4xl font-extrabold text-[#1C1917] leading-tight">
                Butuh Gadget Dadakan? Sewa Aja di Sewao!
            </h1>
            <p class="text-sm sm:text-base text-[#422006]">
                Unit terawat, siap pakai, booking langsung ke WhatsApp.
            </p>
        </div>

        <div class="shrink-0 bg-white border-[1.5px] border-[#1C1917] rounded-xl px-5 py-4 shadow-[2px_2px_0px_#1C1917] text-center">
            <div class="text-3xl sm:text-4xl tracking-widest select-none">📱 💻 📸 🎧</div>
            <div class="font-mono-tech text-[11px] font-semibold text-[#B45309] mt-1.5 uppercase">
                Ready {{ $products->where('stock', '>', 0)->count() }} Unit Pilihan
            </div>
        </div>
    </section>

    {{-- Search Bar + Sorting + Category Pills --}}
    <section class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm pointer-events-none">🔍</span>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari iPhone, MacBook, Sony A7..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border-[1.5px] border-[#1C1917] rounded-xl text-sm outline-none focus:bg-[#FFFDF5]"
                >
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <label for="sortBy" class="text-xs font-bold uppercase tracking-wider text-[#57534E]">Urutkan:</label>
                <select id="sortBy" x-model="sortBy"
                        class="px-3.5 py-2.5 bg-white border-[1.5px] border-[#1C1917] rounded-xl text-sm font-semibold text-[#1C1917] outline-none cursor-pointer">
                    <option value="featured">Favorit / Unggulan Dulu</option>
                    <option value="price_asc">Harga Terendah</option>
                    <option value="price_desc">Harga Tertinggi</option>
                    <option value="name_asc">Nama A–Z</option>
                </select>
            </div>
        </div>

        {{-- Pill Filter Kategori --}}
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                @click="category = 'all'"
                :class="category === 'all' ? 'bg-[#FACC15] text-[#422006] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'bg-white text-[#57534E] border-[#E7E5E4] hover:border-[#1C1917]'"
                class="px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-bold border-[1.5px] transition cursor-pointer"
            >
                Semua
            </button>

            @foreach ($categories as $cat)
                <button
                    type="button"
                    @click="category = '{{ $cat->slug }}'"
                    :class="category === '{{ $cat->slug }}' ? 'bg-[#FACC15] text-[#422006] border-[#1C1917] shadow-[2px_2px_0px_#1C1917]' : 'bg-white text-[#57534E] border-[#E7E5E4] hover:border-[#1C1917]'"
                    class="px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-bold border-[1.5px] transition cursor-pointer"
                >
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </section>

    {{-- Grid Katalog Produk --}}
    <section>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
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

        {{-- Empty State Real-Time --}}
        <div
            x-show="sortedIds.length === 0"
            x-cloak
            class="mt-6 bg-white border-[1.5px] border-[#1C1917] rounded-2xl p-10 text-center space-y-3"
        >
            <div class="text-3xl">🔎</div>
            <h2 class="font-display text-xl font-extrabold">Perangkat Tidak Ditemukan</h2>
            <p class="text-sm text-[#57534E]">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
            <button
                type="button"
                @click="search = ''; category = 'all'; sortBy = 'featured'"
                class="px-4 py-2 bg-[#FACC15] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg cursor-pointer"
            >
                Tampilkan Semua Unit
            </button>
        </div>
    </section>
</div>
@endsection
