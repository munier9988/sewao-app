@extends('layouts.backoffice')

@section('title', 'Pengaturan WhatsApp')
@section('page_title', 'Pengaturan WhatsApp Toko')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    {{-- Form Update Nomor WhatsApp --}}
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm self-start">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg font-extrabold text-slate-900">Nomor Penerima Pesanan</h2>
            <span class="font-mono-tech text-[11px] px-2.5 py-1 rounded-full bg-burgundy-50 border border-burgundy-200 text-burgundy-800 font-semibold">
                FORMAT 628xxx
            </span>
        </div>

        <p class="text-sm text-slate-600 mb-5">
            Pembeli akan diarahkan langsung ke tautan WhatsApp ini ketika menekan tombol <strong>Sewa via WhatsApp</strong>.
        </p>

        <form method="POST" action="{{ route('seller.settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="wa_phone" class="block text-sm font-semibold mb-1">Nomor WhatsApp Toko</label>
                <input
                    id="wa_phone"
                    name="wa_phone"
                    type="text"
                    required
                    value="{{ old('wa_phone', $seller->wa_phone) }}"
                    placeholder="081234567890 atau 6281234567890"
                    class="w-full px-3.5 py-2.5 border border-slate-300 focus:border-emerald-600 rounded-xl text-sm font-mono-tech outline-none"
                >
                <p class="text-xs text-slate-500 mt-1.5">
                    Input seperti <span class="font-mono-tech">0812...</span> atau <span class="font-mono-tech">+62 812...</span> otomatis dinormalisasi menjadi <span class="font-mono-tech font-semibold">62812...</span>.
                </p>
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-burgundy-700 hover:bg-burgundy-800 text-white font-bold text-sm rounded-xl shadow-sm transition cursor-pointer">
                Simpan Nomor WhatsApp
            </button>
        </form>

        @if (!empty($seller->wa_phone))
            <div class="mt-5 pt-4 border-t border-slate-200">
                <div class="text-xs font-semibold text-slate-500 mb-1.5">Pratinjau Tautan Aktif:</div>
                <a href="https://wa.me/{{ $seller->wa_phone }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-burgundy-800 text-white font-mono-tech text-xs font-semibold hover:bg-burgundy-900 transition">
                    <span>wa.me/{{ $seller->wa_phone }}</span>
                    <span>↗</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Daftar Pesanan Masuk Terbaru (Read-only sesuai PRD 1.2) --}}
    <div class="lg:col-span-3 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-display text-lg font-extrabold text-slate-900">Daftar Pesanan Masuk Terbaru</h2>
                <p class="text-xs text-slate-500">Riwayat klaim sewa unit yang diarahkan ke WhatsApp toko.</p>
            </div>
            <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded-full bg-burgundy-50 border border-burgundy-200 text-burgundy-800">
                {{ $incomingOrders->count() }} terbaru
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Pembeli</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse ($incomingOrders as $order)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono-tech text-xs font-semibold text-burgundy-700">{{ $order->order_code }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $order->buyer->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $order->product->name ?? '—' }} ({{ $order->duration_days }} hr)</td>
                            <td class="px-4 py-3 font-mono-tech text-xs font-bold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono-tech text-[11px] uppercase px-2.5 py-0.5 rounded-full border {{ $order->status === 'disewa' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                Belum ada pesanan sewa masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
