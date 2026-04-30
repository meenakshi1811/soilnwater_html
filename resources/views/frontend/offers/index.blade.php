@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">All Offers</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div class="row g-2 mb-3" id="offersFilterBar" data-categories='@json($categoriesForFilter)'>
        <div class="col-12 col-md-4">
            <label for="offersMarketFilterCategory" class="form-label mb-1">Category</label>
            <select id="offersMarketFilterCategory" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label for="offersMarketFilterSubcategory" class="form-label mb-1">Subcategory</label>
            <select id="offersMarketFilterSubcategory" class="form-select" disabled>
                <option value="">All subcategories</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label for="offersMarketFilterValidity" class="form-label mb-1">Validity</label>
            <select id="offersMarketFilterValidity" class="form-select">
                <option value="valid" @selected(request('validity', 'valid') === 'valid')>Valid (Not expired)</option>
                <option value="" @selected(request('validity') === '')>All</option>
                <option value="expired" @selected(request('validity') === 'expired')>Expired</option>
                <option value="expires_today" @selected(request('validity') === 'expires_today')>Expires today</option>
                <option value="no_expiry" @selected(request('validity') === 'no_expiry')>No expiry</option>
            </select>
        </div>
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
                <button type="button" class="offer-modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
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
                    <div class="offer-share-panel mt-4" id="offerSharePanel">
                        <div class="offer-share-panel-head">
                            <h4 class="offer-share-title mb-1">Share this offer</h4>
                            <p class="offer-share-subtitle mb-0">Send this deal quickly using QR or social channels.</p>
                        </div>
                        <div class="offer-share-panel-body">
                            <div class="offer-share-qr-wrap">
                                <img id="offerShareQr" src="" alt="Offer QR code" class="offer-share-qr">
                            </div>
                            <div class="offer-share-links-wrap">
                                <label for="offerShareLink" class="offer-share-link-label">Offer link</label>
                                <input type="text" id="offerShareLink" class="form-control form-control-sm offer-share-link-input" readonly>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <a id="offerShareWhatsapp" href="#" target="_blank" rel="noopener" class="btn btn-sm offer-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
                                    <a id="offerShareFacebook" href="#" target="_blank" rel="noopener" class="btn btn-sm offer-share-btn share-facebook"><i class="fa-brands fa-facebook-f me-1"></i>Facebook</a>
                                    <a id="offerShareInstagram" href="#" target="_blank" rel="noopener" class="btn btn-sm offer-share-btn share-instagram"><i class="fa-brands fa-instagram me-1"></i>Instagram</a>
                                </div>
                            </div>
                        </div>
                    </div>
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
    .offer-modal-close-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid #d5e5fa;
        background: linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
        color: #234d80;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s ease;
    }
    .offer-modal-close-btn:hover {
        background: #eaf3ff;
        color: #103963;
        transform: translateY(-1px);
    }
    .offer-share-qr {
        width: 112px;
        height: 112px;
        border: 1px solid #deebff;
        border-radius: 0.6rem;
        padding: 0.25rem;
        background: #fff;
    }
    .offer-share-panel {
        border: 1px solid #dbe9ff;
        border-radius: 0.9rem;
        background: linear-gradient(180deg, #f8fbff 0%, #f0f7ff 100%);
        overflow: hidden;
    }
    .offer-share-panel-head {
        padding: 0.8rem 0.95rem;
        border-bottom: 1px solid #dbe9ff;
    }
    .offer-share-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #173d67;
    }
    .offer-share-subtitle {
        font-size: 0.8rem;
        color: #4d6f92;
    }
    .offer-share-panel-body {
        display: flex;
        gap: 0.9rem;
        align-items: center;
        padding: 0.85rem 0.95rem 0.95rem;
    }
    .offer-share-qr-wrap {
        flex: 0 0 auto;
    }
    .offer-share-links-wrap {
        flex: 1;
        min-width: 0;
    }
    .offer-share-link-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #2f5277;
        margin-bottom: 0.25rem;
    }
    .offer-share-link-input {
        border: 1px solid #cde0fb;
        background: #fff;
        color: #1a3f66;
    }
    .offer-share-btn {
        border-radius: 999px;
        color: #fff;
        border: 0;
        font-weight: 600;
        padding-inline: 0.7rem;
    }
    .offer-share-btn.share-whatsapp { background: #19b65a; }
    .offer-share-btn.share-facebook { background: #1877f2; }
    .offer-share-btn.share-instagram { background: linear-gradient(135deg, #f58529, #dd2a7b 45%, #8134af); }

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
        min-height: 1.35em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .offer-coupon-card .card-body,
    .offer-meta-row {
        min-width: 0;
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
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
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

    .offer-empty-state-card {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        width: min(100%, 640px);
        padding: 1rem 1.1rem;
        border-radius: 0.85rem;
        border: 1px solid #d6e7ff;
        background: linear-gradient(180deg, #f7fbff 0%, #f2f8ff 100%);
        box-shadow: 0 8px 20px rgba(13, 83, 168, 0.08);
    }

    .offer-empty-state-icon {
        width: 2.15rem;
        height: 2.15rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e6f0ff;
        color: #235da8;
        flex: 0 0 2.15rem;
    }

    .offer-empty-state-title {
        font-size: 1rem;
        font-weight: 700;
        color: #12355b;
    }

    .offer-empty-state-text {
        font-size: 0.92rem;
        color: #335678;
        line-height: 1.5;
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
        .offer-share-panel-body {
            flex-direction: column;
            align-items: flex-start;
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
        const shareLinkEl = document.getElementById('offerShareLink');
        const shareQrEl = document.getElementById('offerShareQr');
        const shareWhatsappEl = document.getElementById('offerShareWhatsapp');
        const shareFacebookEl = document.getElementById('offerShareFacebook');
        const shareInstagramEl = document.getElementById('offerShareInstagram');
        const offersGrid = document.getElementById('offersGrid');
        const loadingText = document.getElementById('offersLoadingText');
        const summaryText = document.getElementById('offersSummaryText');
        const scrollSentinel = document.getElementById('offersScrollSentinel');
        const filtersWrap = document.getElementById('offersFilterBar');
        const categoryFilter = document.getElementById('offersMarketFilterCategory');
        const subcategoryFilter = document.getElementById('offersMarketFilterSubcategory');
        const validityFilter = document.getElementById('offersMarketFilterValidity');
        const categories = filtersWrap ? JSON.parse(filtersWrap.dataset.categories || '[]') : [];
        const initialSubcategoryId = '{{ (string) request('subcategory_id', '') }}';
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

        function getCurrentFilters() {
            return {
                category_id: categoryFilter ? (categoryFilter.value || '') : '',
                subcategory_id: subcategoryFilter ? (subcategoryFilter.value || '') : '',
                validity: validityFilter ? (validityFilter.value || 'valid') : 'valid',
            };
        }

        function getCategoryChildren(categoryId) {
            if (!categoryId) return [];

            for (let i = 0; i < categories.length; i++) {
                if (String(categories[i].id) === String(categoryId)) {
                    return categories[i].children || [];
                }
            }

            return [];
        }

        function populateSubcategoryFilter(categoryId) {
            if (!subcategoryFilter) return;

            const subcategories = getCategoryChildren(categoryId);
            subcategoryFilter.innerHTML = '<option value="">All subcategories</option>';

            if (!subcategories.length) {
                subcategoryFilter.disabled = true;
                return;
            }

            subcategories.forEach(function (subcategory) {
                const option = document.createElement('option');
                option.value = String(subcategory.id);
                option.textContent = subcategory.name;
                subcategoryFilter.appendChild(option);
            });
            subcategoryFilter.disabled = false;

            if (initialSubcategoryId) {
                subcategoryFilter.value = initialSubcategoryId;
            }
        }

        function buildOffersUrl(pageUrl) {
            const url = new URL(pageUrl || window.location.href, window.location.origin);
            const filters = getCurrentFilters();

            Object.entries(filters).forEach(function ([key, value]) {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            });

            return url.toString();
        }

        async function reloadOffersFromStart() {
            if (!offersGrid || isLoading) return;

            isLoading = true;
            setLoadingState(true);

            try {
                const response = await fetch(buildOffersUrl('{{ route('frontend.offers.index') }}'), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to apply filters');
                }

                const payload = await response.json();
                offersGrid.innerHTML = payload.html || '';
                nextPageUrl = payload.next_page_url || '';
                offersGrid.dataset.nextPageUrl = nextPageUrl;

                if (summaryText) {
                    if (payload.total > 0) {
                        summaryText.textContent = `Showing 1 to ${payload.loaded_to} of ${payload.total} results`;
                        summaryText.classList.remove('d-none');
                    } else {
                        summaryText.classList.add('d-none');
                    }
                }
            } catch (error) {
                console.error(error);
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        async function loadNextOffersPage() {
            if (!nextPageUrl || isLoading || !offersGrid) return;

            isLoading = true;
            setLoadingState(true);

            try {
                const response = await fetch(buildOffersUrl(nextPageUrl), {
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

        if (categoryFilter) {
            categoryFilter.addEventListener('change', function () {
                populateSubcategoryFilter(categoryFilter.value || '');
                reloadOffersFromStart();
            });
        }

        if (subcategoryFilter) {
            subcategoryFilter.addEventListener('change', function () {
                reloadOffersFromStart();
            });
        }

        if (validityFilter) {
            validityFilter.addEventListener('change', function () {
                reloadOffersFromStart();
            });
        }

        populateSubcategoryFilter(categoryFilter ? categoryFilter.value : '');

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
            const offerUrl = trigger.getAttribute('data-offer-url') || window.location.href;
            const encodedOfferUrl = encodeURIComponent(offerUrl);
            if (bannerImage) {
                imageEl.src = bannerImage;
                imageEl.classList.remove('d-none');
            } else {
                imageEl.src = '';
                imageEl.classList.add('d-none');
            }

            if (shareLinkEl) {
                shareLinkEl.value = offerUrl;
            }
            if (shareQrEl) {
                shareQrEl.src = `https://api.qrserver.com/v1/create-qr-code/?size=224x224&data=${encodedOfferUrl}`;
            }
            if (shareWhatsappEl) {
                shareWhatsappEl.href = `https://wa.me/?text=${encodeURIComponent('Check this offer: ' + offerUrl)}`;
            }
            if (shareFacebookEl) {
                shareFacebookEl.href = `https://www.facebook.com/sharer/sharer.php?u=${encodedOfferUrl}`;
            }
            if (shareInstagramEl) {
                shareInstagramEl.href = `https://www.instagram.com/?url=${encodedOfferUrl}`;
            }

        });

        if (shareToggleBtn && sharePanelEl) {
            shareToggleBtn.addEventListener('click', function () {
                sharePanelEl.classList.toggle('d-none');
            });
        }
    })();
</script>
@endpush
