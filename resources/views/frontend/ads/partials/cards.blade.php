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

        if ($isSquareAd) {
            $renderWidth = $adWidth;
            $renderHeight = $adHeight;
        } else {
            $renderWidth = 300;
            $ratio = $adWidth > 0 ? ($adHeight / $adWidth) : 0.75;
            $renderHeight = (int) round($renderWidth * $ratio);
            $renderHeight = max(180, min(460, $renderHeight));
        }

        $gridCell = 20;
        $gridColumnSpan = max(1, (int) ceil($renderWidth / $gridCell));
        $gridRowSpan = max(1, (int) ceil($renderHeight / $gridCell));
        $displayWidth = max(180, (int) round($renderWidth * 0.52));
        $displayHeight = max(180, (int) round($renderHeight * 0.52));
        $imageScale = $isSquareAd ? 1 : 0.92;
    @endphp
    <div class="ads-market-grid-item">
        <article
            class="card border-0 offer-coupon-card ads-market-card {{ $isSquareAd ? 'ads-market-card--square' : 'ads-market-card--rect' }} js-ad-modal-trigger"
            role="button"
            tabindex="0"
            style="width:{{ $renderWidth }}px; height:{{ $renderHeight }}px; --ad-display-w: {{ $displayWidth }}; --ad-display-h: {{ $displayHeight }}; --ad-grid-col-span: {{ $gridColumnSpan }}; --ad-grid-row-span: {{ $gridRowSpan }};"
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

            <div class="card-body d-flex flex-column gap-2 p-3">
                <div class="ads-market-meta">
                    <i class="fa-regular fa-calendar-days me-1"></i>
                    Valid upto: {{ $ad->valid_until?->format('d M Y') ?? 'No Expiry' }}
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 offer-meta-row">
                    <span class="ads-pill ads-pill-primary ads-ellipsis" title="{{ $ad->category?->name ?? 'Uncategorized' }}">
                        <i class="fa-solid fa-layer-group me-1"></i>{{ $ad->category?->name ?? 'Uncategorized' }}
                    </span>
                    @if($ad->subcategory)
                        <span class="ads-pill ads-pill-soft ads-ellipsis" title="{{ $ad->subcategory->name }}">
                            <i class="fa-solid fa-tag me-1"></i>{{ $ad->subcategory->name }}
                        </span>
                    @endif
                </div>
                @if($ad->location)
                    <div class="ads-market-meta ads-location-ellipsis" title="{{ $ad->location }}"><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $ad->location }}</div>
                @endif
                <div class="mt-1">
                    <button type="button" class="btn btn-sm ads-view-btn">View Details <i class="fa-solid fa-arrow-right ms-1"></i></button>
                </div>
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
