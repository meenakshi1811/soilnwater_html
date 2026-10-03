@php
    use App\Support\ConsultantListingCard;
    use App\Support\ServiceProviderListingCard;
    use App\Support\VendorListingCard;

    $hasLocation = $hasLocation ?? false;
    $sectionToggles = $sectionToggles ?? [];
    $showRecentAds = ! empty($sectionToggles['recent_ads']);
    $showOffers = ! empty($sectionToggles['offer_discount']);
    $showVendors = ! empty($sectionToggles['top_vendors']);
    $showServices = ! empty($sectionToggles['popular_services']);
    $showConsultants = ! empty($sectionToggles['consultants_enquiry']);
    $showCommunity = data_get($sectionToggles, 'community_hub', true);
    $showPremiumOptions = data_get($sectionToggles, 'premium_options', true);

    $mergeDiscoverCarousel = static function ($entities, $ads, string $entityType, int $interval = 4) {
        $items = collect();
        $ads = collect($ads)->values();
        $count = 0;

        foreach ($entities as $entity) {
            $items->push(['type' => $entityType, $entityType => $entity]);
            $count++;
            if ($ads->isNotEmpty() && $interval > 0 && $count % $interval === 0) {
                $adIndex = (int) ($count / $interval) - 1;
                $items->push(['type' => 'ad', 'ad' => $ads[$adIndex % $ads->count()]]);
            }
        }

        if ($entities->isEmpty() && $ads->isNotEmpty()) {
            foreach ($ads as $ad) {
                $items->push(['type' => 'ad', 'ad' => $ad]);
            }
        }

        return $items;
    };

    $cityFromLabel = static function (?string $label): string {
        if (! filled($label)) {
            return '';
        }

        return trim(explode(',', $label)[0]);
    };

    $recentApprovedAdsList = collect($recentApprovedAds ?? []);
    $sponsoredForRecent = collect($sponsoredListingsAds ?? [])->reject(
        fn ($ad) => $recentApprovedAdsList->contains('id', $ad->id)
    );
    $recentAdsCarouselItems = $recentApprovedAdsList
        ->map(fn ($ad) => ['type' => 'ad', 'ad' => $ad])
        ->concat($sponsoredForRecent->map(fn ($ad) => ['type' => 'ad', 'ad' => $ad]))
        ->values();

    $offersList = collect($offers ?? []);
    $offerCarouselItems = collect($offerDiscountTopAds ?? [])
        ->map(fn ($ad) => ['type' => 'ad', 'ad' => $ad])
        ->concat($mergeDiscoverCarousel(
            $offersList,
            collect($offerDiscountSideAds ?? []),
            'offer',
            3
        ))
        ->values();

    $vendorCarouselItems = collect($topVendorsHeaderAdsList ?? [])
        ->map(fn ($ad) => ['type' => 'ad', 'ad' => $ad])
        ->concat(collect($topVendorCarouselItems ?? []))
        ->values();

    $serviceCarouselItems = $mergeDiscoverCarousel(
        collect($topServiceProviders ?? []),
        collect($sponsoredListingsAds ?? []),
        'service_provider',
        4
    );

    $consultantCarouselItems = $mergeDiscoverCarousel(
        collect($topConsultants ?? []),
        collect($topVendorsSideAds ?? []),
        'consultant',
        4
    );

    $communityPosts = collect($homepageCommunityPosts ?? [])->take(8);
    $communityHubSections = $communityHubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
@endphp

<section class="homepage-discover-section" aria-label="Discover SoilnWater">
    <div class="homepage-discover-section__inner">
        <div class="homepage-discover-grid">
            @if ($showRecentAds)
                <div class="homepage-discover-panel homepage-discover-panel--ads homepage-discover-panel--order-ads">
                    <header class="homepage-discover-panel__head">
                        <div class="homepage-discover-panel__title-wrap">
                            <span class="homepage-discover-panel__icon homepage-discover-panel__icon--ads" aria-hidden="true">
                                <i class="fa-solid fa-rectangle-ad"></i>
                            </span>
                            <div>
                                <h2 class="homepage-discover-panel__title">Recent Ads</h2>
                                <p class="homepage-discover-panel__subtitle">Fresh sponsored listings and marketplace ads near you.</p>
                            </div>
                        </div>
                        <a class="homepage-discover-panel__view-all" href="{{ route('frontend.ads.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </header>
                    <div class="homepage-discover-panel__body">
                    @if ($recentAdsCarouselItems->isNotEmpty())
                        <div
                            class="card-carousel homepage-discover-carousel auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="2"
                            data-show-arrows="true"
                            data-show-dots="false"
                            data-pause-on-hover="false"
                            aria-label="Recent ads carousel"
                        >
                            <div class="card-carousel-track">
                                @foreach ($recentAdsCarouselItems as $item)
                                    <div class="card-carousel-item">
                                        @include('frontend.partials.homepage-discover-ad-card', [
                                            'ad' => $item['ad'],
                                            'meta' => 'Marketplace ad',
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="homepage-discover-panel__empty">No approved ads available yet.</p>
                    @endif
                    </div>
                </div>
            @endif

            @if ($showOffers)
                <div class="homepage-discover-panel homepage-discover-panel--offers homepage-discover-panel--order-offers">
                    <header class="homepage-discover-panel__head">
                        <div class="homepage-discover-panel__title-wrap">
                            <span class="homepage-discover-panel__icon homepage-discover-panel__icon--offers" aria-hidden="true">
                                <i class="fa-solid fa-tags"></i>
                            </span>
                            <div>
                                <h2 class="homepage-discover-panel__title">Recent Offers</h2>
                                <p class="homepage-discover-panel__subtitle">Exclusive offers and promotions from local businesses.</p>
                            </div>
                        </div>
                        <a class="homepage-discover-panel__view-all" href="{{ route('frontend.offers.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </header>
                    <div class="homepage-discover-panel__body">
                    @if ($offerCarouselItems->isNotEmpty())
                        <div
                            class="card-carousel homepage-discover-carousel auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="2"
                            data-show-arrows="true"
                            data-show-dots="false"
                            data-pause-on-hover="false"
                            aria-label="Recent offers carousel"
                        >
                            <div class="card-carousel-track">
                                @foreach ($offerCarouselItems as $item)
                                    <div class="card-carousel-item">
                                        @if ($item['type'] === 'ad')
                                            @include('frontend.partials.homepage-discover-ad-card', [
                                                'ad' => $item['ad'],
                                                'meta' => 'Sponsored offer',
                                            ])
                                        @else
                                            @php
                                                $offer = $item['offer'];
                                                $offerCardDescription = trim(preg_replace('/\s+/', ' ', $offer->short_description ?: 'Special marketplace offer available now.'));
                                                $offerImage = $offer->banner_image ? asset($offer->banner_image) : asset('assets/images/vendor-card-placeholder.svg');
                                            @endphp
                                            <article
                                                class="homepage-discover-card homepage-discover-card--offer js-offer-modal-trigger"
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
                                                <div class="homepage-discover-card__media">
                                                    <img src="{{ $offerImage }}" alt="{{ $offer->title }}" loading="lazy">
                                                    <span class="homepage-discover-card__badge">{{ $offer->discount_tag }}</span>
                                                </div>
                                                <div class="homepage-discover-card__body">
                                                    <h3 class="homepage-discover-card__title">{{ $offer->title }}</h3>
                                                    <p class="homepage-discover-card__meta">{{ \Illuminate\Support\Str::limit($offerCardDescription, 72) }}</p>
                                                </div>
                                            </article>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="homepage-discover-panel__empty">No active offers available.</p>
                    @endif
                    </div>
                </div>
            @endif

            @if ($showServices)
                <div class="homepage-discover-panel homepage-discover-panel--services homepage-discover-panel--order-services">
                    <header class="homepage-discover-panel__head">
                        <div class="homepage-discover-panel__title-wrap">
                            <span class="homepage-discover-panel__icon homepage-discover-panel__icon--services" aria-hidden="true">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </span>
                            <div>
                                <h2 class="homepage-discover-panel__title">Popular Services</h2>
                                <p class="homepage-discover-panel__subtitle">Find trusted professionals for your needs.</p>
                            </div>
                        </div>
                        <div class="homepage-discover-panel__head-actions">
                            <button type="button" class="homepage-discover-panel__enquiry" data-bs-toggle="modal" data-bs-target="#serviceProviderEnquiryModal">Enquiry</button>
                            <a class="homepage-discover-panel__view-all" href="{{ route('frontend.service_providers.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </header>
                    <div class="homepage-discover-panel__body">
                    @if ($serviceCarouselItems->isNotEmpty())
                        <div
                            class="card-carousel homepage-discover-carousel auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="2"
                            data-show-arrows="true"
                            data-show-dots="false"
                            data-pause-on-hover="false"
                            aria-label="Popular services carousel"
                        >
                            <div class="card-carousel-track">
                                @foreach ($serviceCarouselItems as $item)
                                    <div class="card-carousel-item">
                                        @if ($item['type'] === 'ad')
                                            @include('frontend.partials.homepage-discover-ad-card', ['ad' => $item['ad'], 'meta' => 'Sponsored'])
                                        @else
                                            @php
                                                $card = ServiceProviderListingCard::data($item['service_provider'], $hasLocation);
                                            @endphp
                                            @include('frontend.partials.homepage-discover-profile-card', [
                                                'href' => $card['profileUrl'],
                                                'image' => $card['coverImage'],
                                                'title' => $item['service_provider']->publicDisplayName(),
                                                'category' => $card['categoryName'],
                                                'location' => $cityFromLabel($card['locationLabel']),
                                                'ratingScore' => $card['ratingScore'],
                                                'ratingCount' => $card['ratingCount'],
                                            ])
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="homepage-discover-panel__empty">No services available yet.</p>
                    @endif
                    </div>
                </div>
            @endif

            @if ($showConsultants)
                <div class="homepage-discover-panel homepage-discover-panel--consultants homepage-discover-panel--order-consultants">
                    <header class="homepage-discover-panel__head">
                        <div class="homepage-discover-panel__title-wrap">
                            <span class="homepage-discover-panel__icon homepage-discover-panel__icon--consultant" aria-hidden="true">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <div>
                                <h2 class="homepage-discover-panel__title">Find a Consultant</h2>
                                <p class="homepage-discover-panel__subtitle">Connect with experts who can help you.</p>
                            </div>
                        </div>
                        <div class="homepage-discover-panel__head-actions">
                            <button type="button" class="homepage-discover-panel__enquiry" data-bs-toggle="modal" data-bs-target="#consultantEnquiryModal">Enquiry</button>
                            <a class="homepage-discover-panel__view-all" href="{{ route('frontend.consultants.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </header>
                    <div class="homepage-discover-panel__body">
                    @if ($consultantCarouselItems->isNotEmpty())
                        <div
                            class="card-carousel homepage-discover-carousel auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="2"
                            data-show-arrows="true"
                            data-show-dots="false"
                            data-pause-on-hover="false"
                            aria-label="Consultants carousel"
                        >
                            <div class="card-carousel-track">
                                @foreach ($consultantCarouselItems as $item)
                                    <div class="card-carousel-item">
                                        @if ($item['type'] === 'ad')
                                            @include('frontend.partials.homepage-discover-ad-card', ['ad' => $item['ad'], 'meta' => 'Sponsored'])
                                        @else
                                            @php
                                                $card = ConsultantListingCard::data($item['consultant'], $hasLocation);
                                            @endphp
                                            @include('frontend.partials.homepage-discover-profile-card', [
                                                'href' => $card['profileUrl'],
                                                'image' => $card['coverImage'],
                                                'title' => $item['consultant']->publicDisplayName(),
                                                'category' => $card['categoryName'],
                                                'location' => $cityFromLabel($card['locationLabel']),
                                                'ratingScore' => $card['ratingScore'],
                                                'ratingCount' => $card['ratingCount'],
                                            ])
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="homepage-discover-panel__empty">No consultants available yet.</p>
                    @endif
                    </div>
                </div>
            @endif

            @if ($showVendors)
                <div class="homepage-discover-panel homepage-discover-panel--vendors homepage-discover-panel--order-vendors">
                    <header class="homepage-discover-panel__head">
                        <div class="homepage-discover-panel__title-wrap">
                            <span class="homepage-discover-panel__icon homepage-discover-panel__icon--vendor" aria-hidden="true">
                                <i class="fa-solid fa-store"></i>
                            </span>
                            <div>
                                <h2 class="homepage-discover-panel__title">Top Vendors</h2>
                                <p class="homepage-discover-panel__subtitle">Trusted local businesses and stores near you.</p>
                            </div>
                        </div>
                        <a class="homepage-discover-panel__view-all" href="{{ route('frontend.vendors.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </header>
                    <div class="homepage-discover-panel__body">
                    @if ($vendorCarouselItems->isNotEmpty())
                        <div
                            class="card-carousel homepage-discover-carousel homepage-discover-carousel--wide auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="4"
                            data-show-arrows="true"
                            data-show-dots="false"
                            data-pause-on-hover="false"
                            aria-label="Top vendors carousel"
                        >
                            <div class="card-carousel-track">
                                @foreach ($vendorCarouselItems as $item)
                                    <div class="card-carousel-item">
                                        @if ($item['type'] === 'ad')
                                            @include('frontend.partials.homepage-discover-ad-card', ['ad' => $item['ad'], 'meta' => 'Featured ad'])
                                        @else
                                            @php
                                                $card = VendorListingCard::data($item['vendor'], $hasLocation);
                                            @endphp
                                            @include('frontend.partials.homepage-discover-profile-card', [
                                                'href' => $card['storeUrl'],
                                                'image' => $card['coverImage'],
                                                'title' => $item['vendor']->publicDisplayName(),
                                                'category' => $card['categoryName'],
                                                'location' => $cityFromLabel($card['locationLabel']),
                                                'ratingScore' => $card['ratingScore'],
                                                'ratingCount' => $card['ratingCount'],
                                            ])
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="homepage-discover-panel__empty">No vendors available yet.</p>
                    @endif
                    </div>
                </div>
            @endif

            <div class="homepage-discover-panel homepage-discover-panel--education homepage-discover-panel--order-education">
                <header class="homepage-discover-panel__head">
                    <div class="homepage-discover-panel__title-wrap">
                        <span class="homepage-discover-panel__icon homepage-discover-panel__icon--education" aria-hidden="true">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </span>
                        <div>
                            <h2 class="homepage-discover-panel__title">Education &amp; Knowledge Hub</h2>
                            <p class="homepage-discover-panel__subtitle">Schools, courses, tutors, and learning resources.</p>
                        </div>
                    </div>
                    <a class="homepage-discover-panel__view-all" href="{{ route('schools.index') }}">Explore <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </header>
                <div class="homepage-discover-panel__body homepage-discover-panel__body--hub">
                <div class="homepage-discover-education">
                    <div class="homepage-discover-education__visual">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=900&q=80" alt="Student with books" loading="lazy">
                    </div>
                    <div class="homepage-discover-education__tiles">
                        <a class="homepage-discover-education__tile" href="{{ route('schools.index') }}">
                            <span class="homepage-discover-education__tile-icon"><i class="fa-solid fa-school"></i></span>
                            Schools &amp; Colleges
                        </a>
                        <a class="homepage-discover-education__tile" href="{{ route('institutes.index') }}">
                            <span class="homepage-discover-education__tile-icon"><i class="fa-solid fa-laptop"></i></span>
                            Online Courses
                        </a>
                        <a class="homepage-discover-education__tile" href="{{ route('educator.index') }}">
                            <span class="homepage-discover-education__tile-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
                            Tutors &amp; Coaching
                        </a>
                        <a class="homepage-discover-education__tile" href="{{ route('community.index') }}">
                            <span class="homepage-discover-education__tile-icon"><i class="fa-solid fa-book-open"></i></span>
                            Articles &amp; Awareness
                        </a>
                    </div>
                </div>
                </div>
            </div>

            @if ($showCommunity)
                <div class="homepage-discover-panel homepage-discover-panel--community homepage-discover-panel--order-community">
                    <header class="homepage-discover-panel__head">
                        <div class="homepage-discover-panel__title-wrap">
                            <span class="homepage-discover-panel__icon homepage-discover-panel__icon--community" aria-hidden="true">
                                <i class="fa-solid fa-book-open"></i>
                            </span>
                            <div>
                                <h2 class="homepage-discover-panel__title">SoilnWater Community</h2>
                                <p class="homepage-discover-panel__subtitle">Stories · People · Memories · Ideas</p>
                            </div>
                        </div>
                        <a class="homepage-discover-panel__view-all" href="{{ route('community.index') }}">Explore <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </header>
                    <div class="homepage-discover-panel__body homepage-discover-panel__body--hub">
                    @if ($communityPosts->isNotEmpty())
                        <div
                            class="card-carousel homepage-discover-carousel homepage-discover-community-carousel auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="2"
                            data-show-arrows="true"
                            data-show-dots="false"
                            data-pause-on-hover="false"
                            aria-label="Community stories carousel"
                        >
                            <div class="card-carousel-track">
                                @foreach ($communityPosts as $post)
                                    <div class="card-carousel-item">
                                        <a href="{{ route('community.show', $post) }}" class="homepage-discover-story-card">
                                            <div class="homepage-discover-story-card__media">
                                                @if ($post->featuredImageUrl())
                                                    <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" loading="lazy">
                                                @else
                                                    <div class="homepage-discover-story-card__placeholder" aria-hidden="true">
                                                        <i class="fa-solid fa-newspaper"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <h3 class="homepage-discover-story-card__title">{{ \Illuminate\Support\Str::limit($post->title, 48) }}</h3>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div
                            class="card-carousel homepage-discover-carousel homepage-discover-community-carousel auto-ad-slider"
                            data-slide-by="card"
                            data-carousel-cols="2"
                            data-show-arrows="true"
                            data-show-dots="false"
                            aria-label="Community hub categories"
                        >
                            <div class="card-carousel-track">
                                @foreach (collect($communityHubSections)->take(6) as $hubKey => $hub)
                                    <div class="card-carousel-item">
                                        <a href="{{ route('community.index', ['hub' => $hubKey]) }}" class="homepage-discover-story-card">
                                            <div class="homepage-discover-story-card__media">
                                                <div class="homepage-discover-story-card__placeholder" aria-hidden="true">
                                                    <i class="fa-solid {{ $hub['icon'] ?? 'fa-book-open' }}"></i>
                                                </div>
                                            </div>
                                            <h3 class="homepage-discover-story-card__title">{{ $hub['label'] ?? \Illuminate\Support\Str::headline($hubKey) }}</h3>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
