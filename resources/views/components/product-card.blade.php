@props([
    'product',
    'isFavorited' => false,
    'removeOnUnfavorite' => false,
])

@php
    $canBuyerAction = ! auth()->check() || auth()->user()->isPembeli();
    $isPembeli = auth()->check() && auth()->user()->isPembeli();
@endphp

<article
    x-data="{
        favorited: @js($isFavorited),
        loadingFav: false,
        removed: false,
        duration: 1,
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
        }
    }"
    x-show="!removed"
    {{ $attributes->merge(['class' => 'bg-white border-[1.5px] border-[#1C1917] rounded-xl overflow-hidden flex flex-col justify-between transition hover:-translate-y-0.5 hover:shadow-[4px_4px_0px_#1C1917]']) }}
>
    <div>
        {{-- Visual Frame --}}
        <div class="relative h-48 border-b-[1.5px] border-[#1C1917] bg-[#FEF9C3]">
            <x-product-image :product="$product" class="w-full h-full" />

            {{-- Featured Badge --}}
            @if ($product->is_featured)
                <span class="absolute top-3 left-3 font-mono-tech text-[11px] font-bold px-2.5 py-1 rounded-md bg-[#FACC15] text-[#422006] border-[1.5px] border-[#1C1917] shadow-[1px_1px_0px_#1C1917]">
                    ★ Favorit
                </span>
            @endif

            {{-- Favorite Toggle Button --}}
            @if ($isPembeli)
                <button
                    type="button"
                    @click="toggleFavorite()"
                    :aria-label="favorited ? 'Hapus dari Favorit' : 'Simpan ke Favorit'"
                    class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white border-[1.5px] border-[#1C1917] flex items-center justify-center text-lg transition cursor-pointer hover:bg-[#FEF9C3]"
                >
                    <span x-text="favorited ? '♥' : '♡'" :class="favorited ? 'text-red-600' : 'text-[#1C1917]'"></span>
                </button>
            @elseif (! auth()->check())
                <a href="{{ route('login') }}"
                   title="Masuk untuk menyimpan Favorit"
                   class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white border-[1.5px] border-[#1C1917] flex items-center justify-center text-lg text-[#1C1917] hover:bg-[#FEF9C3] transition">
                    ♡
                </a>
            @endif

            {{-- Category Chip --}}
            <span class="absolute bottom-3 left-3 font-mono-tech text-[10px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded bg-white/95 border border-[#1C1917] text-[#1C1917]">
                {{ $product->category->name ?? 'Elektronik' }}
            </span>
        </div>

        {{-- Body --}}
        <div class="p-4">
            <h3 class="font-display text-lg font-extrabold text-[#1C1917] leading-snug">
                {{ $product->name }}
            </h3>
            <p class="text-xs text-[#57534E] mt-1.5 line-clamp-2 min-h-[2.25rem]">
                {{ $product->description ?: 'Unit elektronik siap pakai, kondisi terawat & kelengkapan lengkap.' }}
            </p>
        </div>
    </div>

    {{-- Price, Stock & WhatsApp Action --}}
    <div class="px-4 pb-4 pt-3 border-t border-[#E7E5E4] space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div>
                <div class="font-mono-tech text-base font-bold text-[#B45309]">
                    Rp {{ number_format($product->price_per_day, 0, ',', '.') }}<span class="text-xs font-medium text-[#57534E]">/hr</span>
                </div>
            </div>
            @if ($product->stock > 0)
                <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-300 text-[#15803D]">
                    Stok: {{ $product->stock }}
                </span>
            @else
                <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-md bg-red-50 border border-red-300 text-red-700">
                    Habis
                </span>
            @endif
        </div>

        @if (! $canBuyerAction)
            <div class="w-full py-2 px-3 text-center text-xs font-semibold text-[#57534E] bg-[#FFFDF5] border border-[#E7E5E4] rounded-lg">
                Mode Lihat Saja ({{ ucfirst(auth()->user()->role) }})
            </div>
        @elseif ($product->stock > 0)
            <form method="POST" action="{{ route('orders.store') }}" class="flex items-center gap-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <select name="duration_days" x-model="duration" aria-label="Durasi sewa"
                        class="px-2 py-2.5 border-[1.5px] border-[#1C1917] rounded-lg bg-[#FFFDF5] font-mono-tech text-xs font-bold text-[#1C1917] outline-none">
                    <option value="1">1 hr</option>
                    <option value="2">2 hr</option>
                    <option value="3">3 hr</option>
                    <option value="7">7 hr</option>
                </select>
                <button type="submit"
                    class="flex-1 py-2.5 px-3 bg-[#15803D] hover:bg-[#166534] text-white font-bold text-xs sm:text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition cursor-pointer">
                    Sewa via WhatsApp
                </button>
            </form>
        @else
            <button type="button" disabled
                class="w-full py-2.5 px-3 bg-stone-100 text-stone-400 font-bold text-xs sm:text-sm border-[1.5px] border-stone-300 rounded-lg cursor-not-allowed">
                Stok Habis
            </button>
        @endif
    </div>
</article>
