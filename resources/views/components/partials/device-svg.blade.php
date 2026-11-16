@props(['type' => 'device', 'label' => 'Elektronik'])

<div class="flex flex-col items-center justify-center gap-1.5 text-center select-none">
    @if ($type === 'iphone' || $type === 'android')
        <svg class="w-10 h-10 text-[#422006]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="6" y="2" width="12" height="20" rx="2.5" fill="#FACC15" fill-opacity="0.45"/>
            <line x1="10" y1="5" x2="14" y2="5"/>
            <circle cx="12" cy="18.5" r="0.8" fill="currentColor"/>
        </svg>
    @elseif ($type === 'laptop')
        <svg class="w-10 h-10 text-[#422006]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="4" width="18" height="12" rx="1.5" fill="#FACC15" fill-opacity="0.45"/>
            <path d="M2 19h20"/>
        </svg>
    @elseif ($type === 'camera')
        <svg class="w-10 h-10 text-[#422006]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 7h3l2-2h6l2 2h3a1.5 1.5 0 0 1 1.5 1.5v10A1.5 1.5 0 0 1 20 20H4a1.5 1.5 0 0 1-1.5-1.5v-10A1.5 1.5 0 0 1 4 7z" fill="#FACC15" fill-opacity="0.45"/>
            <circle cx="12" cy="13.5" r="3.5" fill="#FFFDF5"/>
        </svg>
    @else
        <svg class="w-10 h-10 text-[#422006]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="4" width="16" height="16" rx="2" fill="#FACC15" fill-opacity="0.45"/>
            <path d="M9 9h6v6H9z"/>
        </svg>
    @endif
    <span class="font-mono-tech text-[10px] font-semibold uppercase tracking-wider text-[#B45309] leading-tight">
        {{ $label }}
    </span>
</div>
