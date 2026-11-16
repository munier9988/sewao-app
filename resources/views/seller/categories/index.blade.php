@extends('layouts.backoffice')

@section('title', 'Kelola Kategori')
@section('page_title', 'Kelola Kategori')

@section('content')
@php
    $categoriesMap = $categories->mapWithKeys(fn ($c) => [
        $c->id => [
            'id' => $c->id,
            'name' => $c->name,
        ],
    ]);
@endphp

<script>
    window.__sewaoCategoriesMap = @json($categoriesMap);
</script>

<div x-data="{
    createOpen: false,
    editOpen: false,
    editCategory: { id: null, name: '' },
    openEdit(categoryId) {
        const cat = window.__sewaoCategoriesMap[categoryId];
        if (!cat) return;
        this.editCategory = { ...cat };
        this.editOpen = true;
    }
}">
    {{-- KPI Summary & Action Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Kategori</div>
            <div class="font-display text-3xl font-extrabold text-slate-900 mt-1">{{ $categories->count() }}</div>
        </div>
        <div class="bg-burgundy-50/80 border border-burgundy-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-burgundy-800">Total Produk Terkait</div>
            <div class="font-display text-3xl font-extrabold text-burgundy-700 mt-1">{{ $totalProducts }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori Baru</div>
                <div class="text-xs text-slate-500 mt-0.5">Kelompokkan unit sewa</div>
            </div>
            <button
                type="button"
                @click="createOpen = true"
                class="px-4 py-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm rounded-xl shadow-sm transition cursor-pointer shrink-0"
            >
                + Tambah
            </button>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-4 py-3.5">Nama Kategori</th>
                        <th class="px-4 py-3.5">Slug</th>
                        <th class="px-4 py-3.5">Jumlah Produk</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $category->name }}</td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs text-burgundy-700">{{ $category->slug }}</td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 font-semibold text-slate-700">
                                    {{ $category->products_count }} unit
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button
                                    type="button"
                                    @click="openEdit({{ $category->id }})"
                                    class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-300 hover:border-emerald-400 rounded-lg transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('seller.categories.destroy', $category) }}" class="inline"
                                      onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?')">
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
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                                Belum ada kategori perangkat elektronik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Kategori --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="createOpen = false" class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold text-slate-900">Tambah Kategori</h2>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>
            <form method="POST" action="{{ route('seller.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Kategori</label>
                    <input type="text" name="name" required maxlength="80" placeholder="Misal: Drone & Action Cam"
                           class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                    <p class="text-xs text-slate-500 mt-1">Slug URL-friendly akan dibuat otomatis.</p>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm font-semibold border border-slate-300 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-burgundy-700 hover:bg-burgundy-800 text-white rounded-xl cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kategori --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="editOpen = false" class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold text-slate-900">Edit Kategori</h2>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>
            <form method="POST" :action="'{{ url('/seller/categories') }}/' + editCategory.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Kategori</label>
                    <input type="text" name="name" x-model="editCategory.name" required maxlength="80"
                           class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm font-semibold border border-slate-300 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-burgundy-700 hover:bg-burgundy-800 text-white rounded-xl cursor-pointer">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
