@forelse ($ads as $ad)
    <div class="col">
        <article
            class="card h-100 border-0 offer-coupon-card ads-market-card js-ad-modal-trigger"
            role="button"
            tabindex="0"
            data-ad-title="{{ $ad->title }}"
            data-ad-meta="{{ $ad->category?->name ?? 'Uncategorized' }}{{ $ad->subcategory ? ' • '.$ad->subcategory->name : '' }} • {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}"
            data-ad-description="{{ $ad->location ? 'Location: '.$ad->location : 'Approved user ad from marketplace.' }}"
            data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
            data-ad-url="{{ route('frontend.ads.show', $ad) }}"
        >
            @if ($ad->final_image)
                <div class="offer-coupon-image-wrap">
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="offer-coupon-image ads-market-thumb">
                </div>
            @endif

            <div class="card-body d-flex flex-column gap-2 p-3">
                <h2 class="offer-card-title ads-market-title mb-0">{{ $ad->title }}</h2>
                <div class="ads-market-meta">
                    <i class="fa-regular fa-calendar-days me-1"></i>
                    {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 offer-meta-row">
                    <span class="ads-pill ads-pill-primary">
                        <i class="fa-solid fa-layer-group me-1"></i>{{ $ad->category?->name ?? 'Uncategorized' }}
                    </span>
                    @if($ad->subcategory)
                        <span class="ads-pill ads-pill-soft">
                            <i class="fa-solid fa-tag me-1"></i>{{ $ad->subcategory->name }}
                        </span>
                    @endif
                </div>
                @if($ad->location)
                    <div class="ads-market-meta"><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $ad->location }}</div>
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
