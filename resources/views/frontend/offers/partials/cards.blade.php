@forelse ($offers as $offer)
    <div class="col">
        <article
            class="card h-100 shadow-sm border-0 offer-coupon-card js-offer-modal-trigger"
            role="button"
            tabindex="0"
            data-bs-toggle="modal"
            data-bs-target="#offerDetailsModal"
            data-offer-title="{{ $offer->title }}"
            data-offer-discount="{{ $offer->discount_tag }}"
            data-offer-description="{{ $offer->short_description ?: 'Special marketplace offer available now.' }}"
            data-offer-coupon="{{ $offer->coupon_code ? strtoupper($offer->coupon_code) : '' }}"
            data-offer-validity="{{ $offer->valid_until?->format('d M Y') ?? 'No expiry' }}"
            data-offer-image="{{ $offer->banner_image ? asset($offer->banner_image) : '' }}"
        >
            @if ($offer->banner_image)
                <div class="offer-coupon-image-wrap">
                    <img
                        src="{{ asset($offer->banner_image) }}"
                        alt="{{ $offer->title }}"
                        class="offer-coupon-image"
                    >
                </div>
            @endif
            <div class="card-body d-flex flex-column gap-2">
                <h2 class="offer-card-title mb-1">{{ $offer->title }}</h2>
                <div class="d-flex align-items-center flex-wrap gap-2 mt-auto">
                    @if ($offer->discount_tag)
                        <span class="offer-meta-pill offer-meta-pill-discount">{{ $offer->discount_tag }}</span>
                    @endif
                    @if ($offer->coupon_code)
                        <span class="offer-meta-pill offer-meta-pill-coupon">{{ strtoupper($offer->coupon_code) }}</span>
                    @endif
                </div>
            </div>
        </article>
    </div>
@empty
    <div class="col-12 offer-empty-state">
        <div class="alert alert-info mb-0">No active offers available right now.</div>
    </div>
@endforelse
