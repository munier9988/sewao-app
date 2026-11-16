@extends('layouts.backoffice')

@section('title', 'Manajemen Pembayaran')
@section('page_title', 'Manajemen Pembayaran & Pesanan')

@section('content')
<div>
    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-burgundy-50/80 border border-burgundy-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-burgundy-800">Total Pendapatan Lunas</div>
            <div class="font-display text-2xl sm:text-3xl font-extrabold text-burgundy-700 mt-1">
                Rp {{ number_format($stats['revenue_lunas'], 0, ',', '.') }}
            </div>
        </div>
        <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-900">Menunggu Verifikasi</div>
            <div class="font-display text-3xl font-extrabold text-amber-700 mt-1">{{ $stats['pending_verification'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Transaksi Lunas</div>
            <div class="font-display text-3xl font-extrabold text-emerald-700 mt-1">{{ $stats['lunas_count'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Belum Bayar / Ditolak</div>
            <div class="font-display text-3xl font-extrabold text-slate-800 mt-1">{{ $stats['unpaid_count'] }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6 shadow-sm">
        <form method="GET" action="{{ route('seller.payments.index') }}" class="flex flex-col md:flex-row gap-2.5">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari kode order, invoice keranjang, nama pembeli, atau pengirim..."
                class="flex-1 px-3.5 py-2 border border-slate-300 focus:border-burgundy-700 rounded-xl text-sm outline-none"
            >
            <select name="payment_method" class="px-3.5 py-2 border border-slate-300 rounded-xl text-sm bg-white outline-none">
                <option value="">Semua Metode</option>
                <option value="transfer" @selected($methodFilter === 'transfer')>Transfer Bank (Keranjang)</option>
                <option value="wa_langsung" @selected($methodFilter === 'wa_langsung')>WhatsApp Langsung</option>
            </select>
            <select name="payment_status" class="px-3.5 py-2 border border-slate-300 rounded-xl text-sm bg-white outline-none">
                <option value="">Semua Status Bayar</option>
                <option value="menunggu_verifikasi" @selected($statusFilter === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                <option value="lunas" @selected($statusFilter === 'lunas')>Lunas</option>
                <option value="belum_bayar" @selected($statusFilter === 'belum_bayar')>Belum Bayar</option>
                <option value="ditolak" @selected($statusFilter === 'ditolak')>Ditolak</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || $statusFilter !== '' || $methodFilter !== '')
                <a href="{{ route('seller.payments.index') }}" class="px-3.5 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Payments & Orders Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <th class="px-4 py-3.5">Kode & Pembeli</th>
                        <th class="px-4 py-3.5">Unit & Durasi</th>
                        <th class="px-4 py-3.5">Metode & Info Transfer</th>
                        <th class="px-4 py-3.5">Total Tagihan</th>
                        <th class="px-4 py-3.5">Verifikasi Pembayaran & Status Sewa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition align-top">
                            <td class="px-4 py-4">
                                <div class="font-mono-tech text-xs font-bold text-burgundy-700">{{ $order->order_code }}</div>
                                @if ($order->checkout_group)
                                    <div class="font-mono-tech text-[11px] text-slate-500">Group: {{ $order->checkout_group }}</div>
                                @endif
                                <div class="font-bold text-slate-900 mt-1">{{ $order->buyer->name ?? '—' }}</div>
                                @if (!empty($order->buyer?->wa_phone))
                                    <a href="https://wa.me/{{ $order->buyer->wa_phone }}" target="_blank" rel="noopener"
                                       class="font-mono-tech text-xs text-emerald-700 hover:underline">
                                        WA: {{ $order->buyer->wa_phone }} ↗
                                    </a>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900">{{ $order->product->name ?? '—' }}</div>
                                <div class="font-mono-tech text-xs text-slate-500 mt-0.5">
                                    {{ $order->quantity ?? 1 }} unit × {{ $order->duration_days }} hari
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if ($order->payment_method === 'transfer')
                                    <span class="inline-block font-mono-tech text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-burgundy-50 text-burgundy-700 border border-burgundy-200">
                                        Transfer Keranjang
                                    </span>
                                    <div class="text-xs text-slate-700 mt-1">
                                        <strong>Tujuan:</strong> {{ $order->bank_name ?: '—' }}
                                    </div>
                                    <div class="text-xs text-slate-600">
                                        <strong>Pengirim:</strong> {{ $order->payment_note ?: '—' }}
                                    </div>
                                    @if (!empty($order->payment_proof))
                                        <a href="{{ $order->payment_proof }}" target="_blank" rel="noopener"
                                           class="inline-block mt-1 text-xs font-bold text-burgundy-700 hover:underline">
                                            📎 Lihat Bukti Transfer ↗
                                        </a>
                                    @endif
                                @else
                                    <span class="inline-block font-mono-tech text-[11px] font-semibold uppercase px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                        WhatsApp Langsung
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap font-mono-tech text-sm font-extrabold text-burgundy-700">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('seller.payments.update', $order) }}" class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="payment_status" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold bg-white">
                                        <option value="belum_bayar" @selected($order->payment_status === 'belum_bayar')>Belum Bayar</option>
                                        <option value="menunggu_verifikasi" @selected($order->payment_status === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                                        <option value="lunas" @selected($order->payment_status === 'lunas')>Lunas Terverifikasi</option>
                                        <option value="ditolak" @selected($order->payment_status === 'ditolak')>Ditolak</option>
                                    </select>

                                    <select name="status" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold bg-white">
                                        <option value="menunggu_wa" @selected($order->status === 'menunggu_wa')>Menunggu WA</option>
                                        <option value="disewa" @selected($order->status === 'disewa')>Aktif Disewa</option>
                                        <option value="selesai" @selected($order->status === 'selesai')>Selesai</option>
                                    </select>

                                    <button type="submit" class="px-3 py-1.5 bg-burgundy-700 hover:bg-burgundy-800 text-white text-xs font-bold rounded-lg transition cursor-pointer">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                Belum ada data transaksi pembayaran yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
