@extends('layouts.backoffice')

@section('title', 'Kelola Pengguna')
@section('page_title', 'Kelola Pengguna')

@section('content')
<div x-data="{
    createOpen: false,
    editOpen: false,
    editUser: { id: null, name: '', email: '', role: 'pembeli', wa_phone: '' },
    openEdit(user) {
        this.editUser = { ...user, wa_phone: user.wa_phone ?? '' };
        this.editOpen = true;
    }
}">
    {{-- KPI Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 shadow-[2px_2px_0px_#1C1917]">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Total Pengguna</div>
            <div class="font-display text-3xl font-extrabold text-[#1C1917] mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-[#FEF9C3] border-[1.5px] border-[#1C1917] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#422006]">Admin</div>
            <div class="font-display text-3xl font-extrabold text-[#422006] mt-1">{{ $stats['admin'] }}</div>
        </div>
        <div class="bg-white border-[1.5px] border-[#E7E5E4] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Seller</div>
            <div class="font-display text-3xl font-extrabold text-[#B45309] mt-1">{{ $stats['seller'] }}</div>
        </div>
        <div class="bg-white border-[1.5px] border-[#E7E5E4] rounded-xl p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">Pembeli</div>
            <div class="font-display text-3xl font-extrabold text-[#1C1917] mt-1">{{ $stats['pembeli'] }}</div>
        </div>
    </div>

    {{-- Toolbar: Search, Filter & Add Button --}}
    <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-2.5 flex-1">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama, email, atau nomor WA..."
                class="flex-1 px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none"
            >
            <select name="role" class="px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm bg-white outline-none">
                <option value="">Semua Role</option>
                <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
                <option value="seller" @selected($roleFilter === 'seller')>Seller</option>
                <option value="pembeli" @selected($roleFilter === 'pembeli')>Pembeli</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#1C1917] text-white text-sm font-semibold rounded-lg hover:bg-[#422006] transition cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || $roleFilter !== '')
                <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 border-[1.5px] border-[#E7E5E4] rounded-lg text-sm font-semibold text-[#57534E] hover:text-[#1C1917] text-center">
                    Reset
                </a>
            @endif
        </form>

        <button
            type="button"
            @click="createOpen = true"
            class="px-4 py-2.5 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition cursor-pointer shrink-0"
        >
            + Tambah Pengguna
        </button>
    </div>

    {{-- Users Table --}}
    <div class="bg-white border-[1.5px] border-[#1C1917] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FEF9C3] border-b-[1.5px] border-[#1C1917] text-xs font-bold uppercase tracking-wider text-[#422006]">
                        <th class="px-4 py-3">Nama Pengguna</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">WhatsApp</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-sm">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[#FFFDF5] transition">
                            <td class="px-4 py-3.5 font-semibold text-[#1C1917]">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="ml-1.5 font-mono-tech text-[10px] px-2 py-0.5 rounded bg-[#FEF9C3] border border-[#FDE68A] text-[#422006]">ANDA</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs text-[#57534E]">{{ $user->email }}</td>
                            <td class="px-4 py-3.5">
                                @php
                                    $roleBadge = match ($user->role) {
                                        'admin' => 'bg-[#1C1917] text-[#FACC15] border-[#1C1917]',
                                        'seller' => 'bg-[#FACC15] text-[#422006] border-[#1C1917]',
                                        default => 'bg-[#FFFDF5] text-[#57534E] border-[#E7E5E4]',
                                    };
                                @endphp
                                <span class="font-mono-tech text-xs font-semibold uppercase px-2.5 py-1 rounded-md border {{ $roleBadge }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs text-[#1C1917]">
                                {{ $user->wa_phone ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button
                                    type="button"
                                    @click='openEdit(@js(["id" => $user->id, "name" => $user->name, "email" => $user->email, "role" => $user->role, "wa_phone" => $user->wa_phone]))'
                                    class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-[#FEF9C3] text-[#1C1917] border-[1.5px] border-[#1C1917] rounded-lg transition cursor-pointer"
                                >
                                    Edit
                                </button>

                                @if ($user->id === auth()->id())
                                    <button
                                        type="button"
                                        disabled
                                        title="Tidak dapat menghapus akun sendiri"
                                        class="px-3 py-1.5 text-xs font-semibold bg-stone-100 text-stone-400 border-[1.5px] border-stone-200 rounded-lg cursor-not-allowed"
                                    >
                                        Hapus
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus pengguna {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-red-50 text-red-700 border-[1.5px] border-red-300 hover:border-red-600 rounded-lg transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-[#57534E]">
                                Belum ada pengguna yang cocok dengan pencarian Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Pengguna --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1C1917]/60">
        <div @click.outside="createOpen = false" class="w-full max-w-md bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[4px_4px_0px_#1C1917]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold">Tambah Pengguna Baru</h2>
                <button type="button" @click="createOpen = false" class="text-[#57534E] hover:text-[#1C1917] font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm bg-white outline-none">
                            <option value="pembeli">Pembeli</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nomor WA</label>
                        <input type="text" name="wa_phone" placeholder="0812xxx / 628xxx" class="w-full px-3 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm font-semibold border-[1.5px] border-[#E7E5E4] rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] border-[1.5px] border-[#1C1917] rounded-lg cursor-pointer">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Pengguna --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1C1917]/60">
        <div @click.outside="editOpen = false" class="w-full max-w-md bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[4px_4px_0px_#1C1917]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold">Edit Data Pengguna</h2>
                <button type="button" @click="editOpen = false" class="text-[#57534E] hover:text-[#1C1917] font-bold">✕</button>
            </div>

            <form method="POST" :action="'/admin/users/' + editUser.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editUser.name" required class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input type="email" name="email" x-model="editUser.email" required class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Role</label>
                        <select name="role" x-model="editUser.role" required class="w-full px-3 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm bg-white outline-none">
                            <option value="pembeli">Pembeli</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nomor WA</label>
                        <input type="text" name="wa_phone" x-model="editUser.wa_phone" placeholder="628xxx" class="w-full px-3 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password Baru <span class="font-normal text-[#57534E]">(Opsional)</span></label>
                    <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak diubah" class="w-full px-3.5 py-2 border-[1.5px] border-[#E7E5E4] focus:border-[#1C1917] rounded-lg text-sm outline-none">
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
