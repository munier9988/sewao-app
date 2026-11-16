@extends('layouts.backoffice')

@section('title', 'Kelola Produk')
@section('page_title', 'Kelola Produk Elektronik')

@section('content')
<div x-data="{
    createOpen: false,
    editOpen: false,
    editProduct: {
        id: null,
        category_id: '',
        name: '',
        price_per_day: 0,
        stock: 0,
        description: '',
        image_url: '',
        is_featured: false
    },
    openEdit(product) {
        this.editProduct = {
            ...product,
            description: product.description ?? '',
            image_url: product.image_url ?? '',
            is_featured: Boolean(product.is_featured)
        };
        this.editOpen = true;
    }
}">
    @if (empty(auth()->user()->wa_phone))
        <div class="mb-5 p-4 rounded-xl bg-[#FEF9C3] border-[1.5px] border-[#1C1917] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-sm text-[#422006]">
                <strong>Nomor WhatsApp Toko Belum Diatur:</strong> Pembeli membutuhkan nomor WA aktif Anda untuk menghubungi toko saat menyewa unit.
            </div>
            <a href="{{ route('seller.settings.edit') }}" class="px-3.5 py-2 bg-[#FACC15] border-[1.5px] border-[#1C1917] rounded-lg text-xs font-bold text-[#422006] shrink-0 text-center">
                Atur Nomor WA Sekarang
            </a>
        </div>
    @endif

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 shadow-[2px_2px_0px_#1C1917]">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Total Produk</div>
            <div class="font-display text-3xl font-extrabold text-[#1C1917] mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-[#FEF9C3] border-[1.5px] border-[#1C1917] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#422006]">Unit Ready</div>
            <div class="font-display text-3xl font-extrabold text-[#15803D] mt-1">{{ $stats['ready'] }}</div>
        </div>
        <div class="bg-white border-[1.5px] border-[#E7E5E4] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Stok Habis</div>
            <div class="font-display text-3xl font-extrabold text-red-700 mt-1">{{ $stats['empty'] }}</div>
        </div>
        <div class="bg-white border-[1.5px] border-[#E7E5E4] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">★ Produk Unggulan</div>
            <div class="font-display text-3xl font-extrabold text-[#B45309] mt-1">{{ $stats['featured'] }}</div>
        </div>
    </div>

    {{-- Filter & Add Toolbar --}}
    <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('seller.products.index') }}" class="flex flex-col sm:flex-row gap-2.5 flex-1">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama perangkat atau spesifikasi..."
                class="flex-1 px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none"
            >
            <select name="category_id" class="px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm bg-white outline-none">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $categoryFilter === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-[#1C1917] text-white text-sm font-semibold rounded-lg hover:bg-[#422006] transition cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || $categoryFilter !== '')
                <a href="{{ route('seller.products.index') }}" class="px-3.5 py-2 border-[1.5px] border-[#E7E5E4] rounded-lg text-sm font-semibold text-[#57534E] hover:text-[#1C1917] text-center">
                    Reset
                </a>
            @endif
        </form>

        <button
            type="button"
            @click="createOpen = true"
            class="px-4 py-2.5 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition cursor-pointer shrink-0"
        >
            + Tambah Produk
        </button>
    </div>

    {{-- Products Table --}}
    <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FEF9C3] border-b-[1.5px] border-[#1C1917] text-xs font-bold uppercase tracking-wider text-[#422006]">
                        <th class="px-4 py-3">Unit Perangkat</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Tarif / Hari</th>
                        <th class="px-4 py-3">Stok</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-sm">
                    @forelse ($products as $product)
                        <tr class="hover:bg-[#FFFDF5] transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-20 h-16 shrink-0 rounded-lg border-[1.5px] border-[#1C1917] overflow-hidden">
                                        <x-product-image :product="$product" class="w-full h-full" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-[#1C1917]">{{ $product->name }}</span>
                                            @if ($product->is_featured)
                                                <span class="font-mono-tech text-[10px] font-bold px-2 py-0.5 rounded bg-[#FACC15] border border-[#1C1917] text-[#422006]">
                                                    ★ Favorit
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-[#57534E] line-clamp-1 mt-0.5">
                                            {{ $product->description ?: 'Tanpa deskripsi spesifikasi.' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-[#FFFDF5] border border-[#E7E5E4] text-[#57534E]">
                                    {{ $product->category->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono-tech text-xs font-bold text-[#B45309]">
                                Rp {{ number_format($product->price_per_day, 0, ',', '.') }}/hr
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($product->stock > 0)
                                    <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-300 text-[#15803D]">
                                        Ready ({{ $product->stock }})
                                    </span>
                                @else
                                    <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-md bg-red-50 border border-red-300 text-red-700">
                                        Habis (0)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button
                                    type="button"
                                    @click='openEdit(@js([
                                        "id" => $product->id,
                                        "category_id" => $product->category_id,
                                        "name" => $product->name,
                                        "price_per_day" => $product->price_per_day,
                                        "stock" => $product->stock,
                                        "description" => $product->description,
                                        "image_url" => $product->image_url,
                                        "is_featured" => $product->is_featured,
                                    ]))'
                                    class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-[#FEF9C3] text-[#1C1917] border-[1.5px] border-[#1C1917] rounded-lg transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('seller.products.destroy', $product) }}" class="inline"
                                      onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-red-50 text-red-700 border-[1.5px] border-red-300 hover:border-red-600 rounded-lg transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-[#57534E]">
                                Belum ada unit produk terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Produk --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1C1917]/60">
        <div @click.outside="createOpen = false" class="w-full max-w-lg bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[4px_4px_0px_#1C1917] max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold">Tambah Produk Elektronik</h2>
                <button type="button" @click="createOpen = false" class="text-[#57534E] hover:text-[#1C1917] font-bold">✕</button>
            </div>
            <form method="POST" action="{{ route('seller.products.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Perangkat</label>
                    <input type="text" name="name" required maxlength="150" placeholder="Contoh: MacBook Pro M3 14-inch"
                           class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <select name="category_id" required class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm bg-white outline-none">
                        <option value="">Pilih Kategori...</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Harga Sewa / Hari (Rp)</label>
                        <input type="number" name="price_per_day" min="0" required placeholder="250000"
                               class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok Unit</label>
                        <input type="number" name="stock" min="0" value="1" required
                               class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">URL Gambar <span class="font-normal text-[#57534E]">(Opsional — kosongkan untuk SVG otomatis)</span></label>
                    <input type="text" name="image_url" maxlength="255" placeholder="https://... atau /images/unit.jpg"
                           class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Spesifikasi & Kelengkapan</label>
                    <textarea name="description" rows="3" placeholder="Chip, RAM, kondisi baterai, kelengkapan tas/charger..."
                              class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-[#422006] bg-[#FEF9C3] px-3.5 py-2.5 rounded-lg border border-[#FDE68A]">
                    <input type="checkbox" name="is_featured" value="1" class="rounded border-[#1C1917]">
                    Tandai sebagai Produk Unggulan (★ Favorit)
                </label>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm font-semibold border-[1.5px] border-[#E7E5E4] rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] border-[1.5px] border-[#1C1917] rounded-lg cursor-pointer">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Produk --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1C1917]/60">
        <div @click.outside="editOpen = false" class="w-full max-w-lg bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[4px_4px_0px_#1C1917] max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold">Edit Produk Elektronik</h2>
                <button type="button" @click="editOpen = false" class="text-[#57534E] hover:text-[#1C1917] font-bold">✕</button>
            </div>
            <form method="POST" :action="'/seller/products/' + editProduct.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Perangkat</label>
                    <input type="text" name="name" x-model="editProduct.name" required maxlength="150"
                           class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <select name="category_id" x-model="editProduct.category_id" required class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm bg-white outline-none">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Harga Sewa / Hari (Rp)</label>
                        <input type="number" name="price_per_day" x-model="editProduct.price_per_day" min="0" required
                               class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok Unit</label>
                        <input type="number" name="stock" x-model="editProduct.stock" min="0" required
                               class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">URL Gambar <span class="font-normal text-[#57534E]">(Kosongkan untuk SVG placeholder)</span></label>
                    <input type="text" name="image_url" x-model="editProduct.image_url" maxlength="255"
                           class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Spesifikasi & Kelengkapan</label>
                    <textarea name="description" x-model="editProduct.description" rows="3"
                              class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-[#422006] bg-[#FEF9C3] px-3.5 py-2.5 rounded-lg border border-[#FDE68A]">
                    <input type="checkbox" name="is_featured" value="1" x-model="editProduct.is_featured" class="rounded border-[#1C1917]">
                    Tandai sebagai Produk Unggulan (★ Favorit)
                </label>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm font-semibold border-[1.5px] border-[#E7E5E4] rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] border-[1.5px] border-[#1C1917] rounded-lg cursor-pointer">Perbarui Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
