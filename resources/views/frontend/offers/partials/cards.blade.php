@forelse ($offers as $offer)
    <article
        class="om-card js-offer-modal-trigger"
        role="button"
        tabindex="0"
        data-bs-toggle="modal"
        data-bs-target="#offerDetailsModal"
        data-offer-id="{{ $offer->id }}"
        data-offer-title="{{ $offer->title }}"
        data-offer-discount="{{ $offer->discount_tag }}"
        data-offer-description="{{ $offer->short_description ?: 'Special marketplace offer available now.' }}"
        data-offer-coupon="{{ $offer->coupon_code ? strtoupper($offer->coupon_code) : '' }}"
        data-offer-validity="{{ $offer->valid_until?->format('d M Y') ?? 'No expiry' }}"
        data-offer-image="{{ $offer->banner_image ? asset($offer->banner_image) : '' }}"
        data-offer-url="{{ $offer->shareUrl() }}"
    >
        <div class="om-card__media">
            @if ($offer->banner_image)
                <img
                    src="{{ asset($offer->banner_image) }}"
                    alt="{{ $offer->title }}"
                    class="om-card__image"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <span class="om-card__placeholder" aria-hidden="true"><i class="fa-solid fa-tags"></i></span>
            @endif
        </div>
        <div class="om-card__body">
            @if ($offer->discount_tag)
                <span class="om-card__deal">{{ $offer->discount_tag }}</span>
            @endif
            <h2 class="om-card__title">{{ $offer->title }}</h2>
            @if ($offer->coupon_code)
                <span class="om-card__coupon"><i class="fa-solid fa-ticket" aria-hidden="true"></i><span>{{ strtoupper($offer->coupon_code) }}</span></span>
            @endif
            <span class="om-card__link">View offer</span>
        </div>
    </article>
@empty
    <div class="offer-empty-state">
        <div class="offer-empty-state-card">
            <div class="offer-empty-state-icon" aria-hidden="true">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div class="offer-empty-state-content">
                <h3 class="offer-empty-state-title mb-1">No offers found</h3>
                <p class="offer-empty-state-text mb-0">
                    We couldn’t find any matching offers at the moment. Try changing category, subcategory, or validity filters.
                </p>
            </div>
        </div>
    </div>
@endforelse
