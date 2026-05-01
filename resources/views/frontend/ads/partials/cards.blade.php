@php($adSizes = \App\Support\AdSizes::all())
@forelse ($ads as $ad)
    @php
        $sizeConfig = $adSizes[$ad->size_type] ?? null;
        $adWidth = $sizeConfig['w'] ?? 320;
        $adHeight = $sizeConfig['h'] ?? 220;
    @endphp
    <div class="col d-flex justify-content-center">
        <article
            class="card border-0 offer-coupon-card ads-market-card js-ad-modal-trigger"
            role="button"
            tabindex="0"
            style="width: {{ $adWidth }}px; max-width: 100%;"
            data-ad-title="{{ $ad->title }}"
            data-ad-meta="{{ $ad->category?->name ?? 'Uncategorized' }}{{ $ad->subcategory ? ' • '.$ad->subcategory->name : '' }} • {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}"
            data-ad-description="{{ $ad->location ? 'Location: '.$ad->location : 'Approved user ad from marketplace.' }}"
            data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
            data-ad-url="{{ route('frontend.ads.show', $ad) }}"
            data-ad-size="{{ ($sizeConfig['name'] ?? ucfirst(str_replace('_', ' ', (string) $ad->size_type))) . ' (' . $adWidth . '×' . $adHeight . ' px)' }}"
        >
            @if ($ad->final_image)
                <div class="offer-coupon-image-wrap" style="height: {{ $adHeight }}px; max-height: min({{ $adHeight }}px, 70vh); overflow: hidden;">
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="offer-coupon-image ads-market-thumb" style="height: 100%; width: 100%; object-fit: cover;">
                </div>
            @endif

            <div class="card-body d-flex flex-column gap-2 p-3">
                <h2 class="offer-card-title ads-market-title mb-0">{{ $ad->title }}</h2>
                <div class="ads-market-meta">
                    <i class="fa-regular fa-calendar-days me-1"></i>
                    {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 offer-meta-row">
                    <span class="ads-pill ads-pill-soft" title="Selected size">
                        <i class="fa-solid fa-expand me-1"></i>{{ $adWidth }}×{{ $adHeight }} px
                    </span>
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
