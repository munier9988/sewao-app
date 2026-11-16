@extends('layouts.backoffice')

@section('title', 'Kelola Kategori')
@section('page_title', 'Kelola Kategori')

@section('content')
<div x-data="{
    createOpen: false,
    editOpen: false,
    editCategory: { id: null, name: '' },
    openEdit(cat) {
        this.editCategory = { ...cat };
        this.editOpen = true;
    }
}">
    {{-- KPI Summary & Action Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 shadow-[2px_2px_0px_#1C1917]">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Total Kategori</div>
            <div class="font-display text-3xl font-extrabold text-[#1C1917] mt-1">{{ $categories->count() }}</div>
        </div>
        <div class="bg-[#FEF9C3] border-[1.5px] border-[#1C1917] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#422006]">Total Produk Terkait</div>
            <div class="font-display text-3xl font-extrabold text-[#422006] mt-1">{{ $totalProducts }}</div>
        </div>
        <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 flex items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Kategori Baru</div>
                <div class="text-xs text-[#57534E] mt-0.5">Kelompokkan unit sewa</div>
            </div>
            <button
                type="button"
                @click="createOpen = true"
                class="px-4 py-2.5 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition cursor-pointer shrink-0"
            >
                + Tambah
            </button>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FEF9C3] border-b-[1.5px] border-[#1C1917] text-xs font-bold uppercase tracking-wider text-[#422006]">
                        <th class="px-4 py-3">Nama Kategori</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Jumlah Produk</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-sm">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-[#FFFDF5] transition">
                            <td class="px-4 py-3.5 font-semibold text-[#1C1917]">{{ $category->name }}</td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs text-[#B45309]">{{ $category->slug }}</td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs">
                                <span class="px-2.5 py-1 rounded-md bg-[#FFFDF5] border border-[#E7E5E4] font-semibold">
                                    {{ $category->products_count }} unit
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button
                                    type="button"
                                    @click='openEdit(@js(["id" => $category->id, "name" => $category->name]))'
                                    class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-[#FEF9C3] text-[#1C1917] border-[1.5px] border-[#1C1917] rounded-lg transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('seller.categories.destroy', $category) }}" class="inline"
                                      onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?')">
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
                            <td colspan="4" class="px-4 py-10 text-center text-[#57534E]">
                                Belum ada kategori perangkat elektronik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Kategori --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1C1917]/60">
        <div @click.outside="createOpen = false" class="w-full max-w-md bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[4px_4px_0px_#1C1917]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold">Tambah Kategori</h2>
                <button type="button" @click="createOpen = false" class="text-[#57534E] hover:text-[#1C1917] font-bold">✕</button>
            </div>
            <form method="POST" action="{{ route('seller.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Kategori</label>
                    <input type="text" name="name" required maxlength="80" placeholder="Misal: Drone & Action Cam"
                           class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                    <p class="text-xs text-[#57534E] mt-1">Slug URL-friendly akan dibuat otomatis.</p>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm font-semibold border-[1.5px] border-[#E7E5E4] rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] border-[1.5px] border-[#1C1917] rounded-lg cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kategori --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1C1917]/60">
        <div @click.outside="editOpen = false" class="w-full max-w-md bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[4px_4px_0px_#1C1917]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold">Edit Kategori</h2>
                <button type="button" @click="editOpen = false" class="text-[#57534E] hover:text-[#1C1917] font-bold">✕</button>
            </div>
            <form method="POST" :action="'/seller/categories/' + editCategory.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Kategori</label>
                    <input type="text" name="name" x-model="editCategory.name" required maxlength="80"
                           class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false" class="px-4 py-2 text-sm font-semibold border-[1.5px] border-[#E7E5E4] rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] border-[1.5px] border-[#1C1917] rounded-lg cursor-pointer">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
