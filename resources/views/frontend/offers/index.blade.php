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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable offer-details-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="offerDetailsModalLabel">Offer Details</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img id="offerDetailsModalImage" src="" alt="Offer image" class="d-none offer-details-modal-image">
                <div class="offer-details-content">
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
</div>
@endsection

@push('styles')
<style>
    .offer-details-modal .modal-dialog.offer-details-dialog {
        width: min(100% - 1.5rem, 640px);
        max-width: 640px;
        margin-inline: auto;
    }

    .offer-details-modal .modal-content {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 22px 50px rgba(13, 44, 94, 0.18);
        background: linear-gradient(180deg, #f7fbff 0%, #ffffff 26%, #ffffff 100%);
    }

    .offer-details-modal .modal-header {
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid #e6effa;
        background: rgba(255, 255, 255, 0.86);
        backdrop-filter: blur(4px);
    }

    .offer-details-modal .modal-title {
        font-weight: 700;
        color: #12355b;
        letter-spacing: 0.2px;
    }

    .offer-details-modal .modal-body {
        overflow-y: auto;
    }

    .offer-coupon-image-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 768 / 1080;
        height: auto;
        background: #f5f9ff;
        padding: 0;
        overflow: hidden;
    }

    .offer-coupon-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .offer-card-title {
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.35;
    }

    .offer-meta-pill {
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border-radius: 999px;
        font-weight: 600;
        letter-spacing: 0.15px;
        line-height: 1;
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .offer-meta-pill-discount {
        background-color: #e9f2ff;
        color: #0d6efd;
    }

    .offer-meta-pill-coupon {
        border: 1px dashed #9cc8ff;
        background-color: #edf5ff;
        color: #0c4f93;
    }

    .offer-details-modal-image {
        display: block;
        width: 100%;
        height: auto;
        max-height: none;
        object-fit: contain;
        object-position: center;
        margin: 0;
        background: transparent;
    }

    .offer-details-content {
        padding: 1.1rem 1.25rem 1.3rem;
    }

    .offer-details-content #offerDetailsModalTitle {
        color: #0e3157;
        font-weight: 700;
        line-height: 1.25;
    }

    .offer-details-content #offerDetailsModalDescription {
        font-size: 0.97rem;
        line-height: 1.6;
    }

    .offer-details-content .badge {
        border-radius: 999px;
        padding: 0.45rem 0.68rem;
        font-size: 0.74rem;
        letter-spacing: 0.2px;
    }

    .offer-details-content .coupon-code {
        border: 1px dashed #9dc3ef;
        background-color: #edf5ff;
        color: #0c4f93;
        border-radius: 999px;
        padding: 0.38rem 0.7rem;
        font-size: 0.72rem;
        font-weight: 700;
    }

    @media (min-width: 768px) {
        .offer-details-content {
            padding: 1.35rem 1.5rem 1.55rem;
        }
    }

    @media (max-width: 575.98px) {
        .offer-details-modal .modal-dialog.offer-details-dialog {
            width: calc(100% - 1rem);
        }

        .offer-details-modal-image {
            height: auto;
            max-height: none;
        }
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
