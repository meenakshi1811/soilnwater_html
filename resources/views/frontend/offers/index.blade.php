@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">All Offers</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 row-cols-xl-5 row-cols-xxl-6 g-3">
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
                        <div>
                            <span class="badge text-bg-primary w-fit">
                                {{ $offer->discount_tag }}
                            </span>
                        </div>
                        <h2 class="h5 mb-1">{{ $offer->title }}</h2>
                        <p class="small text-muted mb-2 offer-short-description">{{ $offer->short_description ?: 'Special offer available now.' }}</p>
                        @if ($offer->coupon_code)
                            <span class="coupon-code mb-0 offer-meta-pill offer-meta-pill-coupon mt-auto align-self-start">{{ strtoupper($offer->coupon_code) }}</span>
                        @endif
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">No active offers available right now.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $offers->links() }}
    </div>
</div>

<div class="modal fade offer-details-modal" id="offerDetailsModal" tabindex="-1" aria-labelledby="offerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="offerDetailsModalLabel">Offer Details</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="offerDetailsModalImage" src="" alt="Offer image" class="img-fluid rounded mb-3 d-none offer-details-modal-image">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <span class="badge text-bg-primary" id="offerDetailsModalDiscount"></span>
                    <span class="coupon-code mb-0 d-none" id="offerDetailsModalCoupon"></span>
                </div>
                <h3 class="h4 mb-2" id="offerDetailsModalTitle"></h3>
                <p class="text-muted mb-3" id="offerDetailsModalDescription"></p>
                <p class="mb-0"><strong>Valid until:</strong> <span id="offerDetailsModalExpiry"></span></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .offer-coupon-image-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 200px;
        background: #f5f9ff;
        padding: 10px;
        overflow: hidden;
    }

    .offer-coupon-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    .offer-meta-pill {
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border-radius: 10px;
        font-weight: 700;
        letter-spacing: 0.2px;
        line-height: 1;
        font-size: 0.95rem;
    }

    .offer-meta-pill-discount {
        font-size: 0.8rem;
        height: 30px;
        padding: 0 10px;
        border-radius: 8px;
        white-space: nowrap;
    }

    .offer-meta-pill-coupon {
        min-width: 150px;
        white-space: nowrap;
        border: 1px dashed #9cc8ff;
        background-color: #edf5ff;
        color: #0c4f93;
    }

    .offer-short-description {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .offer-details-modal-image {
        width: 100%;
        height: 280px;
        object-fit: contain;
        object-position: center;
        background: #f5f9ff;
        padding: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const offerModal = document.getElementById('offerDetailsModal');
        if (!offerModal) return;

        const titleEl = document.getElementById('offerDetailsModalTitle');
        const discountEl = document.getElementById('offerDetailsModalDiscount');
        const descriptionEl = document.getElementById('offerDetailsModalDescription');
        const couponEl = document.getElementById('offerDetailsModalCoupon');
        const expiryEl = document.getElementById('offerDetailsModalExpiry');
        const imageEl = document.getElementById('offerDetailsModalImage');
        const offerTriggers = document.querySelectorAll('.offer-coupon-card.js-offer-modal-trigger');

        offerTriggers.forEach(function (trigger) {
            trigger.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    trigger.click();
                }
            });
        });

        offerModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger || !trigger.classList.contains('js-offer-modal-trigger')) return;

            titleEl.textContent = trigger.getAttribute('data-offer-title') || 'Offer Details';
            const couponCode = trigger.getAttribute('data-offer-coupon') || '';
            const discountText = trigger.getAttribute('data-offer-discount') || '';

            if (couponCode) {
                couponEl.textContent = couponCode;
                couponEl.classList.remove('d-none');
            } else {
                couponEl.textContent = '';
                couponEl.classList.add('d-none');
            }

            if (discountText) {
                discountEl.textContent = discountText;
                discountEl.classList.remove('d-none');
            } else {
                discountEl.textContent = '';
                discountEl.classList.add('d-none');
            }
            descriptionEl.textContent = trigger.getAttribute('data-offer-description') || '';
            expiryEl.textContent = trigger.getAttribute('data-offer-validity') || 'No expiry';

            const bannerImage = trigger.getAttribute('data-offer-image');
            if (bannerImage) {
                imageEl.src = bannerImage;
                imageEl.classList.remove('d-none');
            } else {
                imageEl.src = '';
                imageEl.classList.add('d-none');
            }
        });
    })();
</script>
@endpush
