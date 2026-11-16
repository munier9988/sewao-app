@props([
    'product',
    'isFavorited' => false,
    'removeOnUnfavorite' => false,
])

@php
    $canBuyerAction = ! auth()->check() || auth()->user()->isPembeli();
    $isPembeli = auth()->check() && auth()->user()->isPembeli();

    $rawImageUrl = trim((string) ($product->image_url ?? ''));
    $resolvedImageUrl = null;
    if ($rawImageUrl !== '') {
        if (str_starts_with($rawImageUrl, 'http://')
            || str_starts_with($rawImageUrl, 'https://')
            || str_starts_with($rawImageUrl, '//')
            || str_starts_with($rawImageUrl, '/')
            || str_starts_with($rawImageUrl, 'data:image/')) {
            $resolvedImageUrl = $rawImageUrl;
        } else {
            $resolvedImageUrl = asset($rawImageUrl);
        }
    }

    $detailPayload = [
        'id' => $product->id,
        'name' => $product->name,
        'category' => $product->category->name ?? 'Elektronik',
        'seller' => $product->seller->name ?? 'Toko Sewao',
        'price_formatted' => number_format($product->price_per_day, 0, ',', '.'),
        'stock' => (int) $product->stock,
        'description' => $product->description ?: 'Unit elektronik siap pakai, kondisi terawat & kelengkapan lengkap.',
        'image_url' => $resolvedImageUrl,
    ];
@endphp

<article
    x-data="{
        favorited: @js($isFavorited),
        loadingFav: false,
        loadingCart: false,
        justAddedCart: false,
        removed: false,
        async toggleFavorite() {
            if (this.loadingFav) return;
            this.loadingFav = true;
            try {
                const res = await fetch('{{ route('favorites.toggle', $product) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                if (res.status === 401) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }
                if (res.ok) {
                    const data = await res.json();
                    this.favorited = data.favorited;
                    window.dispatchEvent(new CustomEvent('favorite-updated', { detail: { count: data.count } }));
                    if (!this.favorited && {{ $removeOnUnfavorite ? 'true' : 'false' }}) {
                        this.removed = true;
                    }
                }
            } finally {
                this.loadingFav = false;
            }
        },
        async addToCart() {
            @guest
                window.location.href = '{{ route('login') }}';
                return;
            @endguest
            if (this.loadingCart) return;
            this.loadingCart = true;
            try {
                const res = await fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: {{ $product->id }},
                        quantity: 1,
                        duration_days: 1,
                    }),
                });
                if (res.status === 401) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }
                if (res.ok) {
                    const data = await res.json();
                    this.justAddedCart = true;
                    setTimeout(() => { this.justAddedCart = false; }, 900);
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                }
            } finally {
                this.loadingCart = false;
            }
        }
    }"
    x-show="!removed"
    {{ $attributes->merge(['class' => 'group bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col justify-between shadow-sm hover:shadow-lg hover:border-burgundy-300 transition duration-200']) }}
>
    <div>
        {{-- Visual Frame (Klik untuk Lihat Detail Produk) --}}
        <div
            role="button"
            tabindex="0"
            data-detail="{{ json_encode($detailPayload) }}"
            @click="$dispatch('open-product-detail', JSON.parse($el.dataset.detail))"
            @keydown.enter="$dispatch('open-product-detail', JSON.parse($el.dataset.detail))"
            class="relative h-52 border-b border-slate-100 bg-burgundy-50/40 overflow-hidden cursor-pointer"
        >
            <x-product-image :product="$product" class="w-full h-full group-hover:scale-[1.03] transition duration-300" />

            {{-- Featured Badge --}}
            @if ($product->is_featured)
                <span class="absolute top-3 left-3 font-mono-tech text-xs font-bold px-2.5 py-1 rounded-full bg-amber-400 text-amber-950 shadow-xs">
                    ★ Favorit
                </span>
            @endif

            {{-- Favorite Toggle Button --}}
            @if ($isPembeli)
                <button
                    type="button"
                    @click.stop="toggleFavorite()"
                    :aria-label="favorited ? 'Hapus dari Favorit' : 'Simpan ke Favorit'"
                    class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/95 backdrop-blur border border-slate-200 shadow-xs flex items-center justify-center text-lg transition cursor-pointer hover:border-burgundy-400"
                >
                    <span x-text="favorited ? '♥' : '♡'" :class="favorited ? 'text-burgundy-600' : 'text-slate-700'"></span>
                </button>
            @elseif (! auth()->check())
                <a href="{{ route('login') }}"
                   @click.stop
                   title="Masuk untuk menyimpan Favorit"
                   class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/95 backdrop-blur border border-slate-200 shadow-xs flex items-center justify-center text-lg text-slate-700 hover:border-burgundy-400 transition">
                    ♡
                </a>
            @endif

            {{-- Category Chip --}}
            <span class="absolute bottom-3 left-3 font-mono-tech text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full bg-white/95 backdrop-blur border border-slate-200 text-slate-700">
                {{ $product->category->name ?? 'Elektronik' }}
            </span>
        </div>

        {{-- Body --}}
        <div class="p-4 sm:p-5">
            <h3
                data-detail="{{ json_encode($detailPayload) }}"
                @click="$dispatch('open-product-detail', JSON.parse($el.dataset.detail))"
                class="font-display text-lg sm:text-xl font-extrabold text-slate-900 leading-snug hover:text-burgundy-700 cursor-pointer transition"
            >
                {{ $product->name }}
            </h3>
            <p class="text-sm text-slate-600 mt-1.5 line-clamp-2 min-h-[2.5rem]">
                {{ $product->description ?: 'Unit elektronik siap pakai, kondisi terawat & kelengkapan lengkap.' }}
            </p>
        </div>
    </div>

    {{-- Price, Stock & Actions (Detail | Tombol Ikon Keranjang Animasi | Sewa via WhatsApp) --}}
    <div class="px-4 sm:px-5 pb-5 pt-3.5 border-t border-slate-100 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="font-mono-tech text-lg font-bold text-burgundy-700">
                Rp {{ number_format($product->price_per_day, 0, ',', '.') }}<span class="text-xs font-medium text-slate-500">/hr</span>
            </div>
            @if ($product->stock > 0)
                <span class="font-mono-tech text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700">
                    Stok: {{ $product->stock }}
                </span>
            @else
                <span class="font-mono-tech text-xs font-semibold px-2.5 py-1 rounded-full bg-red-50 border border-red-200 text-red-700">
                    Habis
                </span>
            @endif
        </div>

        <div class="flex items-center gap-1.5">
            <button
                type="button"
                data-detail="{{ json_encode($detailPayload) }}"
                @click="$dispatch('open-product-detail', JSON.parse($el.dataset.detail))"
                class="px-2.5 py-2.5 bg-white hover:bg-burgundy-50 text-slate-700 hover:text-burgundy-700 font-semibold text-xs border border-slate-300 hover:border-burgundy-300 rounded-xl transition cursor-pointer shrink-0"
            >
                Detail
            </button>

            @if (! $canBuyerAction)
                <div class="flex-1 py-2.5 px-3 text-center text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-200 rounded-xl">
                    Lihat Saja
                </div>
            @elseif ($product->stock > 0)
                {{-- Tombol Ikon Keranjang (Memicu Animasi Popup Bulat ala ShopeeFood) --}}
                <button
                    type="button"
                    @click="addToCart()"
                    title="Tambah ke Keranjang Belanja"
                    :class="justAddedCart ? 'bg-emerald-600 text-white border-emerald-600 scale-110' : 'bg-burgundy-50 hover:bg-burgundy-100 text-burgundy-800 border-burgundy-200'"
                    class="w-10 h-10 rounded-full border flex items-center justify-center text-base font-bold shadow-xs transition duration-200 cursor-pointer shrink-0"
                >
                    <span x-show="!justAddedCart">🛒</span>
                    <span x-show="justAddedCart" style="display: none;" class="text-xs font-extrabold">+1</span>
                </button>

                {{-- Tombol Sewa via WhatsApp --}}
                <form method="POST" action="{{ route('orders.store') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="duration_days" value="1">
                    <button type="submit"
                        class="w-full py-2.5 px-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Sewa via WhatsApp
                    </button>
                </form>
            @else
                <button type="button" disabled
                    class="flex-1 py-2.5 px-3 bg-slate-100 text-slate-400 font-bold text-xs sm:text-sm border border-slate-200 rounded-xl cursor-not-allowed">
                    Stok Habis
                </button>
            @endif
        </div>
    </div>
</article>
