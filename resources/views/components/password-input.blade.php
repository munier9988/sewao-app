@props([
    'id' => 'password',
    'name' => 'password',
    'required' => true,
    'placeholder' => '',
])

<div x-data="{ show: false }" class="relative">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        :type="show ? 'text' : 'password'"
        @if ($required) required @endif
        @if ($placeholder !== '') placeholder="{{ $placeholder }}" @endif
        class="w-full pl-4 pr-12 py-2.5 border border-slate-300 focus:border-burgundy-700 rounded-xl outline-none text-base"
    >
    <button
        type="button"
        @click="show = !show"
        :aria-label="show ? 'Sembunyikan password' : 'Lihat password'"
        :title="show ? 'Sembunyikan password' : 'Lihat password'"
        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-500 hover:text-burgundy-700 transition cursor-pointer"
    >
        {{-- Eye icon (when hidden) --}}
        <svg x-show="!show" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
        {{-- Eye-off icon (when visible) --}}
        <svg x-show="show" style="display: none;" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a21.8 21.8 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 5c6.5 0 10 7 10 7a21.8 21.8 0 0 1-2.16 3.19"/>
            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
            <line x1="2" y1="2" x2="22" y2="22"/>
        </svg>
    </button>
</div>
