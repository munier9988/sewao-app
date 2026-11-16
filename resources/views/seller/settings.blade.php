@extends('layouts.backoffice')

@section('title', 'Pengaturan WhatsApp')
@section('page_title', 'Pengaturan WhatsApp Toko')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    {{-- Form Update Nomor WhatsApp --}}
    <div class="lg:col-span-2 bg-white border-[1.5px] border-[#1C1917] rounded-xl p-6 shadow-[2px_2px_0px_#1C1917] self-start">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg font-extrabold">Nomor Penerima Pesanan</h2>
            <span class="font-mono-tech text-[11px] px-2.5 py-1 rounded bg-[#FEF9C3] border border-[#FDE68A] text-[#422006] font-semibold">
                FORMAT 628xxx
            </span>
        </div>

        <p class="text-sm text-[#57534E] mb-5">
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
                    class="w-full px-3.5 py-2.5 border-[1.5px] border-[#1C1917] rounded-lg text-sm font-mono-tech outline-none focus:bg-[#FFFDF5]"
                >
                <p class="text-xs text-[#57534E] mt-1.5">
                    Input seperti <span class="font-mono-tech">0812...</span> atau <span class="font-mono-tech">+62 812...</span> otomatis dinormalisasi menjadi <span class="font-mono-tech font-semibold">62812...</span>.
                </p>
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-[#FACC15] hover:bg-[#EAB308] text-[#422006] font-bold text-sm border-[1.5px] border-[#1C1917] rounded-lg shadow-[2px_2px_0px_#1C1917] transition cursor-pointer">
                Simpan Nomor WhatsApp
            </button>
        </form>

        @if (!empty($seller->wa_phone))
            <div class="mt-5 pt-4 border-t border-[#E7E5E4]">
                <div class="text-xs font-semibold text-[#57534E] mb-1.5">Pratinjau Tautan Aktif:</div>
                <a href="https://wa.me/{{ $seller->wa_phone }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-[#15803D] text-white font-mono-tech text-xs font-semibold hover:opacity-90 transition">
                    <span>wa.me/{{ $seller->wa_phone }}</span>
                    <span>↗</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Daftar Pesanan Masuk Terbaru (Read-only sesuai PRD 1.2) --}}
    <div class="lg:col-span-3 bg-white border-[1.5px] border-[#1C1917] rounded-xl overflow-hidden">
        <div class="px-5 py-4 bg-[#FEF9C3] border-b-[1.5px] border-[#1C1917] flex items-center justify-between">
            <div>
                <h2 class="font-display text-lg font-extrabold text-[#422006]">Daftar Pesanan Masuk Terbaru</h2>
                <p class="text-xs text-[#57534E]">Riwayat klaim sewa unit yang diarahkan ke WhatsApp toko.</p>
            </div>
            <span class="font-mono-tech text-xs font-bold px-2.5 py-1 rounded bg-white border border-[#1C1917]">
                {{ $incomingOrders->count() }} terbaru
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E7E5E4] text-xs font-bold uppercase tracking-wider text-[#57534E]">
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Pembeli</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-sm">
                    @forelse ($incomingOrders as $order)
                        <tr class="hover:bg-[#FFFDF5]">
                            <td class="px-4 py-3 font-mono-tech text-xs font-semibold text-[#B45309]">{{ $order->order_code }}</td>
                            <td class="px-4 py-3 font-medium text-[#1C1917]">{{ $order->buyer->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-[#57534E]">{{ $order->product->name ?? '—' }} ({{ $order->duration_days }} hr)</td>
                            <td class="px-4 py-3 font-mono-tech text-xs font-bold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono-tech text-[11px] uppercase px-2 py-0.5 rounded border {{ $order->status === 'disewa' ? 'bg-emerald-50 border-emerald-300 text-[#15803D]' : 'bg-[#FEF9C3] border-[#FDE68A] text-[#422006]' }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-[#57534E]">
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
