@extends('layouts.backoffice')

@section('title', 'Kelola Produk')
@section('page_title', 'Kelola Produk Elektronik')

@section('content')
@php
    $productsMap = $products->mapWithKeys(fn ($p) => [
        $p->id => [
            'id' => $p->id,
            'category_id' => $p->category_id,
            'name' => $p->name,
            'price_per_day' => $p->price_per_day,
            'stock' => $p->stock,
            'description' => $p->description ?? '',
            'image_url' => $p->image_url ?? '',
            'is_featured' => (bool) $p->is_featured,
        ],
    ]);
@endphp

<script>
    window.__sewaoProductsMap = @json($productsMap);
</script>

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
    openEdit(productId) {
        const product = window.__sewaoProductsMap[productId];
        if (!product) return;
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
        <div class="mb-5 p-4 rounded-2xl bg-burgundy-50 border border-burgundy-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-sm text-burgundy-950">
                <strong>Nomor WhatsApp Toko Belum Diatur:</strong> Pembeli membutuhkan nomor WA aktif Anda untuk menghubungi toko saat menyewa unit.
            </div>
            <a href="{{ route('seller.settings.edit') }}" class="px-4 py-2 bg-burgundy-700 hover:bg-burgundy-800 rounded-xl text-xs font-bold text-white shrink-0 text-center">
                Atur Nomor WA Sekarang
            </a>
        </div>
    @endif

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Produk</div>
            <div class="font-display text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-burgundy-50/80 border border-burgundy-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-burgundy-800">Unit Ready</div>
            <div class="font-display text-3xl font-extrabold text-burgundy-700 mt-1">{{ $stats['ready'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Stok Habis</div>
            <div class="font-display text-3xl font-extrabold text-red-600 mt-1">{{ $stats['empty'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">★ Produk Unggulan</div>
            <div class="font-display text-3xl font-extrabold text-amber-600 mt-1">{{ $stats['featured'] }}</div>
        </div>
    </div>

    {{-- Filter & Add Toolbar --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('seller.products.index') }}" class="flex flex-col sm:flex-row gap-2.5 flex-1">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama perangkat atau spesifikasi..."
                class="flex-1 px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none"
            >
            <select name="category_id" class="px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm bg-white outline-none">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $categoryFilter === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || $categoryFilter !== '')
                <a href="{{ route('seller.products.index') }}" class="px-3.5 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 text-center">
                    Reset
                </a>
            @endif
        </form>

        <button
            type="button"
            @click="createOpen = true"
            class="px-4 py-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm rounded-xl shadow-sm transition cursor-pointer shrink-0"
        >
            + Tambah Produk
        </button>
    </div>

    {{-- Products Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-4 py-3.5">Unit Perangkat</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Tarif / Hari</th>
                        <th class="px-4 py-3.5">Stok</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-20 h-16 shrink-0 rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
                                        <x-product-image :product="$product" class="w-full h-full" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-slate-900">{{ $product->name }}</span>
                                            @if ($product->is_featured)
                                                <span class="font-mono-tech text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 border border-amber-300 text-amber-800">
                                                    ★ Favorit
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">
                                            {{ $product->description ?: 'Tanpa deskripsi spesifikasi.' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700">
                                    {{ $product->category->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono-tech text-xs font-bold text-burgundy-700">
                                Rp {{ number_format($product->price_per_day, 0, ',', '.') }}/hr
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($product->stock > 0)
                                    <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700">
                                        Ready ({{ $product->stock }})
                                    </span>
                                @else
                                    <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-full bg-red-50 border border-red-200 text-red-700">
                                        Habis (0)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button
                                    type="button"
                                    @click="openEdit({{ $product->id }})"
                                    class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-300 hover:border-emerald-400 rounded-lg transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('seller.products.destroy', $product) }}" class="inline"
                                      onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-red-50 text-red-700 border border-red-200 hover:border-red-400 rounded-lg transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                Belum ada unit produk terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Produk --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="createOpen = false" class="w-full max-w-lg bg-white border border-slate-200 rounded-2xl p-6 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold text-slate-900">Tambah Produk Elektronik</h2>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>
            <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Perangkat</label>
                    <input type="text" name="name" required maxlength="150" placeholder="Contoh: MacBook Pro M3 14-inch"
                           class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <select name="category_id" required class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm bg-white outline-none">
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
                               class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok Unit</label>
                        <input type="number" name="stock" min="0" value="1" required
                               class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">URL Gambar <span class="font-normal text-slate-500">(Opsional — kosongkan untuk SVG otomatis)</span></label>
                    <input type="text" name="image_url" placeholder="https://... atau /uploads/unit.jpg"
                           class="w-full px-3.5 py-2 border border-slate-300 focus:border-burgundy-700 rounded-xl text-sm font-mono-tech outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Atau Unggah File Foto <span class="font-normal text-slate-500">(JPG/PNG/WebP maks 2MB)</span></label>
                    <input type="file" name="image_file" accept="image/*"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Spesifikasi & Kelengkapan</label>
                    <textarea name="description" rows="3" placeholder="Chip, RAM, kondisi baterai, kelengkapan tas/charger..."
                              class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-burgundy-900 bg-burgundy-50 px-3.5 py-2.5 rounded-xl border border-burgundy-200">
                    <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-burgundy-700">
                    Tandai sebagai Produk Unggulan (★ Favorit)
                </label>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm font-semibold border border-slate-300 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-burgundy-700 hover:bg-burgundy-800 text-white rounded-xl cursor-pointer">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Produk --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="editOpen = false" class="w-full max-w-lg bg-white border border-slate-200 rounded-2xl p-6 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold text-slate-900">Edit Produk Elektronik</h2>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>
            <form method="POST" :action="'{{ url('/seller/products') }}/' + editProduct.id" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Perangkat</label>
                    <input type="text" name="name" x-model="editProduct.name" required maxlength="150"
                           class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <select name="category_id" x-model="editProduct.category_id" required class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm bg-white outline-none">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Harga Sewa / Hari (Rp)</label>
                        <input type="number" name="price_per_day" x-model="editProduct.price_per_day" min="0" required
                               class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok Unit</label>
                        <input type="number" name="stock" x-model="editProduct.stock" min="0" required
                               class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">URL Gambar <span class="font-normal text-slate-500">(Kosongkan untuk SVG placeholder)</span></label>
                    <input type="text" name="image_url" x-model="editProduct.image_url"
                           class="w-full px-3.5 py-2 border border-slate-300 focus:border-burgundy-700 rounded-xl text-sm font-mono-tech outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Ganti dengan Upload Foto Baru <span class="font-normal text-slate-500">(Opsional)</span></label>
                    <input type="file" name="image_file" accept="image/*"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Spesifikasi & Kelengkapan</label>
                    <textarea name="description" x-model="editProduct.description" rows="3"
                              class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-burgundy-900 bg-burgundy-50 px-3.5 py-2.5 rounded-xl border border-burgundy-200">
                    <input type="checkbox" name="is_featured" value="1" x-model="editProduct.is_featured" class="rounded border-slate-300 text-burgundy-700">
                    Tandai sebagai Produk Unggulan (★ Favorit)
                </label>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm font-semibold border border-slate-300 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-burgundy-700 hover:bg-burgundy-800 text-white rounded-xl cursor-pointer">Perbarui Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
