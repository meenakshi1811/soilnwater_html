@php
    $adSizes = \App\Support\AdSizes::all();
@endphp
@forelse ($ads as $ad)
    @php
        $rawSizeType = (string) ($ad->size_type ?? '');
        $normalizedSizeType = \Illuminate\Support\Str::of($rawSizeType)->lower()->replace([' ', '-'], '_')->value();

        $sizeConfig = $adSizes[$rawSizeType]
            ?? $adSizes[$normalizedSizeType]
            ?? collect($adSizes)->first(function (array $config, string $key) use ($normalizedSizeType) {
                $normalizedKey = \Illuminate\Support\Str::of($key)->lower()->replace([' ', '-'], '_')->value();
                return $normalizedKey === $normalizedSizeType;
            });

        $adWidth = $sizeConfig['w'] ?? 320;
        $adHeight = $sizeConfig['h'] ?? 220;
        $sizeLabel = $sizeConfig['name'] ?? ucfirst(str_replace('_', ' ', (string) $ad->size_type));
        $sizeText = $sizeLabel.' ('.$adWidth.'×'.$adHeight.' px)';
        $isSquareAd = abs($adWidth - $adHeight) <= 2;
        $isBannerAd = $normalizedSizeType === 'banner';
        $isFullPageAd = in_array($normalizedSizeType, ['fullpage', 'full_page'], true);

        $maxRenderWidth = $isSquareAd ? 320 : ($isBannerAd ? 520 : ($isFullPageAd ? 420 : 300));
        $renderWidth = min($adWidth, $maxRenderWidth);

        $ratio = $adWidth > 0 ? ($adHeight / $adWidth) : 0.75;
        $renderHeight = (int) round($renderWidth * $ratio);

        if ($isSquareAd) {
            $renderWidth = $adWidth;
            $renderHeight = $adHeight;
        } else {
            $maxRenderWidth = $isBannerAd ? 520 : ($isFullPageAd ? 420 : 360);
            $renderWidth = min($adWidth, $maxRenderWidth);

            $ratio = $adWidth > 0 ? ($adHeight / $adWidth) : 0.75;
            $renderHeight = (int) round($renderWidth * $ratio);

            if ($isBannerAd) {
                $renderHeight = max(170, min(260, $renderHeight));
            } elseif ($isFullPageAd) {
                $renderHeight = max(360, min(640, $renderHeight));
            } else {
                $renderHeight = max(220, min(440, $renderHeight));
            }
        }

        if ($isFullPageAd) {
            $renderHeight = max(360, min(640, $renderHeight));
        } elseif ($isBannerAd) {
            $renderHeight = max(170, min(260, $renderHeight));
        } else {
            $renderHeight = max(180, min(380, $renderHeight));
        }

        $gridCell = 20;
        $gridColumnSpan = max(1, (int) ceil($renderWidth / $gridCell));
        $gridRowSpan = max(1, (int) ceil($renderHeight / $gridCell));
        $displayWidth = $renderWidth;
        $displayHeight = $renderHeight;
        $imageScale = $isSquareAd ? 1 : ($isBannerAd || $isFullPageAd ? 0.96 : 0.92);
    @endphp
    <div class="ads-market-grid-item">
        <article
            class="card border-0 offer-coupon-card ads-market-card {{ $isSquareAd ? 'ads-market-card--square' : 'ads-market-card--rect' }} js-ad-modal-trigger"
            role="button"
            tabindex="0"
            style="width:{{ $renderWidth }}px; --ad-w: {{ $renderWidth }}; --ad-h: {{ $renderHeight }}; --ad-display-w: {{ $displayWidth }}; --ad-display-h: {{ $displayHeight }}; --ad-grid-col-span: {{ $gridColumnSpan }}; --ad-grid-row-span: {{ $gridRowSpan }};"
            data-ad-title="{{ $ad->title }}"
            data-ad-meta="{{ $ad->category?->name ?? 'Uncategorized' }}{{ $ad->subcategory ? ' • '.$ad->subcategory->name : '' }} • Valid upto: {{ $ad->valid_until?->format('d M Y') ?? 'No Expiry' }}"
            data-ad-description="{{ $ad->location ? 'Location: '.$ad->location : 'Approved user ad from marketplace.' }}"
            data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
            data-ad-url="{{ route('frontend.ads.show', $ad) }}"
            data-ad-size="{{ $sizeText }}"
            data-ad-id="{{ $ad->id }}"
        >
            <div class="ads-market-card-head d-flex align-items-start justify-content-between gap-2">
                <h2 class="offer-card-title ads-market-title mb-0">{{ $ad->title }}</h2>
            </div>

            <div class="offer-coupon-image-wrap ads-market-image-frame {{ $isSquareAd ? 'is-square' : 'is-rect' }}" style="--ad-image-scale: {{ $imageScale }};">
                @if ($ad->final_image)
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="offer-coupon-image ads-market-thumb">
                @endif
            </div>

        </article>
    </div>
@empty
    <div class="col-12 offer-empty-state">
        <div class="offer-empty-state-card">
            <div class="offer-empty-state-content">
                <h3 class="offer-empty-state-title mb-1">No ads found</h3>
                <p class="offer-empty-state-text mb-0">Try changing filters.</p>
            </div>
        </div>
    </div>
@endforelse
