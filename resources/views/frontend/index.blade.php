@extends('frontend.layouts.app')

@section('content')
@php
  $sectionToggles = data_get($homepageSetting ?? null, 'section_toggles', []);
  $showTopVendors = !empty($sectionToggles['top_vendors']) && $sectionToggles['top_vendors'];
  $showPremiumOptions = data_get($sectionToggles, 'premium_options', true);
  $showPopularPropertiesNearGreenwood = !empty($sectionToggles['popular_properties_near_greenwood']) && $sectionToggles['popular_properties_near_greenwood'];
  $topVendorsHeaderAdsList = collect($topVendorsHeaderAds ?? []);
  $topVendorsList = collect($topVendors ?? []);
  $topVendorsSideAdsList = collect($topVendorsSideAds ?? [])->values();
  $topVendorSlideSize = $topVendorsSideAdsList->isNotEmpty() ? 5 : 6;
  $topVendorSlides = $topVendorsList->chunk($topVendorSlideSize);
  $heroBannerImage = data_get($homepageSetting ?? null, 'hero_banner_image');
  $heroButtonText = data_get($homepageSetting ?? null, 'hero_button_text', 'Advertise Now');
  $heroButtonLink = data_get($homepageSetting ?? null, 'hero_button_link', '#');
  $vendorEnquiryCategoryTree = ($vendorEnquiryCategories ?? collect())
    ->map(function ($category) {
      return [
        'id' => $category->id,
        'name' => $category->name,
        'children' => $category->children->map(function ($child) {
          return [
            'id' => $child->id,
            'name' => $child->name,
          ];
        })->values()->all(),
      ];
    })->values()->all();
  $consultantEnquiryCategoryTree = ($consultantEnquiryCategories ?? collect())
    ->map(function ($category) {
      return [
        'id' => $category->id,
        'name' => $category->name,
        'children' => $category->children->map(function ($child) {
          return [
            'id' => $child->id,
            'name' => $child->name,
          ];
        })->values()->all(),
      ];
    })->values()->all();
  $serviceProviderEnquiryCategoryTree = ($service_providerCategories ?? collect())
    ->map(function ($category) {
      return [
        'id' => $category->id,
        'name' => $category->name,
        'children' => $category->children->map(function ($child) {
          return [
            'id' => $child->id,
            'name' => $child->name,
          ];
        })->values()->all(),
      ];
    })->values()->all();
@endphp

<div id="post-ad" class="visually-hidden" aria-hidden="true"></div>
<div id="post-offer" class="visually-hidden" aria-hidden="true"></div>

<section class="hero">
  <div class="hero-stars">✦ ✦<br>✦</div>
  <div class="hero-content">
    <h1>Grow Your Business with a Professional Marketplace Presence</h1>
    <a href="{{ $heroButtonLink ?: "#" }}" class="btn-yellow">{{ $heroButtonText ?: "Advertise Now" }}</a>
  </div>

  <!-- SVG illustration: megaphone + shopping bags + coins -->
  @if($heroBannerImage)
    <div class="hero-illus"><img src="{{ asset($heroBannerImage) }}" alt="Hero banner" style="max-width:100%;height:auto;"></div>
  @else
  <div class="hero-illus">
    <svg viewBox="0 0 380 160" xmlns="http://www.w3.org/2000/svg" style="overflow:visible;">
      <!-- Ground grass strip -->
      <ellipse cx="200" cy="155" rx="190" ry="14" fill="#a5d6a7" opacity=".5"/>

      <!-- Gold coins pile right -->
      <ellipse cx="338" cy="138" rx="28" ry="8" fill="#fdd835"/>
      <ellipse cx="338" cy="130" rx="24" ry="7" fill="#f9a825"/>
      <ellipse cx="338" cy="123" rx="20" ry="6" fill="#fdd835"/>
      <ellipse cx="338" cy="117" rx="16" ry="5" fill="#f9a825"/>
      <text x="330" y="121" font-size="9" fill="#e65100" font-weight="bold">$</text>

      <!-- Small coins left scatter -->
      <circle cx="80" cy="145" r="9" fill="#fdd835" stroke="#f9a825" stroke-width="1.5"/>
      <text x="76" y="149" font-size="8" fill="#e65100" font-weight="bold">$</text>
      <circle cx="100" cy="138" r="7" fill="#fdd835" stroke="#f9a825" stroke-width="1.2"/>
      <circle cx="65" cy="135" r="6" fill="#f9a825"/>

      <!-- Red shopping bag -->
      <rect x="148" y="70" width="52" height="64" rx="5" fill="#e53935"/>
      <rect x="158" y="58" width="32" height="14" rx="7" fill="none" stroke="#c62828" stroke-width="4"/>
      <rect x="160" y="94" width="28" height="3" rx="1.5" fill="#ef9a9a"/>
      <text x="162" y="115" font-size="18" fill="#fff" opacity=".3">🛍</text>

      <!-- Blue/teal gift box -->
      <rect x="214" y="88" width="46" height="46" rx="5" fill="#0288d1"/>
      <rect x="214" y="88" width="46" height="14" rx="3" fill="#0277bd"/>
      <rect x="235" y="88" width="4" height="46" fill="#fff" opacity=".35"/>
      <rect x="214" y="100" width="46" height="4" fill="#fff" opacity=".35"/>
      <!-- ribbon bow -->
      <path d="M237 88 C230 80 222 82 224 88Z" fill="#4fc3f7"/>
      <path d="M237 88 C244 80 252 82 250 88Z" fill="#4fc3f7"/>
      <circle cx="237" cy="88" r="4" fill="#e1f5fe"/>

      <!-- Green shopping bag -->
      <rect x="270" y="62" width="48" height="72" rx="5" fill="#43a047"/>
      <rect x="280" y="50" width="28" height="14" rx="7" fill="none" stroke="#2e7d32" stroke-width="4"/>
      <rect x="282" y="96" width="24" height="3" rx="1.5" fill="#a5d6a7"/>

      <!-- Yellow/tan envelope bag -->
      <rect x="105" y="82" width="42" height="52" rx="5" fill="#f9a825"/>
      <rect x="105" y="82" width="42" height="14" rx="3" fill="#f57f17"/>
      <!-- envelope flap -->
      <path d="M105 96 L126 112 L147 96Z" fill="#ffe082" opacity=".7"/>

      <!-- Megaphone (centre-left of illustration) -->
      <g transform="translate(30,40) rotate(-18)">
        <!-- cone body -->
        <path d="M0 28 L60 8 L60 52 Z" fill="#1565c0"/>
        <path d="M0 28 L60 8 L60 52 Z" fill="url(#megaGrad)"/>
        <!-- mouthpiece cylinder -->
        <rect x="-18" y="20" width="22" height="16" rx="4" fill="#1976d2"/>
        <!-- rim circle -->
        <circle cx="60" cy="30" r="22" fill="#1976d2" stroke="#0d47a1" stroke-width="2"/>
        <circle cx="60" cy="30" r="15" fill="#1565c0"/>
        <!-- sound waves -->
        <path d="M82 18 Q96 30 82 42" stroke="#90caf9" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        <path d="M88 12 Q106 30 88 48" stroke="#64b5f6" stroke-width="2" fill="none" stroke-linecap="round" opacity=".7"/>
      </g>
      <defs>
        <linearGradient id="megaGrad" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stop-color="#1976d2"/>
          <stop offset="100%" stop-color="#0d47a1"/>
        </linearGradient>
      </defs>

      <!-- Stars / sparkles scattered -->
      <text x="132" y="52" font-size="14" fill="#f9a825">✦</text>
      <text x="305" y="55" font-size="10" fill="#81c784">✦</text>
      <text x="320" y="80" font-size="8" fill="#f9a825">✦</text>
      <text x="108" y="75" font-size="9" fill="#42a5f5">✦</text>

      <!-- Butterflies -->
      <text x="340" y="52" font-size="13">🦋</text>
      <text x="356" y="72" font-size="10">🦋</text>
    </svg>
  </div>
  @endif
</section>


<!-- ══════════════════════════════════════════════════
     CATEGORY BAR  — fixed categories with enhanced icons
══════════════════════════════════════════════════ -->
<div class="cat-bar">
  <div class="cat-scroller-wrap">
    <div class="cat-bar-inner" id="catScroller">
      <a href="/ads-market">
        <div class="cat-item active">
          <div class="cat-icon">
            <i class="fa-solid fa-bullhorn cat-icon-i cat-ads"></i>
          </div>
          <span>ADS</span>
        </div>
      </a>
      <a href="/offers-market">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-tags cat-icon-i cat-offers"></i>
          </div>
          <span>OFFERS</span>
        </div>
      </a>
      {{--
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-cart-shopping cat-icon-i cat-ecommerce"></i>
          </div>
          <span>E-COMMERCE</span>
        </div>
      </a>
      --}}
      <a href="{{ route('frontend.vendors.index') }}">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-store cat-icon-i cat-vendors"></i>
          </div>
          <span>VENDORS</span>
        </div>
      </a>
      <a href="{{ route('frontend.consultants.index') }}">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-user-tie cat-icon-i cat-consultants"></i>
          </div>
          <span>CONSULTANTS</span>
        </div>
      </a>
      <a href="{{ route('frontend.service_providers.index') }}">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-screwdriver-wrench cat-icon-i cat-service"></i>
          </div>
          <span>SERVICES</span>
        </div>
      </a>
      {{--
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-building cat-icon-i cat-builders"></i>
          </div>
          <span>BUILDER &amp; DEVELOPERS</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-diagram-project cat-icon-i cat-projects"></i>
          </div>
          <span>PROJECTS</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-house-chimney cat-icon-i cat-properties"></i>
          </div>
          <span>PROPERTIES</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-hotel cat-icon-i cat-hotel"></i>
          </div>
          <span>HOTEL/HOMESTAY</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-circle-question cat-icon-i cat-enquiry"></i>
          </div>
          <span>ENQUIRY</span>
        </div>
      </a>
      --}}
    </div><!-- /cat-bar-inner -->
  </div>
</div>

<!-- ══════════════════════════════════════════════════
     MAIN CONTENT  (from Image 2 layout with sidebar)
══════════════════════════════════════════════════ -->
<div class="main-wrap">

  <!-- LEFT / MAIN COLUMN -->
  <div class="main-col">

    <!-- Top fold layout: categories + listings with right sidebar ads -->
      @if(!empty($sectionToggles['sponsored_listings']) && $sectionToggles['sponsored_listings'] || (!empty($sectionToggles['top_categories']) && $sectionToggles['top_categories']))
      <div class="top-fold-layout">
        <div class="row g-3 align-items-start top-fold-upper">
        <div class="col-12 col-lg-9 top-fold-main">
          <!-- Top Categories + Boost Ad -->
          @if(!empty($sectionToggles['top_categories']) && $sectionToggles['top_categories'])
            
            <div class="sec">
              <div class="sec-head">
                <div class="sec-title"><i class="fa-solid fa-layer-group"></i> Top Categories</div>
                <a class="view-all" href="#">Learn More ▶</a>
              </div>
              <div class="ad-slider auto-ad-slider top-ad-slider top-categories-ad-slider" data-pause-on-hover="false">
                @forelse(($topCategoriesSliderAds ?? collect()) as $ad)
                  <div class="ad-slide" style="margin-bottom:10px;">
                    <img
                      src="{{ asset($ad->final_image) }}"
                      alt="{{ $ad->title }}"
                      data-ad-id="{{ $ad->id }}"
                      data-ad-url="{{ $ad->shareUrl() }}"
                      data-ad-description="Special marketplace ad available now."
                      style="width:100%;height:auto;display:block;border-radius:8px;"
                    >
                  </div>
                @empty
                  <div class="ad-slide" style="background:linear-gradient(90deg,#e3f2fd,#bbdefb);border-radius:8px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <div>
                      <div style="font-size:17px;font-weight:800;color:#1a3a5c;">Get More Enquiries</div>
                      <div style="font-size:12px;color:#555;">Approved admin ads for size 879×118 will appear here.</div>
                    </div>
                    <button class="btn-yellow" style="white-space:nowrap;">Boost Listing</button>
                  </div>
                @endforelse
              </div>
            </div>
          @endif

          
          <!-- Sponsored Listings -->
          @if(!empty($sectionToggles['sponsored_listings']) && $sectionToggles['sponsored_listings'])
            <div class="sec">
              <div class="sec-head">
                <div class="sec-title"><span class="icon"><i class="fa-solid fa-bullhorn"></i></span> Sponsored Listings</div>
                <a class="view-all" href="#">VIEW ALL ▶</a>
              </div>
              <div class="sponsored-grid sponsored-grid-with-ad">
                <div class="col d-flex">
                  <div class="sp-card">
                  <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=900&q=80" alt="Watch">
                  <div class="sp-card-body"><p>Luxury Watch Sale</p><div class="sp-badge">Sponsored</div></div>
                  </div>
                </div>
                <div class="col d-flex">
                  <div class="sp-card">
                  <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&q=80" alt="Food">
                  <div class="sp-card-body"><p>Local Food Mart Offers</p><div class="sp-badge">Sponsored</div></div>
                  </div>
                </div>
                <div class="col d-flex">
                  <div class="sp-card">
                  <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=900&q=80" alt="Property">
                  <div class="sp-card-body"><p>Property For Sale</p><div class="sp-badge">Sponsored</div></div>
                  </div>
                </div>
                <div class="col d-flex">
                  <div class="sp-card">
                  <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=900&q=80" alt="Services">
                  <div class="sp-card-body"><p>Top Home Services</p><div class="sp-badge">Sponsored</div></div>
                  </div>
                </div>
                <div class="col d-flex">
                  <div class="sp-card">
                  <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=900&q=80" alt="Business">
                  <div class="sp-card-body"><p>Follow the Best Sellers</p><div class="sp-badge">Sponsored</div></div>
                  </div>
                </div>
                <div class="col d-flex">
                  <div class="sp-card">
                  <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=900&q=80" alt="Contract">
                  <div class="sp-card-body"><p>Our Latest Contracts</p><div class="sp-badge">Sponsored</div></div>
                  </div>
                </div>
              </div>
            </div>
          @endif

        </div>

        <aside class="col-12 col-lg-3 top-sidebar-ads">
         @if(!empty($sectionToggles['top_categories']) && $sectionToggles['top_categories'])

          <div class="ad-slider auto-ad-slider business-side-slider">
            @forelse(($topSidebarSliderAds ?? collect()) as $ad)
              <div class="side-card ad-slide">
                <img class="side-card-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
              </div>
            @empty
              <div class="side-card ad-slide">
                <img class="side-card-img" src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=500&q=70" alt="Advertise your business">
                <div class="side-card-body">
                  <h3>Advertise Your Business</h3>
                  <p>Approved admin ads for size 296×292 will appear here.</p>
                  <button class="btn-learn">Start Campaign</button>
                </div>
              </div>
            @endforelse
          </div>
          @endif
          @if(!empty($sectionToggles['sponsored_listings']) && $sectionToggles['sponsored_listings'])
          <div class="ad-slider auto-ad-slider dream-home-side-slider sponsored-listings-ad-slider" data-show-arrows="true" data-pause-on-hover="false">
            @forelse(($sponsoredListingsAds ?? collect()) as $ad)
              <div class="side-card ad-slide sponsored-listings-ad-card" aria-label="{{ $ad->title }}">
                <img class="side-card-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
              </div>
            @empty
              <div class="side-card ad-slide sponsored-listings-ad-card">
                <img class="side-card-img" src="{{ asset('assets/images/ad-sample.png') }}" alt="Sponsored listing ad">
                <div class="side-card-body">
                  <h3>Sponsored Listing Ad</h3>
                  <p>Approved admin ads for size 296×624 will appear here.</p>
                </div>
              </div>
            @endforelse
          </div>
          @endif
        </aside>
        </div>

        @if(!empty($sectionToggles['sponsored_listings']) && $sectionToggles['sponsored_listings'])
        <div class="ad-slider auto-ad-slider top-ad-slider premium-wide-slider" aria-label="Premium marketplace campaign slider" data-show-arrows="true" data-pause-on-hover="false">
          @forelse(($belowSponsoredSliderAds ?? collect()) as $ad)
            <div class="ad-slide premium-marketplace-slide">
              <img
                src="{{ asset($ad->final_image) }}"
                alt="{{ $ad->title }}"
                data-ad-id="{{ $ad->id }}"
                data-ad-url="{{ $ad->shareUrl() }}"
                data-ad-description="Special marketplace ad available now."
                style="width:100%;height:auto;display:block;border-radius:18px;"
              >
            </div>
          @empty
            <div class="adv-strip-dark ad-slide premium-marketplace-slide">
              <div class="adv-text">
                <h3>Grow Faster with Premium Marketplace Ads</h3>
                <p>Reach ready-to-buy customers with top placements across the marketplace.</p>
              </div>
              <button class="ad-slot-btn">Start Campaign</button>
            </div>
            <div class="adv-strip-dark ad-slide premium-marketplace-slide">
              <div class="adv-text">
                <h3>Boost Daily Enquiries Automatically</h3>
                <p>Keep your business visible 24/7 with rotating premium ad positions.</p>
              </div>
              <button class="ad-slot-btn">View Plans</button>
            </div>
            <div class="adv-strip-dark ad-slide premium-marketplace-slide">
              <div class="adv-text">
                <h3>Get Arrow Navigation + Auto Rotation</h3>
                <p>Switch slides manually with arrows or let the banner rotate on its own.</p>
              </div>
              <button class="ad-slot-btn">Book Premium Slot</button>
            </div>
          @endforelse
        </div>
        @endif
        @if(!empty($sectionToggles['ecommerce']) && $sectionToggles['ecommerce'])
        <div class="row g-3 align-items-stretch ecommerce-with-side-ad">
          <div class="col-12">
            <!-- E-Commerce Section -->
            <div class="sec ecommerce-sec">
              <div class="sec-head">
                <div class="sec-title"><span class="icon"><i class="fa-solid fa-cart-shopping"></i></span> E-Commerce</div>
                <a class="view-all" href="#">VIEW ALL ▶</a>
              </div>
              <div class="row g-3 ecommerce-layout">
                <div class="col-12 col-lg-9">
                  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 ecommerce-product-grid">
                    <div class="col">
                      <div class="card h-100 ecommerce-bs-card">
                        <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=900&q=85" class="card-img-top" alt="Phone">
                        <div class="card-body">
                          <p class="card-title">Smartphone</p>
                          <div class="prod-price">₹15,999</div>
                          <div class="prod-badge">Sponsored</div>
                        </div>
                      </div>
                    </div>
                    <div class="col">
                      <div class="card h-100 ecommerce-bs-card">
                        <img src="https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=900&q=85" class="card-img-top" alt="Bag">
                        <div class="card-body">
                          <p class="card-title">Stylish Handbag</p>
                          <div class="prod-price">₹2,499</div>
                          <div class="prod-badge">Sponsored</div>
                        </div>
                      </div>
                    </div>
                    <div class="col">
                      <div class="card h-100 ecommerce-bs-card">
                        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=900&q=85" class="card-img-top" alt="Shoes">
                        <div class="card-body">
                          <p class="card-title">Running Shoes</p>
                          <div class="prod-price">₹3,200</div>
                          <div class="prod-badge">Sponsored</div>
                        </div>
                      </div>
                    </div>
                    <div class="col">
                      <div class="card h-100 ecommerce-bs-card">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=900&q=85" class="card-img-top" alt="Headphone">
                        <div class="card-body">
                          <p class="card-title">Wireless Headphone</p>
                          <div class="prod-price">₹1,800</div>
                          <div class="prod-badge">Sponsored</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-lg-3 col-12 col-lg-3 mt-4">
                  <div class="festival-ad-slider ad-slider auto-ad-slider h-100" aria-label="Festival Campaign Deals slider">
                    @forelse(($ecommerceSideSliderAds ?? collect()) as $ad)
                      <div class="card h-100 ecommerce-ad-bs-card ecommerce-ad-image-card ad-slide">
                        <img src="{{ asset($ad->final_image) }}" class="card-img-top ecommerce-ad-full-img" alt="{{ $ad->title }}" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
                      </div>
                    @empty
                      <div class="card h-100 ecommerce-ad-bs-card ad-slide">
                        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&q=85" class="card-img-top" alt="Festival Campaign Deals vegetables">
                        <div class="card-body">
                          <p class="card-title">Festival Campaign Deals</p>
                          <div class="prod-price">High-Intent Buyers</div>
                          <div class="prod-badge">Sponsored</div>
                          <button class="btn-learn">View Offer</button>
                        </div>
                      </div>
                      <div class="card h-100 ecommerce-ad-bs-card ad-slide">
                        <img src="https://images.unsplash.com/photo-1471193945509-9ad0617afabf?w=900&q=85" class="card-img-top" alt="Festive ad showcase fruits">
                        <div class="card-body">
                          <p class="card-title">Festive Spotlight</p>
                          <div class="prod-price">Run Local Promotions</div>
                          <div class="prod-badge">Sponsored</div>
                          <button class="btn-learn">Book Campaign</button>
                        </div>
                      </div>
                      <div class="card h-100 ecommerce-ad-bs-card ad-slide">
                        <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=900&q=85" class="card-img-top" alt="Seasonal produce campaign">
                        <div class="card-body">
                          <p class="card-title">Seasonal Deal Ads</p>
                          <div class="prod-price">Boost Daily Leads</div>
                          <div class="prod-badge">Sponsored</div>
                          <button class="btn-learn">Start Now</button>
                        </div>
                      </div>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
      @endif

    <!-- Recent Ads Section -->
    @if(!empty($sectionToggles['recent_ads']) && $sectionToggles['recent_ads'])

      <div class="sec recent-ads-section">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-rectangle-ad"></i></span> Recent Ads</div>
          <a class="view-all" href="{{ route('frontend.ads.index') }}">VIEW ALL ▶</a>
        </div>
        <div class="ad-slider auto-ad-slider recent-ads-slider" data-show-arrows="true" data-show-dots="false" aria-label="Recent approved ads slider">
          @forelse(($recentApprovedAds ?? collect())->chunk(6) as $recentAdsChunk)
            <div class="ad-slide">
              <div class="product-grid-4 recent-ads-grid">
                @foreach($recentAdsChunk as $recentAd)
                  @php
                    $selectedCategoryNames = $selectedCategoryNamesByRecentAdId[$recentAd->id] ?? [];
                    if ($selectedCategoryNames === [] && $recentAd->category?->name) {
                      $selectedCategoryNames = [$recentAd->category->name];
                    }

                    $moduleLabels = \App\Support\ModulePermissions::modules();
                    $selectedServiceNames = collect($recentAd->selected_modules ?? [])
                      ->filter(fn ($key) => is_string($key) && isset($moduleLabels[$key]))
                      ->map(fn ($key) => $moduleLabels[$key])
                      ->values()
                      ->all();
                  @endphp
                  <article class="prod-card recent-ad-card"
                    data-ad-description="{{ $recentAd->short_description ?: 'Special marketplace ad available now.' }}"
                    data-ad-url="{{ $recentAd->shareUrl() }}"
                    data-ad-meta="{{ implode(', ', $selectedCategoryNames) }}"
                    data-ad-services="{{ implode(', ', $selectedServiceNames) }}"
                  >
                    <img src="{{ asset($recentAd->final_image) }}" alt="{{ $recentAd->title }}" data-ad-id="{{ $recentAd->id }}" data-ad-url="{{ $recentAd->shareUrl() }}" data-ad-description="{{ $recentAd->short_description ?: 'Special marketplace ad available now.' }}">
                    <div class="prod-card-body">
                      <h6 class="mb-1 offer-coupon-title">{{ $recentAd->title }}</h6>
                      <span class="recent-ad-meta">
                        <i class="fa-solid fa-layer-group"></i>
                        {{ ($selectedCategoryNames !== [] ? implode(', ', $selectedCategoryNames) : 'Uncategorized') }} • {{ $recentAd->created_at?->format('d M Y') ?? 'N/A' }}
                      </span>
                    </div>
                  </article>
                @endforeach
              </div>
            </div>
          @empty
            <div class="ad-slide">
              <div class="product-grid-4 recent-ads-grid">
                <article class="prod-card recent-ad-card">
                  <img src="{{ asset('assets/images/ad-sample.png') }}" alt="Recent approved ad placeholder">
                  <div class="prod-card-body">
                    <p>No approved ads available yet</p>
                    <span class="recent-ad-meta"><i class="fa-solid fa-circle-info"></i> New approved ads will appear here.</span>
                    <button class="btn btn-sm" type="button">View Ad</button>
                  </div>
                </article>
              </div>
            </div>
          @endforelse
        </div>
      </div>
    @endif

    <!-- Offer & Discount Section -->
    @if(!empty($sectionToggles['offer_discount']) && $sectionToggles['offer_discount'])

      <div class="sec promo-slider-section">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-tags"></i></span> Offer &amp; Discount</div>
          <a class="view-all" href="{{ route('frontend.offers.index') }}">VIEW ALL ▶</a>
        </div>
        <div class="promo-layout row g-3 g-lg-4 align-items-stretch">
          <div class="col-12 d-flex">
            <div class="offer-coupon-wrap w-100">
              <div class="ad-slider auto-ad-slider combo-deals-slider" data-show-dots="true" data-show-arrows="false" aria-label="Combo deals slider">
                @forelse(($offerDiscountTopAds ?? collect()) as $ad)
                  <div class="offer-coupon-banner offer-discount-image-slide ad-slide">
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="offer-discount-top-image" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
                  </div>
                @empty
                  <div class="offer-coupon-banner ad-slide">
                    <span class="promo-tag">Combo Deals</span>
                    <h3>Buy more, save more on agri + home products</h3>
                    <p>Unlock bundled discounts from trusted sellers and apply coupon codes at checkout.</p>
                  </div>
                @endforelse
              </div>
              <div class="ad-slider auto-ad-slider offer-coupon-grid-slider" data-show-arrows="true" data-show-dots="false" aria-label="Offer coupon cards slider">
                @forelse ($offers->chunk(6) as $offerChunk)
                  <div class="ad-slide">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-6 g-1 offer-coupon-grid">
                      @foreach ($offerChunk as $offer)
                        @php($offerCardDescription = trim(preg_replace('/\s+/', ' ', $offer->short_description ?: 'Special marketplace offer available now.')))
                        <div class="col">
                          <article
                            class="card h-100 shadow-sm border-0 offer-coupon-card js-offer-modal-trigger"
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
                              <span class="badge text-bg-primary w-fit">{{ $offer->discount_tag }}</span>
                              <h4 class="h6 mb-1 offer-coupon-title">{{ $offer->title }}</h4>
                              <p class="small text-muted mb-2 offer-coupon-description">{{ $offerCardDescription }}</p>
                            </div>
                          </article>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @empty
                  <div class="ad-slide">
                    <div class="row row-cols-1 g-1 offer-coupon-grid">
                      <div class="col">
                        <article class="card h-100 shadow-sm border-0 offer-coupon-card">
                          <div class="card-body d-flex flex-column gap-2 justify-content-center text-center">
                            <h4 class="h6 mb-1">No active offers available</h4>
                            <p class="small text-muted mb-2">Please check back later for fresh deals.</p>
                          </div>
                        </article>
                      </div>
                    </div>
                  </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif



    <div class="modal fade" id="vendorEnquiryModal" tabindex="-1" aria-labelledby="vendorEnquiryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vendor-enquiry-modal-content">
          <div class="modal-header border-0 pb-2">
            <h2 class="modal-title fs-5" id="vendorEnquiryModalLabel">Vendor Enquiry</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-0">
            <form id="vendorEnquiryForm">
              @csrf
              <div class="mb-3">
                <label class="form-label" for="vendorEnquiryEmail">Email</label>
                <input type="email" class="form-control" id="vendorEnquiryEmail" name="email" value="{{ auth()->user()?->email }}" readonly required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="vendorEnquiryPhone">Phone Number</label>
                <input type="text" class="form-control" id="vendorEnquiryPhone" name="phone_number" value="{{ auth()->user()?->phone_number }}" readonly required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="vendorEnquiryPreferredContact">Way to Connect</label>
                <select class="form-select" id="vendorEnquiryPreferredContact" name="preferred_contact" required>
                  <option value="">Select option</option>
                  <option value="text">Text</option>
                  <option value="whatsapp">WhatsApp</option>
                  <option value="call">Call</option>
                  <option value="email">Email</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="vendorEnquiryCategory">Vendor Category</label>
                <select class="form-select" id="vendorEnquiryCategory" name="category_id" required>
                  <option value="">Select category</option>
                  @foreach(($vendorEnquiryCategories ?? collect()) as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="vendorEnquirySubCategory">Sub Category</label>
                <select class="form-select" id="vendorEnquirySubCategory" name="subcategory_id" required disabled>
                  <option value="">Select sub category</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="vendorEnquiryReason">Requirement Details</label>
                <textarea class="form-control" id="vendorEnquiryReason" name="reason" rows="4" maxlength="2000" placeholder="Please share your requirement, location and preferred timeline." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100" id="vendorEnquirySubmitBtn">
                <span class="js-vendor-enquiry-btn-text">Send Enquiry</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none js-vendor-enquiry-btn-loader" role="status" aria-hidden="true"></span>
                <span class="ms-1 d-none js-vendor-enquiry-btn-sending">Sending...</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <div class="modal fade" id="consultantEnquiryModal" tabindex="-1" aria-labelledby="consultantEnquiryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content vendor-enquiry-modal-content">
          <div class="modal-header border-0 pb-2">
            <h2 class="modal-title fs-5" id="consultantEnquiryModalLabel">Consultant Enquiry</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-0">
            <form id="consultantEnquiryForm" enctype="multipart/form-data">
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryName">Name *</label>
                  <input type="text" class="form-control" id="consultantEnquiryName" name="client_name" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryPhone">Phone Number *</label>
                  <input type="text" class="form-control" id="consultantEnquiryPhone" name="phone_number" value="{{ auth()->user()?->phone_number }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryEmail">Email *</label>
                  <input type="email" class="form-control" id="consultantEnquiryEmail" name="email" value="{{ auth()->user()?->email }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryOccupation">Occupation</label>
                  <input type="text" class="form-control" id="consultantEnquiryOccupation" name="occupation" placeholder="Your occupation">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryDob">DOB</label>
                  <input type="date" class="form-control" id="consultantEnquiryDob" name="date_of_birth" value="{{ auth()->user()?->date_of_birth?->format('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryImage">Upload image</label>
                  <input type="file" class="form-control" id="consultantEnquiryImage" name="image" accept="image/*">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquiryCategory">Consultant Category *</label>
                  <select class="form-select" id="consultantEnquiryCategory" name="category_id" required>
                    <option value="">Select category</option>
                    @foreach(($consultantEnquiryCategories ?? collect()) as $category)
                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="consultantEnquirySubCategory">Sub Category *</label>
                  <select class="form-select" id="consultantEnquirySubCategory" name="subcategory_id" required disabled>
                    <option value="">Select sub category</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label" for="consultantEnquiryQuestion">Question *</label>
                  <textarea class="form-control" id="consultantEnquiryQuestion" name="question" rows="4" maxlength="2000" placeholder="Write your question for the consultant." required></textarea>
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100 mt-3" id="consultantEnquirySubmitBtn">
                <span class="js-consultant-enquiry-btn-text">Send Enquiry</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none js-consultant-enquiry-btn-loader" role="status" aria-hidden="true"></span>
                <span class="ms-1 d-none js-consultant-enquiry-btn-sending">Sending...</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="serviceProviderEnquiryModal" tabindex="-1" aria-labelledby="serviceProviderEnquiryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content vendor-enquiry-modal-content">
          <div class="modal-header border-0 pb-2">
            <h2 class="modal-title fs-5" id="serviceProviderEnquiryModalLabel">Service Enquiry</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-0">
            <form id="serviceProviderEnquiryForm" enctype="multipart/form-data">
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryName">Name *</label>
                  <input type="text" class="form-control" id="serviceProviderEnquiryName" name="client_name" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryPhone">Phone Number *</label>
                  <input type="text" class="form-control" id="serviceProviderEnquiryPhone" name="phone_number" value="{{ auth()->user()?->phone_number }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryEmail">Email *</label>
                  <input type="email" class="form-control" id="serviceProviderEnquiryEmail" name="email" value="{{ auth()->user()?->email }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryOccupation">Occupation</label>
                  <input type="text" class="form-control" id="serviceProviderEnquiryOccupation" name="occupation" placeholder="Your occupation">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryDob">DOB</label>
                  <input type="date" class="form-control" id="serviceProviderEnquiryDob" name="date_of_birth" value="{{ auth()->user()?->date_of_birth?->format('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryImage">Upload image</label>
                  <input type="file" class="form-control" id="serviceProviderEnquiryImage" name="image" accept="image/*">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquiryCategory">Service Category *</label>
                  <select class="form-select" id="serviceProviderEnquiryCategory" name="category_id" required>
                    <option value="">Select category</option>
                    @foreach(($service_providerCategories ?? collect()) as $category)
                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="serviceProviderEnquirySubCategory">Sub Category *</label>
                  <select class="form-select" id="serviceProviderEnquirySubCategory" name="subcategory_id" required disabled>
                    <option value="">Select sub category</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label" for="serviceProviderEnquiryQuestion">Question *</label>
                  <textarea class="form-control" id="serviceProviderEnquiryQuestion" name="question" rows="4" maxlength="2000" placeholder="Write your question for the service." required></textarea>
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100 mt-3" id="serviceProviderEnquirySubmitBtn">
                <span class="js-service-provider-enquiry-btn-text">Send Enquiry</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none js-service-provider-enquiry-btn-loader" role="status" aria-hidden="true"></span>
                <span class="ms-1 d-none js-service-provider-enquiry-btn-sending">Sending...</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade offer-details-modal" id="offerDetailsModal" tabindex="-1" aria-labelledby="offerDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header border-0 pb-2">
            <h2 class="modal-title fs-5" id="offerDetailsModalLabel">Offer Details</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-1">
            <img id="offerDetailsModalImage" src="" alt="Offer image" class="img-fluid rounded mb-3 d-none offer-details-modal-image">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <span class="badge text-bg-primary" id="offerDetailsModalDiscount"></span>
              <span class="coupon-code mb-0 d-none" id="offerDetailsModalCoupon"></span>
            </div>
            <h3 class="h4 mb-2" id="offerDetailsModalTitle"></h3>
            <p class="text-muted mb-3" id="offerDetailsModalDescription"></p>
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
      </div>
    </div>

    <!-- Explore Products Near You (SSNY style) -->
    @if(!empty($sectionToggles['explore_products']) && $sectionToggles['explore_products'])

      <div class="sec explore-redesign">
        <div class="sec-head">
          <div class="sec-title">Explore Products Near You</div>
          <a class="view-all" href="#">Learn More ▶</a>
        </div>
        <div class="ad-slider auto-ad-slider top-ad-slider" aria-label="Handpicked local ads slider">
          @forelse(($exploreProductsAds ?? collect()) as $ad)
            <div class="explore-top-banner ad-slide explore-ad-image-slide">
              <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="explore-ad-full-img" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
            </div>
          @empty
            <div class="explore-top-banner ad-slide">
              <div>
                <h4>Handpicked Local Deals Near Greenwood</h4>
                <p>Discover trusted sellers with better delivery speed and verified ratings.</p>
              </div>
              <button class="ad-slot-btn">Get Featured</button>
            </div>
          @endforelse
        </div>
        <div class="explore-grid">
          <div class="exp-card">
            <img src="https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=300&q=70" alt="Agri">
            <div class="exp-card-body"><h4>Greenwood Agri…</h4><div class="sub">Agri Equipment</div><div class="exp-price">₹11,490 onwards</div></div>
          </div>
          <div class="exp-card">
            <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=300&q=70" alt="Solar">
            <div class="exp-card-body"><h4>Springfield Solar…</h4><div class="sub">Solar Panels</div><div class="exp-price">₹14,490 onwards</div></div>
          </div>
          <div class="exp-card">
            <img src="https://images.unsplash.com/photo-1495107334309-fcf20504a5ab?w=300&q=70" alt="Poultry">
            <div class="exp-card-body"><h4>Patel Poultry Greens</h4><div class="sub">Poultry Feed</div><div class="exp-price">₹90–₹95/dozen</div></div>
          </div>
          <div class="exp-card">
            <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=300&q=70" alt="Tiles">
            <div class="exp-card-body"><h4>Prabhu Tiles &amp; Bricks</h4><div class="sub">Construction</div><div class="exp-price">₹79–₹95/unit</div></div>
          </div>
          <div class="exp-card">
            <img src="https://images.unsplash.com/photo-1556742400-b5b7c512bc0d?w=300&q=70" alt="Bakers">
            <div class="exp-card-body"><h4>Bloom Bakery</h4><div class="sub">Bakery Goods</div><div class="exp-price">₹69 onwards</div></div>
          </div>
        </div>
      </div>
    @endif
    <!-- Top Vendors + Properties with Right Ad Rail -->
    <?php if ($showTopVendors || $showPopularPropertiesNearGreenwood): ?>

      <div class="content-with-ad-rail">
        <div class="content-main-stack">
          <!-- Top Vendors -->
          <?php if ($showTopVendors): ?>

          <div class="sec recent-ads-section top-vendors-section">
            <div class="sec-head">
              <div class="sec-title"><span class="icon"><i class="fa-solid fa-store"></i></span> Top Vendors</div>
              <a class="view-all" href="{{ route('frontend.vendors.index') }}">VIEW ALL ▶</a>
            </div>

            @if ($topVendorsHeaderAdsList->isNotEmpty())
              <div class="ad-slider auto-ad-slider top-vendors-banner-slider" aria-label="Top vendor banner ads slider">
                @foreach ($topVendorsHeaderAdsList as $ad)
                  <div class="ad-slide">
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="top-vendors-banner-img" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
                  </div>
                @endforeach
              </div>
            @endif

            <div class="ad-slider auto-ad-slider recent-ads-slider top-vendors-cards-slider" data-show-arrows="true" data-show-dots="false" data-pause-on-hover="false" aria-label="Top vendors slider">
              <?php if ($topVendorSlides->isNotEmpty()): ?>
                <?php foreach ($topVendorSlides as $slideIndex => $vendorChunk): ?>
                  <div class="ad-slide">
                    <div class="product-grid-4 recent-ads-grid top-vendors-grid">
                      <?php foreach ($vendorChunk as $vendor): ?>
                        @include('frontend.partials.vendor-card', ['vendor' => $vendor])
                      <?php endforeach; ?>
                      <?php if ($topVendorsSideAdsList->isNotEmpty()): ?>
                        <?php $sideAd = $topVendorsSideAdsList[$slideIndex % $topVendorsSideAdsList->count()]; ?>
                        <article class="prod-card recent-ad-card top-vendors-ad-card"
                          data-ad-description="{{ $sideAd->short_description ?: 'Special marketplace ad available now.' }}"
                          data-ad-url="{{ $sideAd->shareUrl() }}"
                        >
                          <img src="{{ asset($sideAd->final_image) }}" alt="{{ $sideAd->title }}" data-ad-id="{{ $sideAd->id }}" data-ad-url="{{ $sideAd->shareUrl() }}" data-ad-description="{{ $sideAd->short_description ?: 'Special marketplace ad available now.' }}">
                          <div class="prod-card-body">
                            <h6 class="mb-1 offer-coupon-title">{{ $sideAd->title }}</h6>
                            <span class="recent-ad-meta">
                              <i class="fa-solid fa-rectangle-ad"></i>
                              Featured ad
                            </span>
                          </div>
                        </article>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php elseif ($topVendorsSideAdsList->isNotEmpty()): ?>
                <?php foreach ($topVendorsSideAdsList->chunk(6) as $adsChunk): ?>
                  <div class="ad-slide">
                    <div class="product-grid-4 recent-ads-grid top-vendors-grid">
                      <?php foreach ($adsChunk as $sideAd): ?>
                        <article class="prod-card recent-ad-card top-vendors-ad-card"
                          data-ad-description="{{ $sideAd->short_description ?: 'Special marketplace ad available now.' }}"
                          data-ad-url="{{ $sideAd->shareUrl() }}"
                        >
                          <img src="{{ asset($sideAd->final_image) }}" alt="{{ $sideAd->title }}" data-ad-id="{{ $sideAd->id }}" data-ad-url="{{ $sideAd->shareUrl() }}" data-ad-description="{{ $sideAd->short_description ?: 'Special marketplace ad available now.' }}">
                          <div class="prod-card-body">
                            <h6 class="mb-1 offer-coupon-title">{{ $sideAd->title }}</h6>
                            <span class="recent-ad-meta">
                              <i class="fa-solid fa-rectangle-ad"></i>
                              Featured ad
                            </span>
                          </div>
                        </article>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="ad-slide">
                  <div class="product-grid-4 recent-ads-grid top-vendors-grid">
                    <article class="prod-card recent-ad-card">
                      <div class="prod-card-body">
                        <h6 class="mb-1 offer-coupon-title">No vendors available</h6>
                        <span class="recent-ad-meta"><i class="fa-solid fa-circle-info"></i> Please check back later.</span>
                      </div>
                    </article>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            @if($showPremiumOptions)
              @include('frontend.premium.partials.module-cta', ['type' => 'vendor'])
            @endif
          </div>
          <?php endif; ?>


          <!-- Popular Properties Near Greenwood (Bootstrap redesign with large imagery + ads slider) -->
          <?php if ($showPopularPropertiesNearGreenwood): ?>

            <section class="sec ppng-bootstrap-section">
              <div class="sec-head">
                <div class="sec-title"><span class="icon"><i class="fa-solid fa-map-location-dot"></i></span> Popular Properties Near Greenwood</div>
                <a class="view-all" href="#">Learn More ▶</a>
              </div>

              <div class="row g-4 align-items-stretch">
                <div class="col-12 col-xl-8">
                  <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                      <article class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden ppng-property-card">
                        <div class="ratio ratio-16x9">
                          <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=85&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Drip Irrigation Suppliers property image">
                        </div>
                        <div class="card-body p-4">
                          <h4 class="fw-bold mb-2 ppng-card-title">Drip Irrigation Suppliers</h4>
                          <p class="mb-2 ppng-card-meta">📍 9 2A, Greenwood, IN</p>
                          <p class="mb-2 ppng-card-stars">★★★★★</p>
                          <p class="mb-0 ppng-card-meta ppng-card-meta-muted">Farming | 13 Services</p>
                        </div>
                      </article>
                    </div>

                    <div class="col">
                      <article class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden ppng-property-card">
                        <div class="ratio ratio-16x9">
                          <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&q=85&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Royal Dairy Farm property image">
                        </div>
                        <div class="card-body p-4">
                          <h4 class="fw-bold mb-2 ppng-card-title">Royal Dairy Farm</h4>
                          <p class="mb-2 ppng-card-meta">📍 Greenwood, IN</p>
                          <p class="mb-2 ppng-card-stars">★★★★★</p>
                          <p class="mb-0 ppng-card-meta ppng-card-meta-muted">Dairy | 9 Products</p>
                        </div>
                      </article>
                    </div>

                    <div class="col">
                      <article class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden ppng-property-card">
                        <div class="ratio ratio-16x9">
                          <img src="https://images.unsplash.com/photo-1448630360428-65456885c650?w=1200&q=85&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Vikas Hardware Store property image">
                        </div>
                        <div class="card-body p-4">
                          <h4 class="fw-bold mb-2 ppng-card-title">Vikas Hardware Store</h4>
                          <p class="mb-2 ppng-card-meta">📍 Greenwood, IN</p>
                          <p class="mb-2 ppng-card-stars">★★★★☆</p>
                          <p class="mb-0 ppng-card-meta ppng-card-meta-muted">Hardware | 13 Services</p>
                        </div>
                      </article>
                    </div>

                    <div class="col">
                      <article class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden ppng-property-card">
                        <div class="ratio ratio-16x9">
                          <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1200&q=85&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Greenwood Family Homes property image">
                        </div>
                        <div class="card-body p-4">
                          <h4 class="fw-bold mb-2 ppng-card-title">Greenwood Family Homes</h4>
                          <p class="mb-2 ppng-card-meta">📍 4 E, Greenwood, IN</p>
                          <p class="mb-2 ppng-card-stars">★★★★★</p>
                          <p class="mb-0 ppng-card-meta ppng-card-meta-muted">Residential | 18 Listings</p>
                        </div>
                      </article>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-xl-4">
                  <aside class="section-side-ad ad-slider auto-ad-slider ppng-side-slider" data-show-arrows="true" aria-label="Popular properties ads slider">
                    
                    @forelse(($popularGreenwoodAds ?? collect()) as $ad)
                      <div class="side-card ad-slide ppng-side-image-card" aria-label="{{ $ad->title }}">
                        <img class="side-card-img ppng-side-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
                      </div>
                    @empty
                    <div class="side-card shadow-sm rounded-4 overflow-hidden border-0">
                      <img class="side-card-img" src="https://images.unsplash.com/photo-1494526585095-c41746248156?w=1400&q=85&auto=format&fit=crop" alt="Premium property slot ad image">
                      <div class="side-card-body p-4">
                        <h3 class="fw-bold ppng-ad-title">Premium Property Slot</h3>
                        <p class="mb-3 ppng-ad-copy">Run your campaign next to trending Greenwood listings.</p>
                        <button class="btn btn-warning fw-semibold px-4">Reserve Slot</button>
                      </div>
                    </div>
                    @endforelse


                  </aside>
                </div>
              </div>
            </section>
          <?php endif; ?>
        </div><!-- /content-main-stack -->
      </div><!-- /content-with-ad-rail -->
      <?php endif; ?>

    <!-- Popular Properties redesign (single right-side ad with equal height) -->
    @if(!empty($sectionToggles['popular_properties']) && $sectionToggles['popular_properties'])

      <div class="sec popular-properties-redesign popular-properties-section">
        <div class="sec-head">
          <div class="sec-title">Popular Properties</div>
          <a class="view-all" href="#">Your Projects ▶</a>
        </div>
        <div class="popular-properties-layout">
          <div class="prop-list">
            <div class="listing-card">
              <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=200&q=70" alt="Apt">
              <div class="listing-info">
                <h4>Sharada Residency</h4>
                <div class="addr">📍 9 A, Greenwood, IN</div>
                <div class="listing-price">₹23 Lacs</div>
                <div class="listing-quick-meta">
                  <span><i class="fa-solid fa-bed"></i> 2 BHK</span>
                  <span><i class="fa-solid fa-ruler-combined"></i> 1200 sq ft</span>
                  <span><i class="fa-solid fa-calendar-check"></i> Ready</span>
                </div>
              </div>
              <button class="btn-view">View Details</button>
            </div>
            <div class="listing-card">
              <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=200&q=70" alt="Cozy">
              <div class="listing-info">
                <h4>Cozy Apartments</h4>
                <div class="addr">📍 Greenwood, IN</div>
                <div class="listing-price">₹32 Lacs</div>
                <div class="listing-quick-meta">
                  <span><i class="fa-solid fa-bed"></i> 3 BHK</span>
                  <span><i class="fa-solid fa-ruler-combined"></i> 1450 sq ft</span>
                  <span><i class="fa-solid fa-shield-heart"></i> Gated</span>
                </div>
              </div>
              <button class="btn-view">View Details</button>
            </div>
            <div class="listing-card">
              <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=200&q=70" alt="Urban suites">
              <div class="listing-info">
                <h4>Urban Skyline Suites</h4>
                <div class="addr">📍 11 N, Greenwood, IN</div>
                <div class="listing-price">₹41 Lacs</div>
                <div class="listing-quick-meta">
                  <span><i class="fa-solid fa-bed"></i> 3 BHK</span>
                  <span><i class="fa-solid fa-ruler-combined"></i> 1680 sq ft</span>
                  <span><i class="fa-solid fa-car"></i> 1 Parking</span>
                </div>
              </div>
              <button class="btn-view">View Details</button>
            </div>
            <div class="listing-card">
              <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=200&q=70" alt="family homes">
              <div class="listing-info">
                <h4>Greenwood Family Homes</h4>
                <div class="addr">📍 4 E, Greenwood, IN</div>
                <div class="listing-price">₹68 Lacs</div>
                <div class="listing-quick-meta">
                  <span><i class="fa-solid fa-bed"></i> 4 BHK</span>
                  <span><i class="fa-solid fa-ruler-combined"></i> 2200 sq ft</span>
                  <span><i class="fa-solid fa-tree-city"></i> Park View</span>
                </div>
              </div>
              <button class="btn-view">View Details</button>
            </div>
          </div>
          <aside class="popular-feature-ad ad-slider auto-ad-slider popular-feature-ad-slider" data-show-arrows="true" aria-label="Popular properties featured ad slider">
            @forelse(($popularPropertiesAds ?? collect()) as $ad)
              <div class="side-card ad-slide popular-properties-image-card" aria-label="{{ $ad->title }}">
                <img class="side-card-img popular-properties-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
              </div>
            @empty
              <div class="side-card ad-slide">
                <img class="side-card-img" src="https://images.unsplash.com/photo-1600585152220-90363fe7e115?w=900&q=70" alt="Property promotion ad">
                <div class="side-card-body">
                  <h3>Property Highlight Ad</h3>
                  <p>Showcase your listing in this high-conversion block.</p>
                  <button class="btn-learn">Book Ad</button>
                </div>
              </div>
            @endforelse
          </aside>
        </div>
      </div>

      <div class="sec ad-wide-slot">
        <div class="ad-wide-label">Sponsored Placement</div>
        <div class="ad-slider auto-ad-slider ad-wide-slider" data-show-arrows="true" data-pause-on-hover="false" aria-label="Sponsored placement campaign slider">
          @forelse(($belowPopularAds ?? collect()) as $ad)
            <div class="ad-wide-content ad-slide ad-wide-image-slide">
              <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="ad-wide-full-img" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
            </div>
          @empty
            <div class="ad-wide-content ad-slide">
              <div>
                <h3>Run Automatic Marketplace Promotions</h3>
                <p>Use rotating banner ads with arrow navigation to maximize campaign reach.</p>
              </div>
              <button class="ad-slot-btn">Book Slot</button>
            </div>
          @endforelse
        </div>
      </div>
    @endif



    @if(!empty($sectionToggles['builders_developers']) && $sectionToggles['builders_developers'])

      <!-- Builders & Developers + Side Ad -->
      <div class="section-with-side-ad builders-section-with-side-ad row g-3 align-items-start">
        <div class="col-12 col-lg-9">
          <div class="sec builders-developers-sec">
            <div class="sec-head">
              <div class="sec-title"><span class="icon"><i class="fa-solid fa-screwdriver-wrench"></i></span> Builders &amp; Developers</div>
              <a class="view-all" href="#">VIEW ALL ▶</a>
            </div>
            <div class="row g-3 builders-bootstrap-grid">
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 builders-bs-card">
                  <img src="https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=70" class="card-img-top" alt="Green Heights project exterior">
                  <div class="card-body">
                    <h5 class="card-title mb-0">Green Heights</h5>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 builders-bs-card">
                  <img src="https://images.unsplash.com/photo-1617806118233-18e1de247200?w=800&q=70" class="card-img-top" alt="Urban Residency interiors">
                  <div class="card-body">
                    <h5 class="card-title mb-0">Urban Residency</h5>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 builders-bs-card">
                  <img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=800&q=70" class="card-img-top" alt="Elite Constructions modern home">
                  <div class="card-body">
                    <h5 class="card-title mb-0">Elite Constructions</h5>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 builders-bs-card">
                  <img src="https://images.unsplash.com/photo-1600607687644-c7171b42498f?w=800&q=70" class="card-img-top" alt="Skyline Developers premium apartment tower">
                  <div class="card-body">
                    <h5 class="card-title mb-0">Skyline Developers</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <aside class="col-12 col-lg-3 section-side-ad builders-side-ads-stack" aria-label="Builders and developers side ad panels">
          <div class="ads-section-title">Ads</div>
          <div class="ad-slider auto-ad-slider builders-side-slider builders-side-slider-1" aria-label="Builder ads slider" data-show-arrows="true" data-pause-on-hover="false">
            @forelse(($buildersDevelopersAds ?? collect()) as $ad)
              <div class="side-card ad-slide builders-side-image-card" aria-label="{{ $ad->title }}">
                <img class="side-card-img builders-side-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
              </div>
            @empty
              <div class="side-card ad-slide">
                <img class="side-card-img" src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=500&q=70" alt="Builder spotlight promotion">
                <div class="side-card-body">
                  <h3>Featured Builder Ad</h3>
                  <p>Put your project in front of verified buyers in your city.</p>
                  <button class="btn-learn">Promote Now</button>
                </div>
              </div>
            @endforelse
          </div>
        </aside>
      </div>

      <div class="sec ad-wide-slot">
        <div class="ad-wide-label">Sponsored Placement</div>
        <div class="ad-slider auto-ad-slider ad-wide-slider" aria-label="Sponsored services slider" data-show-arrows="true">
          @forelse(($belowBuildersAds ?? collect()) as $ad)
            <div class="ad-wide-content ad-slide ad-wide-image-slide">
              <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="ad-wide-full-img" data-ad-id="{{ $ad->id }}" data-ad-url="{{ $ad->shareUrl() }}" data-ad-description="Special marketplace ad available now.">
            </div>
          @empty
            <div class="ad-wide-content ad-slide">
              <div>
                <h3>Top Rated Partner Ad</h3>
                <p>Promote trusted services with bold banners and high click visibility.</p>
              </div>
              <button class="ad-slot-btn">Book This Banner</button>
            </div>
          @endforelse
        </div>
      </div>
    @endif

    <!-- Popular Services -->
    <?php
      $showServiceProvidersSection = ! empty($sectionToggles['popular_services']) && $sectionToggles['popular_services'];
      $homepageServiceProviders = $topServiceProviders ?? collect();
      $homepageServiceProviderSlides = $homepageServiceProviders->chunk(5);
    ?>
    <?php if ($showServiceProvidersSection): ?>
      <div class="sec">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-house"></i></span> Popular Services</div>
          <div class="consultant-section-actions">
            <button type="button" class="consultant-enquiry-link" data-bs-toggle="modal" data-bs-target="#serviceProviderEnquiryModal">Enquiry</button>
            <a class="view-all" href="<?= e(route('frontend.service_providers.index')) ?>">VIEW ALL ▶</a>
          </div>
        </div>
        <div class="ad-slider auto-ad-slider consultants-home-slider" data-show-arrows="true" data-show-dots="false" data-pause-on-hover="false" aria-label="Featured services slider">
          <?php if ($homepageServiceProviderSlides->isNotEmpty()): ?>
            <?php foreach ($homepageServiceProviderSlides as $serviceProviderChunk): ?>
              <div class="ad-slide">
                <div class="consult-grid consult-grid-professional">
                  <?php foreach ($serviceProviderChunk as $serviceProvider): ?>
                    <?php
                      $primaryBranch = $serviceProvider->branches->first();
                      $professionalExperience = $serviceProvider->branches->first(
                        fn ($branch) => filled($branch->professional_experience)
                      )?->professional_experience;
                      $servicesOffered = $serviceProvider->branches->first(
                        fn ($branch) => filled($branch->services_offered)
                      )?->services_offered;
                      $profilePlaceholder = asset('assets/images/profile-placeholder.svg');
                      $serviceProviderProfileImage = $primaryBranch?->logo ? asset($primaryBranch->logo) : null;
                      $serviceProviderCardImage = $serviceProvider->logo ? asset($serviceProvider->logo) : ($serviceProviderProfileImage ?? $profilePlaceholder);
                      $serviceProviderCity = $primaryBranch?->city ?: ($serviceProvider->city ?: 'Local Area');
                      $serviceProviderDistance = $hasLocation && $serviceProvider->nearest_distance_km !== null
                        ? ' • '.number_format($serviceProvider->nearest_distance_km, 1).' km'
                        : '';
                    ?>
                    <a class="con-card text-decoration-none{{ $serviceProvider->is_premium ? ' is-premium-card' : '' }}" href="<?= e(route('service_provider.show', $serviceProvider->slug)) ?>" aria-label="View <?= e($serviceProvider->publicDisplayName()) ?> service page">
                      <img src="<?= e($serviceProviderCardImage) ?>" alt="<?= e($serviceProvider->publicDisplayName()) ?>" onerror="this.onerror=null;this.src='<?= e($profilePlaceholder) ?>';">
                      <div class="con-card-body">
                        <p class="con-name">
                          <?= e($serviceProvider->publicDisplayName()) ?>
                          @if($serviceProvider->is_premium)
                            @include('frontend.premium.partials.badge', ['size' => 'xs'])
                          @endif
                        </p>
                        <span class="con-role"><?= e($serviceProviderCity) ?> • <?= e($serviceProvider->services_count) ?> Services<?= e($serviceProviderDistance) ?></span>
                        <?php if (filled($professionalExperience)): ?>
                          <span class="con-professional-detail"><strong>Experience:</strong> <?= e(\Illuminate\Support\Str::limit($professionalExperience, 72)) ?></span>
                        <?php endif; ?>
                        <?php if (filled($servicesOffered)): ?>
                          <span class="con-professional-detail"><strong>Services:</strong> <?= e(\Illuminate\Support\Str::limit($servicesOffered, 72)) ?></span>
                        <?php endif; ?>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="ad-slide">
              <div class="consult-grid consult-grid-professional">
                <div class="con-card consultant-empty-card">
                  <div class="con-card-body">
                    <p class="con-name">No services available</p>
                    <span class="con-role">Please check back later.</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
        @if($showPremiumOptions)
          @include('frontend.premium.partials.module-cta', ['type' => 'service'])
        @endif
      </div>

      <!-- <div class="sec vendor-enquiry-section">
        <div class="vendor-enquiry-card consultant-enquiry-card">
          <div class="vendor-enquiry-copy">
            <span class="vendor-enquiry-pill"><i class="fa-solid fa-house-user"></i> Service Enquiry</span>
            <h3>Need help from a service?</h3>
            <p>Share your requirement and we will notify matching verified services by category and subcategory.</p>
          </div>
          <button type="button" class="btn-yellow vendor-enquiry-btn" data-bs-toggle="modal" data-bs-target="#serviceProviderEnquiryModal">
            Submit Enquiry
          </button>
        </div>
      </div> -->
    <?php endif; ?>

    <!-- Consultants & Enquiry -->
    <?php
      $showConsultantsSection = ! empty($sectionToggles['consultants_enquiry']) && $sectionToggles['consultants_enquiry'];
      $homepageConsultants = $topConsultants ?? collect();
      $homepageConsultantSlides = $homepageConsultants->chunk(5);
    ?>
    <?php if ($showConsultantsSection): ?>
      <div class="sec">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-briefcase"></i></span> Consultants &amp; Enquiry</div>
          <div class="consultant-section-actions">
            <button type="button" class="consultant-enquiry-link" data-bs-toggle="modal" data-bs-target="#consultantEnquiryModal">Enquiry</button>
            <a class="view-all" href="<?= e(route('frontend.consultants.index')) ?>">VIEW ALL ▶</a>
          </div>
        </div>
        <div class="ad-slider auto-ad-slider consultants-home-slider" data-show-arrows="true" data-show-dots="false" data-pause-on-hover="false" aria-label="Featured consultants slider">
          <?php if ($homepageConsultantSlides->isNotEmpty()): ?>
            <?php foreach ($homepageConsultantSlides as $consultantChunk): ?>
              <div class="ad-slide">
                <div class="consult-grid consult-grid-professional">
                  <?php foreach ($consultantChunk as $consultant): ?>
                    <?php
                      $primaryBranch = $consultant->branches->first();
                      $professionalExperience = $consultant->branches->first(
                        fn ($branch) => filled($branch->professional_experience)
                      )?->professional_experience;
                      $servicesOffered = $consultant->branches->first(
                        fn ($branch) => filled($branch->services_offered)
                      )?->services_offered;
                      $profilePlaceholder = asset('assets/images/profile-placeholder.svg');
                      $consultantProfileImage = $primaryBranch?->logo ? asset($primaryBranch->logo) : null;
                      $consultantCardImage = $consultant->logo ? asset($consultant->logo) : ($consultantProfileImage ?? $profilePlaceholder);
                      $consultantCardFallback = $consultant->logo && $consultantProfileImage ? $consultantProfileImage : $profilePlaceholder;
                      $consultantCity = $primaryBranch?->city ?: ($consultant->city ?: 'Local Area');
                      $consultantDistance = $hasLocation && $consultant->nearest_distance_km !== null
                        ? ' • '.number_format($consultant->nearest_distance_km, 1).' km'
                        : '';
                    ?>
                    <a class="con-card text-decoration-none{{ $consultant->is_premium ? ' is-premium-card' : '' }}" href="<?= e(route('consultant.show', $consultant->slug)) ?>" aria-label="View <?= e($consultant->publicDisplayName()) ?> consultant page">
                      <img src="<?= e($consultantCardImage) ?>" alt="<?= e($consultant->publicDisplayName()) ?>" onerror="this.onerror=function(){this.onerror=null;this.src='<?= e($profilePlaceholder) ?>';};this.src='<?= e($consultantCardFallback) ?>';">
                      <div class="con-card-body">
                        <p class="con-name">
                          <?= e($consultant->publicDisplayName()) ?>
                          @if($consultant->is_premium)
                            @include('frontend.premium.partials.badge', ['size' => 'xs'])
                          @endif
                        </p>
                        <span class="con-role"><?= e($consultantCity) ?> • <?= e($consultant->services_count) ?> Services<?= e($consultantDistance) ?></span>
                        <?php if (filled($professionalExperience)): ?>
                          <span class="con-professional-detail"><strong>Experience:</strong> <?= e(\Illuminate\Support\Str::limit($professionalExperience, 72)) ?></span>
                        <?php endif; ?>
                        <?php if (filled($servicesOffered)): ?>
                          <span class="con-professional-detail"><strong>Services:</strong> <?= e(\Illuminate\Support\Str::limit($servicesOffered, 72)) ?></span>
                        <?php endif; ?>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="ad-slide">
              <div class="consult-grid consult-grid-professional">
                <div class="con-card consultant-empty-card">
                  <div class="con-card-body">
                    <p class="con-name">No consultants available</p>
                    <span class="con-role">Please check back later.</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        @if($showPremiumOptions)
          @include('frontend.premium.partials.module-cta', ['type' => 'consultant'])
        @endif
      </div>
    <?php endif; ?>

    <!-- @if($showConsultantsSection)

      <div class="sec vendor-enquiry-section">
        <div class="vendor-enquiry-card consultant-enquiry-card">
          <div class="vendor-enquiry-copy">
            <span class="vendor-enquiry-pill"><i class="fa-solid fa-briefcase"></i> Consultant Enquiry</span>
            <h3>Need help from a consultant?</h3>
            <p>Share your question and we will notify matching verified consultants by category and subcategory.</p>
          </div>
          <button type="button" class="btn-yellow vendor-enquiry-btn" data-bs-toggle="modal" data-bs-target="#consultantEnquiryModal">
            Submit Enquiry
          </button>
        </div>
      </div>
    @endif -->

    @if(data_get($sectionToggles, 'vendor_enquiry', true))
      <div class="sec vendor-enquiry-section">
        <div class="vendor-enquiry-card">
          <div class="vendor-enquiry-copy">
            <span class="vendor-enquiry-pill"><i class="fa-solid fa-circle-question"></i> Vendor Enquiry</span>
            <h3>Need help from a vendor?</h3>
            <p>Share your requirement and our team will connect you with the right verified vendor.</p>
          </div>
          <button type="button" class="btn-yellow vendor-enquiry-btn" data-bs-toggle="modal" data-bs-target="#vendorEnquiryModal">
            Submit Enquiry
          </button>
        </div>
      </div>
    @endif

    <!-- Local Sellers CTA -->
    <!-- <div class="seller-highlight seller-highlight-redesign">
      <div class="seller-highlight-info">
        <span class="seller-badge"><i class="fa-solid fa-check"></i></span>
        <div>
          <div class="seller-title">Local Sellers Across India</div>
          <div class="seller-sub">Trusted partners with local support, pan-India reach, and secure payments.</div>
          <div class="seller-points">
            <span><i class="fa-solid fa-store"></i> 12,000+ Verified Sellers</span>
            <span><i class="fa-solid fa-location-dot"></i> Presence in 28 States</span>
            <span><i class="fa-solid fa-credit-card"></i> Safe &amp; Protected Payments</span>
          </div>
        </div>
      </div>
      <div class="seller-highlight-actions">
        <button class="btn-yellow">Become a Seller</button>
        <button class="btn-login">Browse Sellers</button>
      </div>
    </div> -->

  </div><!-- /main-col -->



</div><!-- /main-wrap -->


<!-- <div class="trusted-wrap">
  <div class="trusted-bar">
    <div class="trust-item"><span class="trust-icon"><i class="fa-solid fa-shield-heart"></i></span> Trusted Local Sellers</div>
    <div class="trust-item"><span class="trust-icon"><i class="fa-solid fa-earth-asia"></i></span> Nationwide Reach</div>
    <div class="trust-item"><span class="trust-icon"><i class="fa-solid fa-lock"></i></span> Secure Payments</div>
  </div>
</div> -->



@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
  .offer-details-modal .modal-content {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 18px 44px rgba(26, 58, 92, 0.2);
  }
  .offer-details-modal .modal-title {
    color: #1a3a5c;
    font-weight: 800;
  }
  .offer-details-modal #offerDetailsModalTitle {
    color: #1a3a5c;
    font-weight: 700;
  }
  .offer-details-modal #offerDetailsModalDescription {
    line-height: 1.55;
  }
  .offer-login-message{display:flex;gap:.8rem;align-items:flex-start;background:#f8faff;border:1px solid #d6e4ff;border-radius:12px;padding:.85rem .9rem;margin-top:.75rem}
  .offer-login-message-icon{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#e8f0ff;color:#2457c5;flex:0 0 34px}
  .offer-login-message-title{font-size:1rem;font-weight:700;color:#1d3557}
  .offer-login-message-text{font-size:.9rem;color:#5d6b82}
  .offer-details-modal-image {
    width: 100%;
    aspect-ratio: 768 / 1080;
    height: auto;
    object-fit: cover;
    object-position: center;
    background: #f5f9ff;
    padding: 0;
  }
  .offer-coupon-wrap .offer-coupon-image-wrap {
    border: 0;
    background: transparent;
    width: 100%;
    aspect-ratio: 3 / 4;
    overflow: hidden;
    border-radius: 10px 10px 0 0;
  }
  .offer-coupon-wrap .offer-coupon-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    background: transparent;
    border-radius: 10px 10px 0 0;
  }
  .offer-coupon-wrap .offer-coupon-card {
    padding: 0;
  }
  .offer-coupon-wrap .offer-coupon-card .card-body {
    padding: .7rem .9rem .85rem;
  }

  .vendor-enquiry-card{display:flex;justify-content:space-between;align-items:center;gap:1rem;background:linear-gradient(135deg,#f0f7ff,#ffffff);border:1px solid #dcecff;border-radius:16px;padding:1.2rem 1.3rem;box-shadow:0 8px 20px rgba(33,102,171,.08)}
  .vendor-enquiry-copy h3{margin:0 0 .4rem;color:#1a3a5c;font-size:1.3rem;font-weight:800}
  .vendor-enquiry-copy p{margin:0;color:#5d6b82}
  .vendor-enquiry-pill{display:inline-flex;align-items:center;gap:.35rem;background:#e8f2ff;color:#1e4b8f;border-radius:999px;padding:.3rem .7rem;font-size:.78rem;font-weight:700;margin-bottom:.55rem}
  .vendor-enquiry-btn{white-space:nowrap}
  .vendor-enquiry-modal-content{border-radius:14px;border:0;box-shadow:0 18px 44px rgba(26,58,92,.2)}
  .consultant-section-actions{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
  .consultant-enquiry-link{border:1px solid #cfe7ff;background:#f5fbff;color:#1769bd;border-radius:999px;padding:.35rem .85rem;font-size:.85rem;font-weight:800;line-height:1.2}
  .consultant-enquiry-link:hover,.consultant-enquiry-link:focus{background:#eaf5ff;color:#0b5cab}
  @media (max-width: 767px){.vendor-enquiry-card{flex-direction:column;align-items:flex-start}.vendor-enquiry-btn{width:100%}.consultant-section-actions{width:100%;justify-content:space-between}}

</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const showToast = (type, message) => {
      try {
        if (window.toastr && window.jQuery && typeof window.toastr[type] === 'function') {
          window.toastr.options = { closeButton: true, progressBar: true, timeOut: 3500 };
          window.toastr[type](message);
          return;
        }
      } catch (toastError) {
        console.warn('Toastr unavailable, using alert fallback.', toastError);
      }

      alert(message);
    };

    const bindCategorySelect = (form, categories, categorySelector, subcategorySelector) => {
      if (!form) return null;
      const categorySelect = form.querySelector(categorySelector);
      const subcategorySelect = form.querySelector(subcategorySelector);

      categorySelect?.addEventListener('change', function () {
        const selected = categories.find((category) => String(category.id) === this.value);
        subcategorySelect.innerHTML = '<option value="">Select sub category</option>';
        if (!selected || !selected.children || !selected.children.length) {
          subcategorySelect.setAttribute('disabled', 'disabled');
          return;
        }
        selected.children.forEach(function (child) {
          const option = document.createElement('option');
          option.value = child.id;
          option.textContent = child.name;
          subcategorySelect.appendChild(option);
        });
        subcategorySelect.removeAttribute('disabled');
      });

      return subcategorySelect;
    };

    const vendorForm = document.getElementById('vendorEnquiryForm');
    const vendorSubcategorySelect = bindCategorySelect(vendorForm, @json($vendorEnquiryCategoryTree), '#vendorEnquiryCategory', '#vendorEnquirySubCategory');

    vendorForm?.addEventListener('submit', async function (event) {
      event.preventDefault();
      const submitBtn = document.getElementById('vendorEnquirySubmitBtn');
      const loader = submitBtn?.querySelector('.js-vendor-enquiry-btn-loader');
      const sending = submitBtn?.querySelector('.js-vendor-enquiry-btn-sending');
      const btnText = submitBtn?.querySelector('.js-vendor-enquiry-btn-text');

      submitBtn?.setAttribute('disabled', 'disabled');
      loader?.classList.remove('d-none');
      sending?.classList.remove('d-none');
      btnText?.classList.add('d-none');

      try {
        const response = await fetch("{{ route('frontend.vendor-enquiry') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': vendorForm.querySelector('input[name="_token"]').value,
          },
          body: JSON.stringify({
            email: vendorForm.querySelector('#vendorEnquiryEmail').value,
            phone_number: vendorForm.querySelector('#vendorEnquiryPhone').value,
            preferred_contact: vendorForm.querySelector('#vendorEnquiryPreferredContact').value,
            category_id: vendorForm.querySelector('#vendorEnquiryCategory').value,
            subcategory_id: vendorForm.querySelector('#vendorEnquirySubCategory').value,
            reason: vendorForm.querySelector('#vendorEnquiryReason').value,
          }),
        });

        const data = await response.json();
        showToast(response.ok ? 'success' : 'error', data.message || (response.ok ? 'Enquiry sent successfully.' : 'Unable to send enquiry.'));

        if (response.ok) {
          vendorForm.reset();
          vendorSubcategorySelect.innerHTML = '<option value="">Select sub category</option>';
          vendorSubcategorySelect.setAttribute('disabled', 'disabled');
          const modalEl = document.getElementById('vendorEnquiryModal');
          if (window.bootstrap?.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          }
        }
      } catch (error) {
        showToast('error', 'Unable to send enquiry. Please try again.');
      } finally {
        submitBtn?.removeAttribute('disabled');
        loader?.classList.add('d-none');
        sending?.classList.add('d-none');
        btnText?.classList.remove('d-none');
      }
    });

    const bindProfileEnquiryForm = ({ formId, categories, categorySelector, subcategorySelector, submitBtnId, loaderClass, sendingClass, textClass, endpoint, modalId, successMessage, failureMessage }) => {
      const form = document.getElementById(formId);
      if (!form) return;
      const subcategorySelect = bindCategorySelect(form, categories, categorySelector, subcategorySelector);

      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const submitBtn = document.getElementById(submitBtnId);
        const loader = submitBtn?.querySelector(loaderClass);
        const sending = submitBtn?.querySelector(sendingClass);
        const btnText = submitBtn?.querySelector(textClass);

        submitBtn?.setAttribute('disabled', 'disabled');
        loader?.classList.remove('d-none');
        sending?.classList.remove('d-none');
        btnText?.classList.add('d-none');

        try {
          const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
            body: new FormData(form),
          });

          const data = await response.json();
          showToast(response.ok ? 'success' : 'error', data.message || (response.ok ? successMessage : failureMessage));

          if (response.ok) {
            form.reset();
            if (subcategorySelect) {
              subcategorySelect.innerHTML = '<option value="">Select sub category</option>';
              subcategorySelect.setAttribute('disabled', 'disabled');
            }
            const modalEl = document.getElementById(modalId);
            if (window.bootstrap?.Modal && modalEl) {
              window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
          }
        } catch (error) {
          showToast('error', failureMessage);
        } finally {
          submitBtn?.removeAttribute('disabled');
          loader?.classList.add('d-none');
          sending?.classList.add('d-none');
          btnText?.classList.remove('d-none');
        }
      });
    };

    bindProfileEnquiryForm({
      formId: 'consultantEnquiryForm',
      categories: @json($consultantEnquiryCategoryTree),
      categorySelector: '#consultantEnquiryCategory',
      subcategorySelector: '#consultantEnquirySubCategory',
      submitBtnId: 'consultantEnquirySubmitBtn',
      loaderClass: '.js-consultant-enquiry-btn-loader',
      sendingClass: '.js-consultant-enquiry-btn-sending',
      textClass: '.js-consultant-enquiry-btn-text',
      endpoint: "{{ route('frontend.consultant-enquiry') }}",
      modalId: 'consultantEnquiryModal',
      successMessage: 'Consultant enquiry sent successfully.',
      failureMessage: 'Unable to send consultant enquiry. Please try again.',
    });

    bindProfileEnquiryForm({
      formId: 'serviceProviderEnquiryForm',
      categories: @json($serviceProviderEnquiryCategoryTree),
      categorySelector: '#serviceProviderEnquiryCategory',
      subcategorySelector: '#serviceProviderEnquirySubCategory',
      submitBtnId: 'serviceProviderEnquirySubmitBtn',
      loaderClass: '.js-service-provider-enquiry-btn-loader',
      sendingClass: '.js-service-provider-enquiry-btn-sending',
      textClass: '.js-service-provider-enquiry-btn-text',
      endpoint: "{{ route('frontend.service-provider-enquiry') }}",
      modalId: 'serviceProviderEnquiryModal',
      successMessage: 'Service enquiry sent successfully.',
      failureMessage: 'Unable to send service enquiry. Please try again.',
    });
  });
</script>

@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const isUserLoggedIn = @json(auth()->check());

    const offerModal = document.getElementById('offerDetailsModal');
    if (!offerModal) return;
    const titleEl = document.getElementById('offerDetailsModalTitle');
    const discountEl = document.getElementById('offerDetailsModalDiscount');
    const descriptionEl = document.getElementById('offerDetailsModalDescription');
    const couponEl = document.getElementById('offerDetailsModalCoupon');
    const expiryEl = document.getElementById('offerDetailsModalExpiry');
    const validityRowEl = document.getElementById('offerDetailsModalValidityRow');
    const imageEl = document.getElementById('offerDetailsModalImage');
    const loginMessageBox = document.getElementById('offerLoginMessageBox');
    const sharePanelEl = document.getElementById('offerSharePanel');
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
    const offerTriggers = document.querySelectorAll('.offer-coupon-card.js-offer-modal-trigger');

    offerTriggers.forEach(function (trigger) {
      trigger.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          trigger.click();
        }
      });
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

    offerModal.addEventListener('show.bs.modal', function (event) {
      const trigger = event.relatedTarget;
      if (!trigger || !trigger.classList.contains('js-offer-modal-trigger')) return;

      if (!isUserLoggedIn) {
        if (loginMessageBox) loginMessageBox.classList.remove('d-none');
        titleEl.textContent = 'You are not logged in';
        discountEl.textContent = '';
        discountEl.classList.add('d-none');
        descriptionEl.textContent = '';
        if (validityRowEl) validityRowEl.classList.add('d-none');
        expiryEl.textContent = '';
        couponEl.textContent = '';
        couponEl.classList.add('d-none');
        imageEl.src = '';
        imageEl.classList.add('d-none');
        if (sharePanelEl) sharePanelEl.classList.add('d-none');
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
      const offerId = trigger.getAttribute('data-offer-id') || '';
      if (offerReportActions) offerReportActions.classList.toggle('d-none', !offerId);
      if (offerReportPopupWrap) offerReportPopupWrap.classList.add('d-none');
      if (offerReportForm && offerId) {
        offerReportForm.action = `{{ url('/offers-market') }}/${offerId}/report`;
        const reportReason = offerReportForm.querySelector('textarea[name="reason"]');
        if (reportReason) reportReason.value = '';
      }

      titleEl.textContent = trigger.getAttribute('data-offer-title') || 'Offer Details';
      discountEl.textContent = trigger.getAttribute('data-offer-discount') || '';
      descriptionEl.textContent = trigger.getAttribute('data-offer-description') || '';
      expiryEl.textContent = trigger.getAttribute('data-offer-validity') || 'No expiry';
 
      const couponCode = trigger.getAttribute('data-offer-coupon');
      if (couponCode) {
        couponEl.textContent = couponCode;
        couponEl.classList.remove('d-none');
      } else {
        couponEl.textContent = '';
        couponEl.classList.add('d-none');
      }

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
    });

    window.soilnwaterBindShareCopyButton('offerShareCopyBtn', 'offerShareLink');
    window.soilnwaterBindInstagramShareButton('offerShareInstagram', 'offerShareLink', {
      title: 'SoilnWater Offer',
      text: 'Check this offer on SoilnWater',
    });
  });
</script>
@endpush
