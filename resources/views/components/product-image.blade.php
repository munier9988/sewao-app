@props([
    'product',
    'class' => 'w-full h-full',
])

@php
    $categoryName = strtolower($product->category->name ?? '');
    $iconType = 'device';
    if (str_contains($categoryName, 'iphone') || str_contains($categoryName, 'ios')) {
        $iconType = 'iphone';
    } elseif (str_contains($categoryName, 'android')) {
        $iconType = 'android';
    } elseif (str_contains($categoryName, 'laptop') || str_contains($categoryName, 'komputer')) {
        $iconType = 'laptop';
    } elseif (str_contains($categoryName, 'kamera') || str_contains($categoryName, 'multimedia')) {
        $iconType = 'camera';
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-[#FEF9C3] flex items-center justify-center ' . $class]) }}>
    @if (!empty($product->image_url))
        <img
            src="{{ $product->image_url }}"
            alt="{{ $product->name }}"
            class="w-full h-full object-cover"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        >
        <div class="hidden w-full h-full flex-col items-center justify-center p-3 text-[#422006]">
            @include('components.partials.device-svg', ['type' => $iconType, 'label' => $product->category->name ?? 'Elektronik'])
        </div>
    @else
        <div class="w-full h-full flex flex-col items-center justify-center p-3 text-[#422006]">
            @include('components.partials.device-svg', ['type' => $iconType, 'label' => $product->category->name ?? 'Elektronik'])
        </div>
    @endif
</div>
