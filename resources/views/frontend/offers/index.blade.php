@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">All Offers</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div
        id="offersGrid"
        class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 row-cols-xl-5 row-cols-xxl-6 g-3"
        data-next-page-url="{{ $offers->nextPageUrl() }}"
    >
        @include('frontend.offers.partials.cards', ['offers' => $offers])
    </div>

    <div class="mt-4 offer-pagination-wrap" id="offersPaginationState">
        @if ($offers->total() > 0)
            <p class="offer-pagination-summary mb-0" id="offersSummaryText">
                Showing {{ $offers->firstItem() }} to {{ $offers->lastItem() }} of {{ $offers->total() }} results
            </p>
        @endif
        <p class="offer-pagination-loading mb-0 d-none" id="offersLoadingText">Loading more offers…</p>
    </div>

    <div id="offersScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
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


    .offer-pagination-wrap {
        width: fit-content;
        max-width: 100%;
        min-height: 1.5rem;
    }

    .offer-pagination-summary {
        margin-top: 0.25rem;
        font-size: 1rem;
        color: #0f2742;
    }

    .offer-pagination-loading {
        margin-top: 0.5rem;
        font-size: 0.95rem;
        color: #66788a;
    }

    .offer-scroll-sentinel {
        width: 100%;
        height: 1px;
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
        const offersGrid = document.getElementById('offersGrid');
        const loadingText = document.getElementById('offersLoadingText');
        const summaryText = document.getElementById('offersSummaryText');
        const scrollSentinel = document.getElementById('offersScrollSentinel');
        let nextPageUrl = offersGrid ? offersGrid.dataset.nextPageUrl || '' : '';
        let isLoading = false;

        document.addEventListener('keydown', function (event) {
            const trigger = event.target.closest('.offer-coupon-card.js-offer-modal-trigger');
            if (!trigger) return;

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                trigger.click();
            }
        });

        function setLoadingState(show) {
            if (!loadingText) return;
            loadingText.classList.toggle('d-none', !show);
        }

        async function loadNextOffersPage() {
            if (!nextPageUrl || isLoading || !offersGrid) return;

            isLoading = true;
            setLoadingState(true);

            try {
                const response = await fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load more offers');
                }

                const payload = await response.json();

                const emptyState = offersGrid.querySelector('.offer-empty-state');
                if (emptyState) {
                    emptyState.remove();
                }

                if (payload.html) {
                    offersGrid.insertAdjacentHTML('beforeend', payload.html);
                }

                nextPageUrl = payload.next_page_url || '';
                offersGrid.dataset.nextPageUrl = nextPageUrl;

                if (summaryText && payload.total > 0) {
                    summaryText.textContent = `Showing 1 to ${payload.loaded_to} of ${payload.total} results`;
                }
            } catch (error) {
                console.error(error);
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        if (scrollSentinel && offersGrid && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadNextOffersPage();
                    }
                });
            }, {
                rootMargin: '300px 0px',
            });

            observer.observe(scrollSentinel);
        } else {
            window.addEventListener('scroll', function () {
                if (!nextPageUrl || isLoading || !scrollSentinel) return;

                const sentinelTop = scrollSentinel.getBoundingClientRect().top;
                if (sentinelTop <= window.innerHeight + 300) {
                    loadNextOffersPage();
                }
            }, { passive: true });
        }

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
