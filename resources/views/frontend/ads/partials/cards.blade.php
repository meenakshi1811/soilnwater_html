<style>
.adx-wrapper {
    width: 100%;
    padding: 20px 30px;
    box-sizing: border-box;
}

.adx-grid {
    width: 100%;
}

.adx-item {
    margin-bottom: 12px;
}

.adx-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.adx-title {
    font-size: 15px;
    font-weight: 600;
    padding: 12px 14px;
    color: #052b60;
}

.adx-image-box {
    padding: 0 14px 14px;
}

.adx-image-inner {
    background: #f5f5f5;
    border-radius: 12px;
    overflow: hidden;
}

.adx-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

@media (max-width: 575px) {
    .adx-wrapper {
        padding: 16px;
    }

    .adx-item,
    .adx-card,
    .adx-image-inner {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

@php    
    $adSizes = \App\Support\AdSizes::all();
@endphp

<div class="adx-wrapper">
    <div class="adx-grid">

        @forelse ($ads as $ad)
            @php
                $rawSizeType = (string) ($ad->size_type ?? '');
                $normalizedSizeType = \Illuminate\Support\Str::of($rawSizeType)->lower()->replace([' ', '-'], '_')->value();

                $sizeConfig = $adSizes[$rawSizeType]
                    ?? $adSizes[$normalizedSizeType]
                    ?? collect($adSizes)->first(function ($config, $key) use ($normalizedSizeType) {
                        return \Illuminate\Support\Str::of($key)->lower()->replace([' ', '-'], '_')->value() === $normalizedSizeType;
                    });

                $adWidth = (int) ($sizeConfig['w'] ?? 320);
                $adHeight = (int) ($sizeConfig['h'] ?? 220);

                $cardWidth = $adWidth + 28;
            @endphp

            <div class="adx-item" style="width: {{ $cardWidth }}px;">
                <article
                    class="adx-card js-ad-modal-trigger"
                    role="button"
                    tabindex="0"
                    data-ad-title="{{ $ad->title }}"
                    data-ad-meta="{{ $ad->category?->name ?? 'Uncategorized' }}"
                    data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
                    data-ad-url="{{ route('frontend.ads.show', $ad) }}"
                    data-ad-id="{{ $ad->id }}"
                >
                    <div class="adx-title">
                        {{ $ad->title }}
                    </div>

                    <div class="adx-image-box">
                        <div class="adx-image-inner" style="width: {{ $adWidth }}px; height: {{ $adHeight }}px;">
                            @if ($ad->final_image)
                                <img src="{{ asset($ad->final_image) }}" class="adx-img" alt="{{ $ad->title }}">
                            @endif
                        </div>
                    </div>
                </article>
            </div>

        @empty
            <div class="adx-item text-center">
                <h4>No ads found</h4>
            </div>
        @endforelse

    </div>
</div>

<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('.adx-grid');
    if (!grid) return;

    imagesLoaded(grid, function () {
        new Masonry(grid, {
            itemSelector: '.adx-item',
            gutter: 12,
            fitWidth: false,
            horizontalOrder: false
        });
    });
});
</script>