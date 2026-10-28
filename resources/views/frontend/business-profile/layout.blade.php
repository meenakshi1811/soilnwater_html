{{--
  The single-page profile of a vendor, consultant or service provider: a header with the cover, logo
  and actions, then the sections (menu on the left, tabs on smaller screens), and the contact
  details on the right.

  From the page that extends this:
    $biz          App\Support\BusinessProfilePage::for(...)
    $bizOffer     the services or products: id, navLabel, title, icon, lead, count, chips, partial,
                  data, empty
    $bizEnquiry   the enquiry popup's form: action, serviceField, services
    $bizSections  the profile's own page sections
    $bizAds       fullPage, supporting, compactCard (partial), recent, recentCategoryNames, recentLabel
    $bizPremiumType  'vendor' | 'consultant' | 'service'
    $similarVendors (vendors), $preview
--}}
@extends('frontend.layouts.app')

@php
    use App\Models\PremiumPrice;
    use App\Support\BusinessProfilePage;
    use Illuminate\Support\Str;

    $preview = (bool) ($preview ?? false);
    $bizSections = collect($bizSections ?? []);
    $similarVendors = collect($similarVendors ?? []);
    $fullPageAds = collect($bizAds['fullPage'] ?? [])->filter()->values();
    $supportingAds = collect($bizAds['supporting'] ?? [])->filter()->values()->take(3);
    $recentAds = collect($bizAds['recent'] ?? [])->values();
    $enquiryTarget = auth()->check() ? '#bizEnquiryModal' : '#bizLoginRequiredModal';
    $primaryPhone = $biz['phones'][0] ?? null;
    $metaDescription = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($biz['aboutHtml'] ?: $biz['taglineHtml']))) ?: $biz['name'].' on SoilnWater', 155);

    // The owner's own sections: those with something to show, named by their title
    $customSections = $bizSections->map(function ($section) {
        $title = trim(html_entity_decode(strip_tags((string) $section->title)));
        $hasBody = filled($section->image_path)
            || trim(strip_tags((string) $section->content, '<img><video><iframe><embed><object>')) !== '';

        // What the section holds: pictures (image grid, image cards, brochure), a video, a picture
        // beside text, or text only
        $content = (string) $section->content;
        $kind = match (true) {
            str_contains($content, 'data-grid-image-slot') || str_contains($content, 'data-card-image-slot') || str_contains($content, 'data-brochure-wrap') => 'gallery',
            str_contains($content, 'vendor-section-video') => 'video',
            filled($section->image_path) => 'split',
            default => 'text',
        };

        return $hasBody ? [
            'model' => $section,
            'id' => 'biz-section-'.$section->id,
            'title' => $title !== '' ? $title : 'More',
            'hasVideo' => $kind === 'video',
            'kind' => $kind,
        ] : null;
    })->filter()->values();

    $showAbout = $biz['aboutHtml'] !== '' || $biz['taglineHtml'] !== '';
    $showBranches = $biz['branches']->count() > 1;

    $navItems = collect([
        ['id' => 'biz-overview', 'label' => 'Overview', 'icon' => 'fa-id-card'],
        ['id' => $bizOffer['id'], 'label' => $bizOffer['navLabel'], 'icon' => $bizOffer['icon']],
    ])
        ->when($showAbout, fn ($items) => $items->push(['id' => 'biz-about', 'label' => 'About', 'icon' => 'fa-circle-info']))
        ->when($biz['expertise']->isNotEmpty(), fn ($items) => $items->push(['id' => 'biz-expertise', 'label' => 'Expertise', 'icon' => 'fa-award']))
        ->merge($customSections->map(fn ($section) => ['id' => $section['id'], 'label' => Str::limit($section['title'], 28), 'icon' => 'fa-layer-group']))
        ->when($showBranches, fn ($items) => $items->push(['id' => 'biz-branches', 'label' => 'Branches', 'icon' => 'fa-code-branch']))
        ->push(['id' => 'biz-contact', 'label' => 'Contact', 'icon' => 'fa-address-book'])
        ->values();

    $stats = collect([
        ['value' => number_format($bizOffer['count']), 'label' => $bizOffer['navLabel']],
        $biz['branches']->count() > 0 ? ['value' => number_format($biz['branches']->count()), 'label' => Str::plural('Branch', $biz['branches']->count())] : null,
        $biz['since'] ? ['value' => $biz['since'], 'label' => 'In business since'] : null,
    ])->filter()->values();
@endphp

@section('meta_title', $biz['name'].' · '.$biz['typeLabel'].' | SoilnWater')
@section('meta_description', $metaDescription)
@if($biz['banners']->isNotEmpty() || $biz['logo'])
    @section('meta_image', $biz['banners']->first() ?: $biz['logo'])
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/business-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="biz-page biz-page--{{ $biz['type'] }}" id="bizProfilePage">
    <div class="biz-container">
        @if($preview)
            <div class="biz-preview-banner"><i class="fa-solid fa-eye" aria-hidden="true"></i> Preview — only you can see this page until it is published.</div>
        @endif

        <nav class="biz-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
            <span class="biz-breadcrumb__sep" aria-hidden="true">›</span>
            <a href="{{ $biz['listingUrl'] }}">{{ $biz['listingLabel'] }}</a>
            <span class="biz-breadcrumb__sep" aria-hidden="true">›</span>
            <span class="biz-breadcrumb__current" aria-current="page">{{ $biz['name'] }}</span>
        </nav>

        {{-- Header: cover, logo, name, details and actions --}}
        <section class="biz-hero {{ $biz['logo'] ? 'biz-hero--has-logo' : '' }}" id="biz-overview">
            <div class="biz-hero__cover {{ $biz['banners']->isEmpty() ? 'biz-hero__cover--plain' : '' }}">
                @if($biz['banners']->isNotEmpty())
                    <div id="bizCoverCarousel" class="carousel slide carousel-fade h-100" @if($biz['banners']->count() > 1) data-bs-ride="carousel" data-bs-interval="5000" @endif>
                        <div class="carousel-inner h-100">
                            @foreach($biz['banners'] as $banner)
                                <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                                    <span class="biz-hero__backdrop" style="background-image: url('{{ $banner }}')" aria-hidden="true"></span>
                                    <img src="{{ $banner }}" alt="{{ $biz['name'] }} banner {{ $loop->iteration }}" class="biz-hero__image" @if(! $loop->first) loading="lazy" @endif>
                                </div>
                            @endforeach
                        </div>
                        @if($biz['banners']->count() > 1)
                            <div class="carousel-indicators biz-hero__dots">
                                @foreach($biz['banners'] as $banner)
                                    <button type="button" data-bs-target="#bizCoverCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" @if($loop->first) aria-current="true" @endif aria-label="Banner {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <span class="biz-hero__watermark" aria-hidden="true"><i class="fa-solid {{ $biz['typeIcon'] }}"></i></span>
                @endif
                @if($biz['logo'])
                    {{-- Phones: the logo in place of the banner --}}
                    <div class="biz-hero__cover-logo">
                        {{-- (a wide logo is shown whole; a photo or square one fills the tile) --}}
                        <img src="{{ $biz['logo'] }}" alt="{{ $biz['name'] }} logo" onload="this.classList.toggle('is-wide', this.naturalWidth > this.naturalHeight * 1.4)">
                    </div>
                @endif
            </div>

            <div class="biz-hero__body">
                <div class="biz-hero__logo">
                    @if($biz['logo'])
                        <img src="{{ $biz['logo'] }}" alt="{{ $biz['name'] }} logo">
                    @else
                        <span>{{ $biz['initials'] }}</span>
                    @endif
                </div>

                <div class="biz-hero__identity">
                    <div class="biz-hero__badges">
                        <span class="biz-badge biz-badge--type"><i class="fa-solid {{ $biz['typeIcon'] }}" aria-hidden="true"></i> {{ $biz['typeLabel'] }}</span>
                        @if($biz['isPremium'])
                            <span class="biz-badge biz-badge--premium"><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium</span>
                            <span class="biz-badge biz-badge--verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                        @endif
                        @if($biz['category'])
                            <span class="biz-badge">{{ $biz['category'] }}</span>
                        @endif
                    </div>
                    <h1 class="biz-hero__name">{{ $biz['name'] }}</h1>
                    @if($biz['headline'] !== '')
                        <p class="biz-hero__headline">{{ $biz['headline'] }}</p>
                    @endif
                    <ul class="biz-hero__meta">
                        @if($biz['place'] !== '')
                            <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $biz['place'] }}</li>
                        @endif
                        @if($biz['since'])
                            <li><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Since {{ $biz['since'] }}</li>
                        @endif
                        @if(filled($biz['contactPerson']))
                            <li><i class="fa-solid fa-user" aria-hidden="true"></i> {{ $biz['contactPerson'] }}</li>
                        @endif
                    </ul>
                </div>

                <div class="biz-hero__actions">
                    <button type="button" class="biz-btn biz-btn--primary" data-bs-toggle="modal" data-bs-target="{{ $enquiryTarget }}">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <span><span class="biz-hide-sm">Send </span>Enquiry</span>
                    </button>
                    @if($primaryPhone)
                        <a href="tel:{{ $primaryPhone }}" class="biz-btn biz-btn--outline"><i class="fa-solid fa-phone" aria-hidden="true"></i> <span>Call</span></a>
                    @endif
                    @if($biz['whatsappUrl'])
                        <a href="{{ $biz['whatsappUrl'] }}" target="_blank" rel="noopener" class="biz-btn biz-btn--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> <span>WhatsApp</span></a>
                    @endif
                    <button type="button" class="biz-btn biz-btn--outline" data-bs-toggle="modal" data-bs-target="#bizShareModal">
                        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> <span>Share</span>
                    </button>
                </div>
            </div>

            @if($stats->count() > 1)
                <dl class="biz-hero__stats">
                    @foreach($stats as $stat)
                        <div class="biz-hero__stat">
                            <dt>{{ $stat['label'] }}</dt>
                            <dd>{{ $stat['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </section>

        {{-- Tabs for phones and tablets --}}
        <nav class="biz-tabs" aria-label="Profile sections">
            <div class="biz-tabs__track">
                @foreach($navItems as $item)
                    <a href="#{{ $item['id'] }}" class="biz-tabs__link {{ $loop->first ? 'is-active' : '' }}" data-biz-nav>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </nav>

        <div class="biz-grid">
            <aside class="biz-nav" aria-label="Profile sections">
                <div class="biz-nav__inner">
                    <ul class="biz-nav__list">
                        @foreach($navItems as $item)
                            <li>
                                <a href="#{{ $item['id'] }}" class="biz-nav__link {{ $loop->first ? 'is-active' : '' }}" data-biz-nav>
                                    <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="biz-btn biz-btn--outline biz-nav__share" data-bs-toggle="modal" data-bs-target="#bizShareModal">
                        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Share Profile
                    </button>
                </div>
            </aside>

            <main class="biz-main">
                {{-- Services / products --}}
                <section class="biz-section" id="{{ $bizOffer['id'] }}" data-biz-filter-scope>
                    <header class="biz-section__head">
                        <div>
                            <h2 class="biz-section__title">
                                <i class="fa-solid {{ $bizOffer['icon'] }}" aria-hidden="true"></i>
                                {{ $bizOffer['title'] }}
                                @if($bizOffer['count'] > 0)<span class="biz-count">{{ number_format($bizOffer['count']) }}</span>@endif
                            </h2>
                            <p class="biz-section__lead">{{ $bizOffer['lead'] }}</p>
                        </div>
                    </header>

                    @if($bizOffer['count'] > 0)
                        @if($bizOffer['chips']->count() > 1)
                            <div class="biz-chips" role="group" aria-label="Filter by category">
                                <button type="button" class="biz-chip is-active" data-biz-filter="">All <span>{{ $bizOffer['count'] }}</span></button>
                                @foreach($bizOffer['chips'] as $categoryId => $chip)
                                    <button type="button" class="biz-chip" data-biz-filter="{{ $categoryId }}">{{ $chip['name'] }} <span>{{ $chip['count'] }}</span></button>
                                @endforeach
                            </div>
                        @endif

                        <div class="biz-items" data-biz-items data-biz-limit="{{ $bizOffer['limit'] ?? 9 }}">
                            @include($bizOffer['partial'], $bizOffer['data'])
                        </div>
                        <button type="button" class="biz-more" data-biz-more hidden>
                            Show all {{ strtolower($bizOffer['navLabel']) }} <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    @else
                        <div class="biz-empty">
                            <i class="fa-solid {{ $bizOffer['icon'] }}" aria-hidden="true"></i>
                            <p>{{ $bizOffer['empty'] }}</p>
                        </div>
                    @endif
                </section>

                @if($supportingAds->isNotEmpty())
                    <section class="biz-section biz-sponsored" aria-label="Sponsored">
                        <p class="biz-sponsored__label"><i class="fa-solid fa-rectangle-ad" aria-hidden="true"></i> Sponsored</p>
                        <div class="biz-sponsored__grid">
                            @foreach($supportingAds as $ad)
                                @include($bizAds['compactCard'], ['ad' => $ad, 'variant' => 'service'])
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($showAbout)
                    <section class="biz-section biz-builder vendor-store-page" id="biz-about">
                        <h2 class="biz-section__title"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> About {{ $biz['name'] }}</h2>
                        {{-- Four lines, then "Read more" --}}
                        <div class="biz-collapsible" data-biz-collapsible>
                            @if($biz['taglineHtml'] !== '')
                                <div class="biz-about__lead">{!! $biz['taglineHtml'] !!}</div>
                            @endif
                            @if($biz['aboutHtml'] !== '')
                                <div class="content-body">{!! $biz['aboutHtml'] !!}</div>
                            @endif
                        </div>
                        <button type="button" class="biz-readmore" data-biz-readmore hidden>Read more <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    </section>
                @endif

                @if($biz['expertise']->isNotEmpty())
                    <section class="biz-section" id="biz-expertise">
                        <h2 class="biz-section__title"><i class="fa-solid fa-award" aria-hidden="true"></i> Expertise</h2>
                        <div class="biz-expertise {{ $biz['expertise']->count() > 1 ? 'biz-expertise--two' : '' }}">
                            @foreach($biz['expertise'] as $branch)
                                <article class="biz-expertise__card">
                                    <header>
                                        <h3>{{ $branch->branch_name ?: $biz['name'] }}</h3>
                                        @if($branch->is_primary && $biz['expertise']->count() > 1)
                                            <span class="biz-tag">Primary branch</span>
                                        @endif
                                    </header>
                                    @if(filled($branch->professional_experience))
                                        <p class="biz-expertise__label"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Professional experience</p>
                                        <p class="biz-expertise__text">{{ $branch->professional_experience }}</p>
                                    @endif
                                    @if(filled($branch->services_offered))
                                        <p class="biz-expertise__label"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Services offered</p>
                                        <p class="biz-expertise__text">{{ $branch->services_offered }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @foreach($customSections as $section)
                    @php
                        $model = $section['model'];
                    @endphp
                    <section class="biz-section biz-builder vendor-store-page vendor-store-section vendor-custom-section biz-builder--{{ $section['kind'] }} {{ $section['hasVideo'] ? 'has-video-section' : '' }}" id="{{ $section['id'] }}" @if($section['kind'] === 'gallery') data-biz-gallery-section @endif>
                        <h2 class="biz-section__title"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> {{ $section['title'] }}</h2>
                        @if($section['kind'] === 'text')
                            {{-- Four lines, then "Read more" --}}
                            <div class="biz-collapsible" data-biz-collapsible>
                                <div class="content-body">{!! BusinessProfilePage::richHtml($model->content) !!}</div>
                            </div>
                            <button type="button" class="biz-readmore" data-biz-readmore hidden>Read more <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                        @else
                            <div class="biz-builder__body {{ $model->image_path ? 'biz-builder__body--split' : '' }}">
                                @if($model->image_path)
                                    <img src="{{ asset($model->image_path) }}" alt="{{ $section['title'] }}" class="biz-builder__image" loading="lazy">
                                @endif
                                <div class="content-body">{!! BusinessProfilePage::richHtml($model->content) !!}</div>
                            </div>
                        @endif
                    </section>
                @endforeach

                @if($showBranches)
                    <section class="biz-section" id="biz-branches">
                        <h2 class="biz-section__title"><i class="fa-solid fa-code-branch" aria-hidden="true"></i> Branches <span class="biz-count">{{ $biz['branches']->count() }}</span></h2>
                        <div class="biz-branches">
                            @foreach($biz['branches'] as $branch)
                                @php
                                    $branchAddress = collect([$branch->address, $branch->city, $branch->state, $branch->pincode])->filter(fn ($part) => filled($part))->implode(', ');
                                @endphp
                                <article class="biz-branch">
                                    <header>
                                        <h3>{{ $branch->branch_name ?: $biz['name'] }}</h3>
                                        @if($branch->is_primary)<span class="biz-tag">Primary</span>@endif
                                    </header>
                                    @if($branchAddress !== '')
                                        <p><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $branchAddress }}</p>
                                    @endif
                                    @if(filled($branch->phone))
                                        <p><i class="fa-solid fa-phone" aria-hidden="true"></i> <a href="tel:{{ $branch->phone }}">{{ $branch->phone }}</a></p>
                                    @endif
                                    @if(filled($branch->email))
                                        <p><i class="fa-solid fa-envelope" aria-hidden="true"></i> <a href="mailto:{{ $branch->email }}">{{ $branch->email }}</a></p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="biz-section" id="biz-contact">
                    <h2 class="biz-section__title"><i class="fa-solid fa-address-book" aria-hidden="true"></i> Contact &amp; Location</h2>
                    <div class="biz-contact">
                        <ul class="biz-contact__list">
                            <li>
                                <span class="biz-contact__icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                                <div>
                                    <span class="biz-contact__label">Address</span>
                                    <span class="biz-contact__value">{{ $biz['address'] ?: 'Address not added yet' }}</span>
                                    @if($biz['mapQuery'])
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $biz['mapQuery'] }}" target="_blank" rel="noopener" class="biz-contact__link">Get directions <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                                    @endif
                                </div>
                            </li>
                            @if($biz['phones'] !== [])
                                <li>
                                    <span class="biz-contact__icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="biz-contact__label">Phone</span>
                                        <span class="biz-contact__value">@include('frontend.partials.profile-phone-links', ['phones' => $biz['phones']])</span>
                                    </div>
                                </li>
                            @endif
                            @if($biz['whatsappUrl'])
                                <li>
                                    <span class="biz-contact__icon biz-contact__icon--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="biz-contact__label">WhatsApp</span>
                                        <a href="{{ $biz['whatsappUrl'] }}" target="_blank" rel="noopener" class="biz-contact__value">Chat on WhatsApp</a>
                                    </div>
                                </li>
                            @endif
                            @if(filled($biz['email']))
                                <li>
                                    <span class="biz-contact__icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="biz-contact__label">Email</span>
                                        <a href="mailto:{{ $biz['email'] }}" class="biz-contact__value">{{ $biz['email'] }}</a>
                                    </div>
                                </li>
                            @endif
                        </ul>

                        <div class="biz-contact__cta">
                            <span class="biz-contact__cta-icon"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></span>
                            <h3>Have a requirement?</h3>
                            <p>Send an enquiry and {{ $biz['name'] }} will get back to you.</p>
                            <button type="button" class="biz-btn biz-btn--primary" data-bs-toggle="modal" data-bs-target="{{ $enquiryTarget }}">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Enquiry
                            </button>
                        </div>
                    </div>

                    @if($biz['mapQuery'])
                        <div class="biz-map">
                            <iframe
                                title="Map for {{ $biz['name'] }}"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ $biz['mapQuery'] }}&z=15&hl=en&output=embed"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @endif
                </section>
            </main>

            <aside class="biz-side">
                <div class="biz-card biz-card--contact">
                    <h3 class="biz-card__title"><i class="fa-solid fa-headset" aria-hidden="true"></i> Contact {{ $biz['typeLabel'] }}</h3>
                    @foreach($biz['phones'] as $phone)
                        <a href="tel:{{ $phone }}" class="biz-card__row"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $phone }}</a>
                    @endforeach
                    @if($biz['whatsappUrl'])
                        <a href="{{ $biz['whatsappUrl'] }}" target="_blank" rel="noopener" class="biz-card__row biz-card__row--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp</a>
                    @endif
                    @if(filled($biz['email']))
                        <a href="mailto:{{ $biz['email'] }}" class="biz-card__row"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <span>{{ $biz['email'] }}</span></a>
                    @endif
                    <button type="button" class="biz-btn biz-btn--primary" data-bs-toggle="modal" data-bs-target="{{ $enquiryTarget }}">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Enquiry
                    </button>
                </div>

                @if($biz['address'])
                    <div class="biz-card biz-card--location">
                        <h3 class="biz-card__title"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Location</h3>
                        <p class="biz-card__text">{{ $biz['address'] }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $biz['mapQuery'] }}" target="_blank" rel="noopener" class="biz-card__link">Get directions <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    </div>
                @endif

                <div class="biz-card">
                    <h3 class="biz-card__title"><i class="fa-solid fa-building" aria-hidden="true"></i> Business details</h3>
                    {{-- The first three, then "More details" (business-profile.js) --}}
                    <dl class="biz-facts" data-biz-facts>
                        <div><dt>Type</dt><dd>{{ $biz['typeLabel'] }}</dd></div>
                        @if($biz['category'])<div><dt>Category</dt><dd>{{ $biz['category'] }}</dd></div>@endif
                        @if(filled($biz['contactPerson']))<div><dt>Contact person</dt><dd>{{ $biz['contactPerson'] }}</dd></div>@endif
                        @if($biz['since'])<div><dt>In business since</dt><dd>{{ $biz['since'] }}</dd></div>@endif
                        <div><dt>{{ $bizOffer['navLabel'] }}</dt><dd>{{ number_format($bizOffer['count']) }}</dd></div>
                        @if($biz['branches']->count() > 0)<div><dt>Branches</dt><dd>{{ $biz['branches']->count() }}</dd></div>@endif
                    </dl>
                    <button type="button" class="biz-readmore" data-biz-facts-more hidden>More details <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    @if($biz['facebook'] || $biz['instagram'])
                        <div class="biz-social">
                            @if($biz['facebook'])<a href="{{ $biz['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>@endif
                            @if($biz['instagram'])<a href="{{ $biz['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>@endif
                        </div>
                    @endif
                </div>

                @if($biz['isOwner'] && ! $biz['isPremium'])
                    <div class="biz-card biz-card--premium">
                        <span class="biz-card--premium__icon"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                        <h3 class="biz-card__title">Go Premium</h3>
                        <p class="biz-card__text">A premium badge, priority listing and an ad-free profile from {{ PremiumPrice::formatAmount(PremiumPrice::amountFor($bizPremiumType)) }}.</p>
                        <a href="{{ route('frontend.premium.show', $bizPremiumType) }}" class="biz-btn biz-btn--premium">Get Premium</a>
                    </div>
                @endif

                @if($fullPageAds->isNotEmpty())
                    <div class="biz-card biz-card--ads">
                        @include('frontend.store.partials.ads-rail', ['ads' => $fullPageAds, 'railId' => 'bizSideAds'])
                    </div>
                @endif

                @if($biz['reportRoute'])
                    <button type="button" class="biz-report" data-bs-toggle="modal" data-bs-target="#bizReportModal">
                        <i class="fa-regular fa-flag" aria-hidden="true"></i> Report this profile
                    </button>
                @endif
            </aside>
        </div>

        @if($recentAds->isNotEmpty())
            <div class="biz-extra">
                @include('frontend.store.partials.recent-ads-slider', [
                    'ads' => $recentAds,
                    'selectedCategoryNamesByAdId' => $bizAds['recentCategoryNames'] ?? [],
                    'sectionTitle' => 'Sponsored Ads',
                    'defaultCategoryLabel' => $bizAds['recentLabel'] ?? $biz['listingLabel'],
                ])
            </div>
        @endif

        @if($similarVendors->isNotEmpty())
            <div class="biz-extra">
                @include('frontend.store.partials.similar-vendors', ['similarVendors' => $similarVendors])
            </div>
        @endif
    </div>

    {{-- Phones: call, share, WhatsApp and enquiry always at hand (instead of the header's buttons) --}}
    <div class="biz-actionbar">
        @if($primaryPhone)
            <a href="tel:{{ $primaryPhone }}" class="biz-actionbar__btn"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call</a>
        @endif
        <button type="button" class="biz-actionbar__btn" data-bs-toggle="modal" data-bs-target="#bizShareModal"><i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Share</button>
        @if($biz['whatsappUrl'])
            <a href="{{ $biz['whatsappUrl'] }}" target="_blank" rel="noopener" class="biz-actionbar__btn biz-actionbar__btn--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
        @endif
        <button type="button" class="biz-actionbar__btn biz-actionbar__btn--primary" data-bs-toggle="modal" data-bs-target="{{ $enquiryTarget }}"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enquire</button>
    </div>
</div>

{{-- Popups --}}
@include('frontend.business-profile.partials.share-modal', ['shareUrl' => $biz['url'], 'shareName' => $biz['name']])

@auth
    @include('frontend.business-profile.partials.enquiry-modal', ['biz' => $biz, 'enquiry' => $bizEnquiry])
@else
    @include('frontend.business-profile.partials.login-modal', ['loginName' => $biz['name']])
@endauth

@if($biz['reportRoute'])
    @include('frontend.partials.profile-report-modal', [
        'reportModalId' => 'bizReportModal',
        'reportFormId' => 'bizReportForm',
        'reportLabel' => $biz['typeLabel'],
        'reportAction' => $biz['reportRoute'],
    ])
@endif

<div class="modal fade" id="bizImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Image preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 text-center">
                <img id="bizImageModalImg" src="" alt="" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>

@stack('biz_modals')
@endsection

@include('community.partials.toastr-assets')

@push('scripts')
<script src="{{ asset('assets/js/business-profile.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
