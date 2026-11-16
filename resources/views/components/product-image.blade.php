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

    $rawImageUrl = trim((string) ($product->image_url ?? ''));
    $resolvedImageUrl = null;
    if ($rawImageUrl !== '') {
        if (str_starts_with($rawImageUrl, 'http://')
            || str_starts_with($rawImageUrl, 'https://')
            || str_starts_with($rawImageUrl, '//')
            || str_starts_with($rawImageUrl, '/')
            || str_starts_with($rawImageUrl, 'data:image/')) {
            $resolvedImageUrl = $rawImageUrl;
        } else {
            $resolvedImageUrl = asset($rawImageUrl);
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-[#FDF2F4] flex items-center justify-center ' . $class]) }}>
    @if ($resolvedImageUrl !== null)
        <img
            src="{{ $resolvedImageUrl }}"
            alt="{{ $product->name }}"
            loading="lazy"
            referrerpolicy="no-referrer"
            class="w-full h-full object-cover"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        >
        <div class="hidden w-full h-full flex-col items-center justify-center p-3 text-[#720926]">
            @include('components.partials.device-svg', ['type' => $iconType, 'label' => $product->category->name ?? 'Elektronik'])
        </div>
    @else
        <div class="w-full h-full flex flex-col items-center justify-center p-3 text-[#720926]">
            @include('components.partials.device-svg', ['type' => $iconType, 'label' => $product->category->name ?? 'Elektronik'])
        </div>
    @endif
</div>
