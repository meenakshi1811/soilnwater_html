@extends('frontend.layouts.app')

@section('content')
@php
  $marketBannerImage = data_get($homepageSetting ?? null, 'offers_market_banner_image');
@endphp

<div class="vendors-page offers-market-page">
    @include('frontend.marketplace.partials.breadcrumb', ['module' => 'offers'])

    @if ($marketBannerImage)
        <img src="{{ asset($marketBannerImage) }}" alt="Offers market" class="om-banner">
    @endif

    <section class="om-panel">
        <header class="om-head">
            <h1 class="om-head__title">Offers &amp; Discounts</h1>
            <p class="om-head__lead">Coupons and deals from businesses on SoilnWater</p>
        </header>

        <div class="om-filters" id="offersFilterBar" data-categories='@json($categoriesForFilter)'>
            <div class="om-filter">
                <label for="offersMarketFilterCategory" class="om-filter__label">Category</label>
                <select id="offersMarketFilterCategory" class="form-select om-filter__select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="om-filter">
                <label for="offersMarketFilterSubcategory" class="om-filter__label">Subcategory</label>
                <select id="offersMarketFilterSubcategory" class="form-select om-filter__select" disabled>
                    <option value="">All subcategories</option>
                </select>
            </div>
            <div class="om-filter">
                <label for="offersMarketFilterValidity" class="om-filter__label">Validity</label>
                <select id="offersMarketFilterValidity" class="form-select om-filter__select">
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
            class="row row-cols-2 row-cols-lg-4 row-cols-xl-5 row-cols-xxl-6 g-3"
            data-next-page-url="{{ $offers->nextPageUrl() }}"
        >
            @include('frontend.offers.partials.cards', ['offers' => $offers])
        </div>

        <div class="om-pagination offer-pagination-wrap" id="offersPaginationState">
            @if ($offers->total() > 0)
                <p class="offer-pagination-summary mb-0" id="offersSummaryText">
                    Showing {{ $offers->firstItem() }} to {{ $offers->lastItem() }} of {{ $offers->total() }} results
                </p>
            @endif
            <p class="offer-pagination-loading mb-0 d-none" id="offersLoadingText">Loading more offers…</p>
        </div>

        <div id="offersScrollSentinel" class="offer-scroll-sentinel" aria-hidden="true"></div>
    </section>
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
                    <p class="text-muted mb-3 offer-details-description" id="offerDetailsModalDescription"></p>
                    {{-- Phones: long details fold to 4 lines --}}
                    <button type="button" class="offer-readmore d-none" id="offerDetailsReadMore" aria-controls="offerDetailsModalDescription" aria-expanded="false">Read more</button>
                    <p class="mb-0" id="offerDetailsModalValidityRow"><strong>Valid until:</strong> <span id="offerDetailsModalExpiry"></span></p>
                    <div class="offer-login-message d-none" id="offerLoginMessageBox" role="status" aria-live="polite">
                        <div class="offer-login-message-icon"><i class="fa-solid fa-lock"></i></div>
                        <div>
                            <h4 class="offer-login-message-title mb-1">You are not logged in</h4>
                            <p class="offer-login-message-text mb-2">Please log in to view this offer details and validity.</p>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Login to continue</a>
                        </div>
                    </div>
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
                                    <button type="button" id="offerShareCopyBtn" class="btn btn-sm btn-outline-secondary">Copy link</button>
                                    <a id="offerShareWhatsapp" href="#" target="_blank" rel="noopener" class="btn btn-sm offer-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
                                    <a id="offerShareFacebook" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm offer-share-btn share-facebook"><i class="fa-brands fa-facebook-f me-1"></i>Facebook</a>
                                    <button type="button" id="offerShareInstagram" class="btn btn-sm offer-share-btn share-instagram"><i class="fa-brands fa-instagram me-1"></i>Instagram</button>
                                </div>
                                <p class="small text-muted mt-2 mb-0">On Android, pick <strong>Instagram</strong> from the share list for Story or DM. The link is copied automatically.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 border-top pt-3 d-none" id="offerReportActions">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="openOfferReportPopupBtn">
                            <i class="fa-regular fa-flag me-1"></i> Report this offer
                        </button>
                    </div>
                    <div class="mt-3 d-none" id="offerReportPopupWrap">
                        <div class="ad-report-popup border rounded-3 p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="h6 mb-0"><i class="fa-regular fa-flag me-1 text-danger"></i>Report this offer</h5>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0" id="closeOfferReportPopupBtn">Close</button>
                            </div>
                            @auth
                                <form id="offerReportForm" method="POST" action="#">
                                    @csrf
                                    <textarea name="reason" class="form-control form-control-sm mb-2 ad-report-textarea" rows="3" placeholder="Enter reason for reporting this offer" required></textarea>
                                    <button type="submit" class="btn btn-sm btn-danger">Submit Report</button>
                                </form>
                            @else
                                <p class="mb-0 small text-muted">Please <a href="{{ route('login') }}">login</a> to report this offer.</p>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            {{-- Phones: the share options in a bar at the bottom of the popup (the panel above is for wider screens) --}}
            <div class="modal-footer offer-share-bar d-none" id="offerShareBar">
                <a id="offerBarWhatsapp" href="#" target="_blank" rel="noopener" class="offer-share-bar__btn offer-share-bar__btn--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span>WhatsApp</span></a>
                <a id="offerBarFacebook" href="#" target="_blank" rel="noopener noreferrer" class="offer-share-bar__btn offer-share-bar__btn--facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i><span>Facebook</span></a>
                <button type="button" id="offerBarInstagram" class="offer-share-bar__btn offer-share-bar__btn--instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i><span>Instagram</span></button>
                <button type="button" id="offerBarCopy" class="offer-share-bar__btn"><i class="fa-solid fa-link" aria-hidden="true"></i><span>Copy link</span></button>
            </div>
        </div>
    </div>
</div>
@endsection



@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/offers-market.css') }}?v={{ now()->timestamp }}">
@endpush

@push('scripts')
<script>
    (function () {
        const offerModal = document.getElementById('offerDetailsModal');
        if (!offerModal) return;
        const isLoggedIn = @json(auth()->check());

        const titleEl = document.getElementById('offerDetailsModalTitle');
        const discountEl = document.getElementById('offerDetailsModalDiscount');
        const descriptionEl = document.getElementById('offerDetailsModalDescription');
        const couponEl = document.getElementById('offerDetailsModalCoupon');
        const expiryEl = document.getElementById('offerDetailsModalExpiry');
        const validityRowEl = document.getElementById('offerDetailsModalValidityRow');
        const imageEl = document.getElementById('offerDetailsModalImage');
        const loginMessageBox = document.getElementById('offerLoginMessageBox');
        const sharePanelEl = document.getElementById('offerSharePanel');
        const shareBarEl = document.getElementById('offerShareBar');
        const readMoreBtn = document.getElementById('offerDetailsReadMore');
        const shareLinkEl = document.getElementById('offerShareLink');
        const shareQrEl = document.getElementById('offerShareQr');
        const shareWhatsappEl = document.getElementById('offerShareWhatsapp');
        const shareFacebookEl = document.getElementById('offerShareFacebook');
        const shareInstagramEl = document.getElementById('offerShareInstagram');
        const offerReportActions = document.getElementById('offerReportActions');
        const offerReportForm = document.getElementById('offerReportForm');
        const openOfferReportPopupBtn = document.getElementById('openOfferReportPopupBtn');
        const closeOfferReportPopupBtn = document.getElementById('closeOfferReportPopupBtn');
        const offerReportPopupWrap = document.getElementById('offerReportPopupWrap');
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

            if (!isLoggedIn) {
                if (loginMessageBox) loginMessageBox.classList.remove('d-none');
                titleEl.textContent = 'You are not logged in';
                descriptionEl.textContent = '';
                discountEl.textContent = '';
                discountEl.classList.add('d-none');
                couponEl.textContent = '';
                couponEl.classList.add('d-none');
                if (validityRowEl) validityRowEl.classList.add('d-none');
                expiryEl.textContent = '';
                imageEl.src = '';
                imageEl.classList.add('d-none');
                if (sharePanelEl) sharePanelEl.classList.add('d-none');
                if (shareBarEl) shareBarEl.classList.add('d-none');
                if (readMoreBtn) readMoreBtn.classList.add('d-none');
                if (shareLinkEl) shareLinkEl.value = '';
                if (shareQrEl) shareQrEl.src = '';
                if (shareWhatsappEl) shareWhatsappEl.href = '#';
                if (shareFacebookEl) shareFacebookEl.href = '#';
                if (offerReportActions) offerReportActions.classList.add('d-none');
                if (offerReportPopupWrap) offerReportPopupWrap.classList.add('d-none');
                return;
            }
            if (loginMessageBox) loginMessageBox.classList.add('d-none');
            if (validityRowEl) validityRowEl.classList.remove('d-none');
            if (sharePanelEl) sharePanelEl.classList.remove('d-none');
            if (shareBarEl) shareBarEl.classList.remove('d-none');
            const offerId = trigger.getAttribute('data-offer-id') || '';
            if (offerReportActions) offerReportActions.classList.toggle('d-none', !offerId);
            if (offerReportPopupWrap) offerReportPopupWrap.classList.add('d-none');
            if (offerReportForm && offerId) {
                offerReportForm.action = `{{ url('/offers-market') }}/${offerId}/report`;
                const reportReason = offerReportForm.querySelector('textarea[name="reason"]');
                if (reportReason) reportReason.value = '';
            }

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
            descriptionEl.classList.add('is-clamped');
            if (readMoreBtn) {
                readMoreBtn.classList.add('d-none');
                readMoreBtn.textContent = 'Read more';
                readMoreBtn.setAttribute('aria-expanded', 'false');
            }
            expiryEl.textContent = trigger.getAttribute('data-offer-validity') || 'No expiry';

            const bannerImage = trigger.getAttribute('data-offer-image');
            if (bannerImage) {
                imageEl.src = bannerImage;
                imageEl.classList.remove('d-none');
            } else {
                imageEl.src = '';
                imageEl.classList.add('d-none');
            }

            window.soilnwaterPopulateShareLinks({
                url: trigger.getAttribute('data-offer-url') || window.location.href,
                linkInput: shareLinkEl,
                qrImage: shareQrEl,
                whatsappLink: shareWhatsappEl,
                facebookLink: shareFacebookEl,
                whatsappSuffix: 'Check this offer on SoilnWater',
                qrSize: 224,
            });
            window.soilnwaterPopulateShareLinks({
                url: trigger.getAttribute('data-offer-url') || window.location.href,
                whatsappLink: 'offerBarWhatsapp',
                facebookLink: 'offerBarFacebook',
                whatsappSuffix: 'Check this offer on SoilnWater',
            });

        });

        // "Read more" only when the folded details (phones) hide some of the text; the popup has
        // to be on screen to measure it
        offerModal.addEventListener('shown.bs.modal', function () {
            if (!readMoreBtn || !isLoggedIn) return;
            const hidesText = descriptionEl.scrollHeight > descriptionEl.clientHeight + 1;
            readMoreBtn.classList.toggle('d-none', !hidesText);
        });

        if (readMoreBtn) {
            readMoreBtn.addEventListener('click', function () {
                const folded = descriptionEl.classList.toggle('is-clamped');
                readMoreBtn.textContent = folded ? 'Read more' : 'Show less';
                readMoreBtn.setAttribute('aria-expanded', folded ? 'false' : 'true');
            });
        }

        window.soilnwaterBindShareCopyButton('offerShareCopyBtn', 'offerShareLink');
        window.soilnwaterBindShareCopyButton('offerBarCopy', 'offerShareLink');
        window.soilnwaterBindInstagramShareButton('offerBarInstagram', 'offerShareLink', {
            title: 'SoilnWater Offer',
            text: 'Check this offer on SoilnWater',
        });
        window.soilnwaterBindInstagramShareButton('offerShareInstagram', 'offerShareLink', {
            title: 'SoilnWater Offer',
            text: 'Check this offer on SoilnWater',
        });

        if (openOfferReportPopupBtn && offerReportPopupWrap) {
            openOfferReportPopupBtn.addEventListener('click', function () {
                offerReportPopupWrap.classList.remove('d-none');
                offerReportPopupWrap.querySelector('textarea')?.focus();
            });
        }

        if (closeOfferReportPopupBtn && offerReportPopupWrap) {
            closeOfferReportPopupBtn.addEventListener('click', function () {
                offerReportPopupWrap.classList.add('d-none');
            });
        }
    })();
</script>
@endpush
