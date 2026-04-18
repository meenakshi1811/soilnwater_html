@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">All Offers</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div class="row g-3">
        @forelse ($offers as $offer)
            <div class="col-12 col-sm-6 col-lg-3">
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
                        <div class="d-flex justify-content-center pt-3">
                            <img
                                src="{{ asset($offer->banner_image) }}"
                                alt="{{ $offer->title }}"
                                class="card-img-top"
                                style="height: 220px; width: 75%; object-fit: cover; border-radius: .5rem;"
                            >
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column gap-2">
                        <span class="badge text-bg-primary w-fit">
                            {{ $offer->coupon_code ? strtoupper($offer->coupon_code) : $offer->discount_tag }}
                        </span>
                        <h2 class="h5 mb-1">{{ $offer->title }}</h2>
                        <p class="small text-muted mb-2">{{ $offer->short_description ?: 'Special offer available now.' }}</p>
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
                <img id="offerDetailsModalImage" src="" alt="Offer image" class="img-fluid rounded mb-3 d-none" style="max-height: 220px; width: 100%; object-fit: cover;">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <span class="badge text-bg-primary" id="offerDetailsModalDiscount"></span>
                </div>
                <h3 class="h4 mb-2" id="offerDetailsModalTitle"></h3>
                <p class="text-muted mb-3" id="offerDetailsModalDescription"></p>
                <p class="mb-0"><strong>Valid until:</strong> <span id="offerDetailsModalExpiry"></span></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const offerModal = document.getElementById('offerDetailsModal');
        if (!offerModal) return;

        const titleEl = document.getElementById('offerDetailsModalTitle');
        const discountEl = document.getElementById('offerDetailsModalDiscount');
        const descriptionEl = document.getElementById('offerDetailsModalDescription');
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
            const couponCode = trigger.getAttribute('data-offer-coupon');
            discountEl.textContent = couponCode || trigger.getAttribute('data-offer-discount') || '';
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
