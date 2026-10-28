{{--
  A vendor's product: its pictures beside the price, options and enquiry; then the description,
  specifications, video and similar products, with the seller beside them.
--}}
@extends('frontend.layouts.app')

@php
    use App\Support\BusinessProfilePage;
    use Illuminate\Support\Str;

    $biz = BusinessProfilePage::for('vendor', $vendor);
    $preview = (bool) ($preview ?? false);
    $railAds = collect($railAds ?? []);
    // Whole rows of four (and of two on phones)
    $similarProducts = collect($similarProducts ?? []);
    if ($similarProducts->count() > 4) {
        $similarProducts = $similarProducts->take(intdiv($similarProducts->count(), 4) * 4);
    }

    $images = collect(is_array($product->images) ? $product->images : [])->filter()->map(fn ($path) => asset($path))->values();
    $list = fn ($value) => collect(explode(',', (string) $value))->map(fn ($item) => trim($item))->filter()->values();
    $colors = $list($product->colors);
    $sizes = $list($product->sizes);
    $rows = fn ($raw, string $labelKey, string $valueKey) => collect(is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []))
        ->map(fn ($row) => is_array($row) ? [trim((string) ($row[$labelKey] ?? '')), trim((string) ($row[$valueKey] ?? ''))] : null)
        ->filter(fn ($row) => $row && $row[0] !== '' && $row[1] !== '')
        ->values();
    $specs = $rows($product->specs, 'feature', 'value');
    $tiers = $rows($product->bulk_tiers, 'buy_min', 'price');

    $finalPrice = (float) $product->final_price;
    $basePrice = (float) $product->base_price;
    $discount = (float) $product->discount_percent;
    $inStock = (int) $product->stock_quantity > 0;
    $shipping = (float) $product->shipping_charges;

    $description = trim((string) $product->description);
    preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', (string) $product->youtube_link, $youtube);
    $youtubeId = $youtube[1] ?? null;

    $facts = collect([
        ['Brand', $product->brand],
        ['Category', $productCategory ?? null],
        ['SKU', $product->sku],
        ['Shipping', $shipping > 0 ? '₹'.number_format($shipping, 2) : null],
        // (the seller card has the store's address)
        ['Ships from', Str::lower(trim((string) $product->location)) === Str::lower($biz['address']) ? null : $product->location],
        ['Buy online', $product->is_online_sale ? 'Available' : null],
    ])->filter(fn ($fact) => filled($fact[1]))->values();

    $productUrl = route('store.products.show', ['slug' => $vendor->slug, 'product' => $product->id]);
    $enquiryTarget = auth()->check() ? '#bizEnquiryModal' : '#bizLoginRequiredModal';
    $primaryPhone = $biz['phones'][0] ?? null;
@endphp

@section('meta_title', $product->name.' · '.$biz['name'].' | SoilnWater')
@section('meta_description', Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($description))) ?: $product->name.' from '.$biz['name'].' on SoilnWater', 155))
@if($images->isNotEmpty())
    @section('meta_image', $images->first())
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/business-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="biz-page biz-page--vendor biz-page--product" id="bizProfilePage">
    <div class="biz-container">
        @if($preview)
            <div class="biz-preview-banner"><i class="fa-solid fa-eye" aria-hidden="true"></i> Preview — only you can see this page until the store is published.</div>
        @endif

        <nav class="biz-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
            <span class="biz-breadcrumb__sep" aria-hidden="true">›</span>
            <a href="{{ $biz['listingUrl'] }}">{{ $biz['listingLabel'] }}</a>
            <span class="biz-breadcrumb__sep" aria-hidden="true">›</span>
            <a href="{{ $biz['url'] }}">{{ $biz['name'] }}</a>
            <span class="biz-breadcrumb__sep" aria-hidden="true">›</span>
            <span class="biz-breadcrumb__current" aria-current="page">{{ $product->name }}</span>
        </nav>

        <div class="biz-product-grid">
            <main class="biz-main">
                <section class="biz-section biz-product">
                    {{-- Pictures --}}
                    <div class="biz-gallery" data-biz-gallery>
                        <div class="biz-gallery__stage">
                            @if($images->isNotEmpty())
                                <img src="{{ $images->first() }}" alt="{{ $product->name }}" class="biz-gallery__image" data-biz-gallery-main>
                                <button type="button" class="biz-gallery__zoom" data-bs-toggle="modal" data-bs-target="#bizGalleryModal" aria-label="View the pictures larger">
                                    <i class="fa-solid fa-expand" aria-hidden="true"></i>
                                </button>
                                @if($images->count() > 1)
                                    <button type="button" class="biz-gallery__arrow biz-gallery__arrow--prev" data-biz-gallery-step="-1" aria-label="Previous picture"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                                    <button type="button" class="biz-gallery__arrow biz-gallery__arrow--next" data-biz-gallery-step="1" aria-label="Next picture"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                                @endif
                            @else
                                <span class="biz-gallery__placeholder" aria-hidden="true"><i class="fa-solid fa-box-open"></i></span>
                            @endif
                            @if($discount > 0)
                                <span class="biz-gallery__deal">{{ rtrim(rtrim(number_format($discount, 1), '0'), '.') }}% off</span>
                            @endif
                        </div>
                        @if($images->count() > 1)
                            <div class="biz-gallery__thumbs">
                                @foreach($images as $image)
                                    <button type="button" class="biz-gallery__thumb {{ $loop->first ? 'is-active' : '' }}" data-biz-gallery-thumb="{{ $image }}" aria-label="Picture {{ $loop->iteration }}">
                                        <img src="{{ $image }}" alt="" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Price, options and enquiry --}}
                    <div class="biz-product__info">
                        @if($productCategory ?? $product->brand)
                            <p class="biz-product__eyebrow">{{ collect([$product->brand, $productCategory ?? null])->filter()->implode(' · ') }}</p>
                        @endif
                        <h1 class="biz-product__name">{{ $product->name }}</h1>
                        <p class="biz-product__seller">
                            Sold by <a href="{{ $biz['url'] }}">{{ $biz['name'] }}</a>
                            @if($biz['isPremium'])
                                <span class="biz-badge biz-badge--verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                            @endif
                        </p>

                        <div class="biz-product__price">
                            @if($finalPrice > 0)
                                <strong>₹{{ number_format($finalPrice, 2) }}</strong>
                                @if($basePrice > $finalPrice)
                                    <del>₹{{ number_format($basePrice, 2) }}</del>
                                    <span class="biz-product__off">{{ rtrim(rtrim(number_format($discount ?: (1 - $finalPrice / $basePrice) * 100, 1), '0'), '.') }}% off</span>
                                @endif
                            @else
                                <strong class="biz-product__ask">Price on request</strong>
                            @endif
                        </div>
                        <span class="biz-stock {{ $inStock ? 'biz-stock--in' : 'biz-stock--out' }}">
                            <i class="fa-solid {{ $inStock ? 'fa-circle-check' : 'fa-circle-xmark' }}" aria-hidden="true"></i>
                            {{ $inStock ? 'In stock' : 'Out of stock' }}
                        </span>

                        @if($colors->isNotEmpty())
                            <div class="biz-option">
                                <p class="biz-option__label">Colours</p>
                                <div class="biz-option__values">
                                    @foreach($colors as $color)
                                        <span class="biz-option__value">{{ $color }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($sizes->isNotEmpty())
                            <div class="biz-option">
                                <p class="biz-option__label">Sizes</p>
                                <div class="biz-option__values">
                                    @foreach($sizes as $size)
                                        <span class="biz-option__value">{{ $size }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($tiers->isNotEmpty())
                            <div class="biz-option">
                                <p class="biz-option__label">Bulk prices</p>
                                <div class="biz-tiers">
                                    @foreach($tiers as [$minimum, $price])
                                        <div class="biz-tiers__row"><span>Buy {{ $minimum }}+</span><strong>₹{{ number_format((float) $price, 2) }} each</strong></div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="biz-product__actions">
                            <button type="button" class="biz-btn biz-btn--primary biz-product__enquire" data-bs-toggle="modal" data-bs-target="{{ $enquiryTarget }}">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Enquiry
                            </button>
                            @if($primaryPhone)
                                <a href="tel:{{ $primaryPhone }}" class="biz-btn biz-btn--outline"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call</a>
                            @endif
                            @if($biz['whatsappUrl'])
                                <a href="{{ $biz['whatsappUrl'] }}?text={{ urlencode('Hi, I am interested in '.$product->name.' ('.$productUrl.')') }}" target="_blank" rel="noopener" class="biz-btn biz-btn--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                            @endif
                            <button type="button" class="biz-btn biz-btn--outline" data-bs-toggle="modal" data-bs-target="#bizShareModal" aria-label="Share this product"><i class="fa-solid fa-share-nodes" aria-hidden="true"></i></button>
                        </div>

                        @if($facts->isNotEmpty())
                            <dl class="biz-product__facts">
                                @foreach($facts as [$label, $value])
                                    <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </section>

                @if($description !== '')
                    <section class="biz-section">
                        <h2 class="biz-section__title"><i class="fa-solid fa-align-left" aria-hidden="true"></i> Description</h2>
                        <div class="biz-collapsible" data-biz-collapsible>
                            <p class="biz-product__description">{{ $description }}</p>
                        </div>
                        <button type="button" class="biz-readmore" data-biz-readmore hidden>Read more <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    </section>
                @endif

                @if($specs->isNotEmpty())
                    <section class="biz-section">
                        <h2 class="biz-section__title"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Specifications</h2>
                        <dl class="biz-specs">
                            @foreach($specs as [$feature, $value])
                                <div><dt>{{ $feature }}</dt><dd>{{ $value }}</dd></div>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if($youtubeId || filled($product->video_file))
                    <section class="biz-section">
                        <h2 class="biz-section__title"><i class="fa-solid fa-circle-play" aria-hidden="true"></i> Video</h2>
                        <div class="biz-video">
                            @if($youtubeId)
                                <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" title="{{ $product->name }} video" loading="lazy" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                            @else
                                <video src="{{ asset($product->video_file) }}" controls preload="metadata"></video>
                            @endif
                        </div>
                    </section>
                @endif

                @if($similarProducts->isNotEmpty())
                    <section class="biz-section">
                        <header class="biz-section__head biz-section__head--split">
                            <h2 class="biz-section__title"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i> More from this store</h2>
                            <a href="{{ $biz['url'] }}#biz-products" class="biz-section__link">See all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </header>
                        <div class="biz-items biz-items--four">
                            @include('frontend.business-profile.partials.product-cards', ['products' => $similarProducts, 'slug' => $vendor->slug, 'categoryNames' => []])
                        </div>
                    </section>
                @endif
            </main>

            <aside class="biz-side">
                <div class="biz-card biz-seller">
                    <p class="biz-seller__eyebrow">Sold by</p>
                    <a href="{{ $biz['url'] }}" class="biz-seller__head">
                        <span class="biz-seller__logo">
                            @if($biz['logo'])
                                <img src="{{ $biz['logo'] }}" alt="">
                            @else
                                {{ $biz['initials'] }}
                            @endif
                        </span>
                        <span>
                            <span class="biz-seller__name">{{ $biz['name'] }}</span>
                            @if($biz['place'] !== '')
                                <span class="biz-seller__place"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $biz['place'] }}</span>
                            @endif
                        </span>
                    </a>
                    @if($biz['isPremium'])
                        <div class="biz-hero__badges">
                            <span class="biz-badge biz-badge--premium"><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium</span>
                            <span class="biz-badge biz-badge--verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                        </div>
                    @endif
                    @foreach($biz['phones'] as $phone)
                        <a href="tel:{{ $phone }}" class="biz-card__row"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $phone }}</a>
                    @endforeach
                    @if($biz['address'])
                        <p class="biz-card__text"><i class="fa-solid fa-store" aria-hidden="true"></i> {{ $biz['address'] }}</p>
                    @endif
                    <a href="{{ $biz['url'] }}" class="biz-btn biz-btn--outline biz-seller__visit">Visit store <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                @if($railAds->isNotEmpty())
                    <div class="biz-card biz-card--ads">
                        @include('frontend.store.partials.ads-rail', ['ads' => $railAds, 'railId' => 'bizProductAds'])
                    </div>
                @endif
            </aside>
        </div>
    </div>

    {{-- Phones: call, WhatsApp and enquiry always at hand --}}
    <div class="biz-actionbar">
        @if($primaryPhone)
            <a href="tel:{{ $primaryPhone }}" class="biz-actionbar__btn"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call</a>
        @endif
        @if($biz['whatsappUrl'])
            <a href="{{ $biz['whatsappUrl'] }}?text={{ urlencode('Hi, I am interested in '.$product->name.' ('.$productUrl.')') }}" target="_blank" rel="noopener" class="biz-actionbar__btn biz-actionbar__btn--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
        @endif
        <button type="button" class="biz-actionbar__btn biz-actionbar__btn--primary" data-bs-toggle="modal" data-bs-target="{{ $enquiryTarget }}"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enquire</button>
    </div>
</div>

{{-- Popups --}}
@if($images->isNotEmpty())
    <div class="modal fade biz-gallery-modal" id="bizGalleryModal" tabindex="-1" aria-labelledby="bizGalleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bizGalleryModalLabel">{{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body biz-gallery" data-biz-gallery>
                    <div class="biz-gallery__stage biz-gallery__stage--large">
                        <img src="{{ $images->first() }}" alt="{{ $product->name }}" class="biz-gallery__image" data-biz-gallery-main>
                        @if($images->count() > 1)
                            <button type="button" class="biz-gallery__arrow biz-gallery__arrow--prev" data-biz-gallery-step="-1" aria-label="Previous picture"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                            <button type="button" class="biz-gallery__arrow biz-gallery__arrow--next" data-biz-gallery-step="1" aria-label="Next picture"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                        @endif
                    </div>
                    @if($images->count() > 1)
                        <div class="biz-gallery__thumbs">
                            @foreach($images as $image)
                                <button type="button" class="biz-gallery__thumb {{ $loop->first ? 'is-active' : '' }}" data-biz-gallery-thumb="{{ $image }}" aria-label="Picture {{ $loop->iteration }}">
                                    <img src="{{ $image }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@include('frontend.business-profile.partials.share-modal', ['shareUrl' => $productUrl, 'shareName' => $product->name, 'shareTitle' => 'Share this product'])

@auth
    @include('frontend.business-profile.partials.enquiry-modal', [
        'biz' => $biz,
        'enquiry' => [
            'action' => route('store.products.enquiry', [$vendor->slug, $product->id]),
            'title' => $product->name,
        ],
    ])
@else
    @include('frontend.business-profile.partials.login-modal', ['loginName' => $biz['name']])
@endauth
@endsection

@include('community.partials.toastr-assets')

@push('scripts')
<script src="{{ asset('assets/js/business-profile.js') }}?v={{ now()->timestamp }}" defer></script>
@endpush
