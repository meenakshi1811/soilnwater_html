@extends('frontend.layouts.app')

@section('content')
@php
  $sectionToggles = data_get($homepageSetting ?? null, 'section_toggles', []);
  $heroBannerImage = data_get($homepageSetting ?? null, 'hero_banner_image');
  $heroButtonText = data_get($homepageSetting ?? null, 'hero_button_text', 'Advertise Now');
  $heroButtonLink = data_get($homepageSetting ?? null, 'hero_button_link', '#');
  
@endphp

<div id="post-ad" class="visually-hidden" aria-hidden="true"></div>
<div id="post-offer" class="visually-hidden" aria-hidden="true"></div>
<!-- ══════════════════════════════════════════════════
     HERO BANNER
══════════════════════════════════════════════════ -->
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
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-cart-shopping cat-icon-i cat-ecommerce"></i>
          </div>
          <span>E-COMMERCE</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-store cat-icon-i cat-vendors"></i>
          </div>
          <span>VENDORS</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-user-tie cat-icon-i cat-consultants"></i>
          </div>
          <span>CONSULTANTS</span>
        </div>
      </a>
      <a href="">
        <div class="cat-item">
          <div class="cat-icon">
            <i class="fa-solid fa-screwdriver-wrench cat-icon-i cat-service"></i>
          </div>
          <span>SERVICE PROVIDERS</span>
        </div>
      </a>
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
                <img class="side-card-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}">
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
                <img class="side-card-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}">
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
                        <img src="{{ asset($ad->final_image) }}" class="card-img-top ecommerce-ad-full-img" alt="{{ $ad->title }}">
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
                  <article class="prod-card recent-ad-card" data-ad-description="{{ $recentAd->short_description ?: 'Special marketplace ad available now.' }}">
                    <img src="{{ asset($recentAd->final_image) }}" alt="{{ $recentAd->title }}">
                    <div class="prod-card-body">
                      <h6 class="mb-1 offer-coupon-title">{{ $recentAd->title }}</h6>
                      <span class="recent-ad-meta">
                        <i class="fa-solid fa-layer-group"></i>
                        {{ $recentAd->category?->name ?? 'Uncategorized' }} • {{ $recentAd->created_at?->format('d M Y') ?? 'N/A' }}
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
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="offer-discount-top-image">
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
                              <span class="badge text-bg-primary w-fit">{{ $offer->discount_tag }}</span>
                              <h4 class="h6 mb-1 offer-coupon-title">{{ $offer->title }}</h4>
                              <p class="small text-muted mb-2 offer-coupon-description">{{ $offer->short_description ?: 'Special marketplace offer available now.' }}</p>
                            
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



    <div class="modal fade offer-details-modal" id="adDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable offer-details-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title fs-5">Ad Details</h2>
            <button type="button" class="offer-modal-close-btn" data-bs-dismiss="modal">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <div class="modal-body p-0">
            <img id="adDetailsModalImage" src="" alt="Ad image" class="d-none offer-details-modal-image">
            <div class="offer-details-content">
              <h3 class="h4 mb-2" id="adDetailsModalTitle"></h3>
              <p class="text-muted mb-2" id="adDetailsModalMeta"></p>
              <p class="text-muted mb-3" id="adDetailsModalDescription"></p>

              <button type="button" class="btn btn-outline-primary btn-sm mb-3 d-none" id="adDetailsEnlargeBtn">
                <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Enlarge image
              </button>

              <div class="offer-share-panel mt-2">
                <div class="offer-share-panel-head">
                  <h4 class="offer-share-title mb-1">Share this ad</h4>
                </div>
                <div class="offer-share-panel-body">
                  <div class="offer-share-qr-wrap">
                    <img id="adShareQr" src="" alt="Ad QR" class="offer-share-qr">
                  </div>
                  <div class="offer-share-links-wrap">
                    <input type="text" id="adShareLink" class="form-control form-control-sm offer-share-link-input" readonly>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                      <a id="adShareWhatsapp" href="#" target="_blank" class="btn btn-sm offer-share-btn share-whatsapp">WhatsApp</a>
                      <a id="adShareFacebook" href="#" target="_blank" class="btn btn-sm offer-share-btn share-facebook">Facebook</a>
                      <a id="adShareInstagram" href="#" target="_blank" class="btn btn-sm offer-share-btn share-instagram">Instagram</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="adImageEnlargeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
          <div class="modal-header border-0">
            <h2 class="modal-title fs-6 text-white">Ad Image Preview</h2>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-0">
            <img id="adImageEnlargePreview" src="" alt="Enlarged ad image" class="img-fluid w-100 rounded">
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
            <p class="mb-0"><strong>Valid until:</strong> <span id="offerDetailsModalExpiry"></span></p>
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
              <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="explore-ad-full-img">
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
    @if(!empty($sectionToggles['top_vendors']) && $sectionToggles['top_vendors'] ||(!empty($sectionToggles['popular_properties_near_greenwood']) && $sectionToggles['popular_properties_near_greenwood']))

      <div class="content-with-ad-rail">
        <div class="content-main-stack">
          <!-- Top Vendors -->
          @if(!empty($sectionToggles['top_vendors']) && $sectionToggles['top_vendors'])

          <div class="sec">
            <div class="sec-head">
              <div class="sec-title"><span class="icon"><i class="fa-solid fa-store"></i></span> Top Vendors</div>
              <a class="view-all" href="#">VIEW ALL ▶</a>
            </div>
            <div class="ad-slider auto-ad-slider top-ad-slider top-vendors-featured-slider" aria-label="Top vendor featured ads slider">
              @forelse(($topVendorsHeaderAds ?? collect()) as $ad)
                <div class="vendor-top-ad ad-slide top-vendor-image-slide">
                  <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="top-vendor-header-img">
                </div>
              @empty
                <div class="vendor-top-ad ad-slide">
                  <div>
                    <div class="vendor-top-ad-title">Boost Vendor Reach</div>
                    <div class="vendor-top-ad-sub">Auto-target active buyers and drive qualified leads every day.</div>
                  </div>
                  <button class="vendor-top-ad-btn">Featured</button>
                </div>
              @endforelse
            </div>
            <div class="row g-3 align-items-start">
              <div class="col-12 col-lg-9">
            <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3">
              <div class="col">
              <div class="vendor-card card h-100">
                <img src="https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=300&q=70" alt="Fashion">
                <div class="vendor-card-body card-body d-flex flex-column">
                  <p>Elite Fashion Store</p>
                  <div class="vendor-card-sub">Boutique Apparel • Downtown</div>
                  
                  <button class="vendor-card-btn">View Store</button>
                </div>
              </div>
              </div>
              <div class="col">
              <div class="vendor-card card h-100">
                <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=300&q=70" alt="Grocery">
                <div class="vendor-card-body card-body d-flex flex-column">
                  <p>Fresh Grocery Mart</p>
                  <div class="vendor-card-sub">Organic Produce • City Center</div>
                  
                  <button class="vendor-card-btn">View Store</button>
                </div>
              </div>
              </div>
              <div class="col">
              <div class="vendor-card card h-100">
                <img src="https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=300&q=70" alt="Tech">
                <div class="vendor-card-body card-body d-flex flex-column">
                  <p>Tech World</p>
                  <div class="vendor-card-sub">Laptops & Gadgets • North Ave</div>
                  
                  <button class="vendor-card-btn">View Store</button>
                </div>
              </div>
              </div>
              <div class="col">
              <div class="vendor-card card h-100">
                <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=300&q=70" alt="Decor">
                <div class="vendor-card-body card-body d-flex flex-column">
                  <p>Sunshine Decor</p>
                  <div class="vendor-card-sub">Home Interiors • Market Road</div>
                  <button class="vendor-card-btn">View Store</button>
                </div>
              </div>
              </div>
              <div class="col">
              <div class="view-all-card ad-slot-card h-100" style="min-height:130px;"><div class="ad-tag">Sponsored</div><h4>Vendor Promotion</h4><p>Run targeted ads for local customers.</p><button class="ad-slot-btn">Advertise</button></div>
              </div>
            </div></div>
              <aside class="col-12 col-lg-3 section-side-ad ad-slider auto-ad-slider top-vendor-side-slider" aria-label="Top vendor side ads slider">
                @forelse(($topVendorsSideAds ?? collect()) as $ad)
                  <div class="side-card ad-slide top-vendor-side-image-card" aria-label="{{ $ad->title }}">
                    <img class="side-card-img top-vendor-side-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}">
                  </div>
                @empty
                  <div class="side-card ad-slide">
                    <img class="side-card-img" src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?w=500&q=70" alt="Top vendor ad">
                    <div class="side-card-body">
                      <h3>Top Vendor Ad</h3>
                      <p>Show your brand next to trusted vendors.</p>
                      <button class="btn-learn">Get Placement</button>
                    </div>
                  </div>
                @endforelse
              </aside>
            </div>
          </div>
          @endif


          <!-- Popular Properties Near Greenwood (Bootstrap redesign with large imagery + ads slider) -->
          @if(!empty($sectionToggles['popular_properties_near_greenwood']) && $sectionToggles['popular_properties_near_greenwood'])

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
                        <img class="side-card-img ppng-side-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}">
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
          @endif
        </div><!-- /content-main-stack -->
      </div><!-- /content-with-ad-rail -->
      @endif

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
                <img class="side-card-img popular-properties-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}">
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
              <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="ad-wide-full-img">
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
                <img class="side-card-img builders-side-full-img" src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}">
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
              <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="ad-wide-full-img">
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

    <!-- Popular Services / Properties -->
    @if(!empty($sectionToggles['popular_services']) && $sectionToggles['popular_services'])

      <div class="sec">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-house"></i></span> Popular Services</div>
          <a class="view-all" href="#">VIEW ALL ▶</a>
        </div>
        <div class="product-grid-4 popular-services-grid">
          <div class="prod-card popular-service-card">
            <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&q=80" alt="2 BHK apartment interior">
            <div class="prod-card-body">
              <p>2 BHK Apartment</p>
              <span class="popular-service-meta"><i class="fa-solid fa-location-dot"></i> South Delhi • Ready to Move</span>
            </div>
          </div>
          <div class="prod-card popular-service-card">
            <img src="https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=900&q=80" alt="Luxury villa with pool">
            <div class="prod-card-body">
              <p>Luxury Villa</p>
              <span class="popular-service-meta"><i class="fa-solid fa-star"></i> Premium Listing • Verified Builder</span>
            </div>
          </div>
          <div class="prod-card popular-service-card">
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=900&q=80" alt="Modern office space">
            <div class="prod-card-body">
              <p>Office Space</p>
              <span class="popular-service-meta"><i class="fa-solid fa-briefcase"></i> Business District • Furnished</span>
            </div>
          </div>
          <div class="prod-card popular-service-card">
            <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?w=900&q=80" alt="Modern kitchen renovation">
            <div class="prod-card-body">
              <p>Interior Design</p>
              <span class="popular-service-meta"><i class="fa-solid fa-paint-roller"></i> Home Makeover • 4.9 Rating</span>
            </div>
          </div>
          <div class="prod-card popular-service-card">
            <img src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=900&q=80" alt="Comfortable rental home bedroom">
            <div class="prod-card-body">
              <p>Rental Homes</p>
              <span class="popular-service-meta"><i class="fa-solid fa-key"></i> Flexible Lease • Instant Visit</span>
            </div>
          </div>
        </div>
      </div>
    @endif

    <!-- Consultants & Enquiry -->
    @if(!empty($sectionToggles['consultants_enquiry']) && $sectionToggles['consultants_enquiry'])

      <div class="sec">
        <div class="sec-head">
          <div class="sec-title"><span class="icon"><i class="fa-solid fa-briefcase"></i></span> Consultants &amp; Enquiry</div>
          <a class="view-all" href="#">POST YOUR QUERY ▶</a>
        </div>
        <div class="consult-grid consult-grid-professional">
          <div class="con-card">
            <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=480&q=75" alt="Dr. Anil Sharma portrait">
            <div class="con-card-body">
              <p class="con-name">Dr. Anil Sharma</p>
              <span class="con-role">Medical Consultant</span>
            </div>
          </div>
          <div class="con-card">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=480&q=75" alt="Legal advisor portrait">
            <div class="con-card-body">
              <p class="con-name">Neha Verma</p>
              <span class="con-role">Legal Advisor</span>
            </div>
          </div>
          <div class="con-card">
            <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=480&q=75" alt="Career consultant portrait">
            <div class="con-card-body">
              <p class="con-name">Riya Malhotra</p>
              <span class="con-role">Career Consultant</span>
            </div>
          </div>
          <div class="con-card">
            <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=480&q=75" alt="Business consultant portrait">
            <div class="con-card-body">
              <p class="con-name">Arjun Mehta</p>
              <span class="con-role">Business Consultant</span>
            </div>
          </div>
          <div class="con-card">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=480&q=75" alt="Financial consultant portrait">
            <div class="con-card-body">
              <p class="con-name">Sana Iqbal</p>
              <span class="con-role">Financial Consultant</span>
            </div>
          </div>
        </div>
      </div>
    @endif

    <!-- Local Sellers CTA -->
    <div class="seller-highlight seller-highlight-redesign">
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
    </div>

  </div><!-- /main-col -->



</div><!-- /main-wrap -->


<!-- ══════════════════════════════════════════════════
     TRUSTED BAR
══════════════════════════════════════════════════ -->
<div class="trusted-wrap">
  <div class="trusted-bar">
    <div class="trust-item"><span class="trust-icon"><i class="fa-solid fa-shield-heart"></i></span> Trusted Local Sellers</div>
    <div class="trust-item"><span class="trust-icon"><i class="fa-solid fa-earth-asia"></i></span> Nationwide Reach</div>
    <div class="trust-item"><span class="trust-icon"><i class="fa-solid fa-lock"></i></span> Secure Payments</div>
  </div>
</div>



@endsection

@push('styles')
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
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const adModal = document.getElementById('adDetailsModal');
    const adImageEl = document.getElementById('adDetailsModalImage');
    const adEnlargeBtn = document.getElementById('adDetailsEnlargeBtn');
    const adImageEnlargePreview = document.getElementById('adImageEnlargePreview');

    if (adModal) {
      document.addEventListener('click', function (event) {
        const adImage = event.target.closest('.ad-slider img, .recent-ad-card img');
        if (!adImage || adImage.closest('.offer-coupon-card')) return;

        event.preventDefault();

        const adTitle = adImage.getAttribute('alt') || 'Ad Details';
        const adSrc = adImage.getAttribute('src') || '';
        const adMeta = 'Home Page Advertisement';
        const adCard = adImage.closest('.recent-ad-card');
        const adDescription = adCard?.dataset.adDescription || 'You are viewing this ad from the homepage slider/recent ads section.';

        document.getElementById('adDetailsModalTitle').textContent = adTitle;
        document.getElementById('adDetailsModalMeta').textContent = adMeta;
        document.getElementById('adDetailsModalDescription').textContent = adDescription;

        if (adSrc) {
          adImageEl.src = adSrc;
          adImageEl.classList.remove('d-none');
          adEnlargeBtn.classList.remove('d-none');
        } else {
          adImageEl.src = '';
          adImageEl.classList.add('d-none');
          adEnlargeBtn.classList.add('d-none');
        }

        const shareUrl = window.location.href;
        document.getElementById('adShareLink').value = shareUrl;
        document.getElementById('adShareQr').src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(shareUrl)}`;
        document.getElementById('adShareWhatsapp').href = `https://wa.me/?text=${encodeURIComponent('Check this ad: ' + shareUrl)}`;
        document.getElementById('adShareFacebook').href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
        document.getElementById('adShareInstagram').href = shareUrl;

        new bootstrap.Modal(adModal).show();
      });

      if (adEnlargeBtn) {
        adEnlargeBtn.addEventListener('click', function () {
          if (!adImageEl || !adImageEl.src) return;
          adImageEnlargePreview.src = adImageEl.src;
          new bootstrap.Modal(document.getElementById('adImageEnlargeModal')).show();
        });
      }
    }

    const offerModal = document.getElementById('offerDetailsModal');
    if (!offerModal) return;
    const isLoggedIn = @json(auth()->check());
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

      if (!isLoggedIn) {
        titleEl.textContent = 'You are not logged in';
        discountEl.textContent = '';
        discountEl.classList.add('d-none');
        descriptionEl.textContent = 'Please log in to view offer details.';
        expiryEl.textContent = '-';
        couponEl.textContent = '';
        couponEl.classList.add('d-none');
        imageEl.src = '';
        imageEl.classList.add('d-none');
        return;
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
    });
  });
</script>
@endpush
