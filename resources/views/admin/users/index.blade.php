@extends('layouts.backoffice')

@section('title', 'Kelola Pengguna')
@section('page_title', 'Kelola Pengguna')

@section('content')
@php
    $usersMap = $users->mapWithKeys(fn ($u) => [
        $u->id => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'wa_phone' => $u->wa_phone ?? '',
        ],
    ]);
@endphp

<script>
    window.__sewaoUsersMap = @json($usersMap);
</script>

<div x-data="{
    createOpen: false,
    editOpen: false,
    editUser: { id: null, name: '', email: '', role: 'pembeli', wa_phone: '' },
    openEdit(userId) {
        const user = window.__sewaoUsersMap[userId];
        if (!user) return;
        this.editUser = { ...user, wa_phone: user.wa_phone ?? '' };
        this.editOpen = true;
    }
}">
    {{-- KPI Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pengguna</div>
            <div class="font-display text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-burgundy-50/80 border border-burgundy-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-burgundy-800">Admin</div>
            <div class="font-display text-3xl font-extrabold text-burgundy-700 mt-1">{{ $stats['admin'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Seller</div>
            <div class="font-display text-3xl font-extrabold text-burgundy-600 mt-1">{{ $stats['seller'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pembeli</div>
            <div class="font-display text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['pembeli'] }}</div>
        </div>
    </div>

    {{-- Toolbar: Search, Filter & Add Button --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-2.5 flex-1">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama, email, atau nomor WA..."
                class="flex-1 px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none"
            >
            <select name="role" class="px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm bg-white outline-none">
                <option value="">Semua Role</option>
                <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
                <option value="seller" @selected($roleFilter === 'seller')>Seller</option>
                <option value="pembeli" @selected($roleFilter === 'pembeli')>Pembeli</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || $roleFilter !== '')
                <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 text-center">
                    Reset
                </a>
            @endif
        </form>

        <button
            type="button"
            @click="createOpen = true"
            class="px-4 py-2.5 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm rounded-xl shadow-sm transition cursor-pointer shrink-0"
        >
            + Tambah Pengguna
        </button>
    </div>

    {{-- Users Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-4 py-3.5">Nama Pengguna</th>
                        <th class="px-4 py-3.5">Email</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">WhatsApp</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 font-semibold text-slate-900">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="ml-1.5 font-mono-tech text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">ANDA</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3.5">
                                @php
                                    $roleBadge = match ($user->role) {
                                        'admin' => 'bg-slate-900 text-white',
                                        'seller' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };
                                @endphp
                                <span class="font-mono-tech text-xs font-semibold uppercase px-2.5 py-1 rounded-full {{ $roleBadge }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono-tech text-xs text-slate-700">
                                {{ $user->wa_phone ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button
                                    type="button"
                                    @click="openEdit({{ $user->id }})"
                                    class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-300 hover:border-emerald-400 rounded-lg transition cursor-pointer"
                                >
                                    Edit
                                </button>

                                @if ($user->id === auth()->id())
                                    <button
                                        type="button"
                                        disabled
                                        title="Tidak dapat menghapus akun sendiri"
                                        class="px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 rounded-lg cursor-not-allowed"
                                    >
                                        Hapus
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus pengguna {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold bg-white hover:bg-red-50 text-red-700 border border-red-200 hover:border-red-400 rounded-lg transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                Belum ada pengguna yang cocok dengan pencarian Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Pengguna --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="createOpen = false" class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold text-slate-900">Tambah Pengguna Baru</h2>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm bg-white outline-none">
                            <option value="pembeli">Pembeli</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nomor WA</label>
                        <input type="text" name="wa_phone" placeholder="0812xxx / 628xxx" class="w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-sm font-semibold border border-slate-300 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-burgundy-700 hover:bg-burgundy-800 text-white rounded-xl cursor-pointer">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Pengguna --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="editOpen = false" class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl font-extrabold text-slate-900">Edit Data Pengguna</h2>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>

            <form method="POST" :action="'{{ url('/admin/users') }}/' + editUser.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editUser.name" required class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input type="email" name="email" x-model="editUser.email" required class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Role</label>
                        <select name="role" x-model="editUser.role" required class="w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm bg-white outline-none">
                            <option value="pembeli">Pembeli</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nomor WA</label>
                        <input type="text" name="wa_phone" x-model="editUser.wa_phone" placeholder="628xxx" class="w-full px-3 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password Baru <span class="font-normal text-slate-500">(Opsional)</span></label>
                    <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak diubah" class="w-full px-3.5 py-2 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm outline-none">
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
