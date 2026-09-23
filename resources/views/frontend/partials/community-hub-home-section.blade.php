@php
  $hubSections = $communityHubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
  $posts = collect($homepageCommunityPosts ?? []);
  $engagement = $communityEngagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
@endphp

<div class="sec promo-slider-section homepage-community-hub-section">
  <div class="sec-head">
    <div class="sec-title">
      <span class="icon"><i class="fa-solid fa-people-group"></i></span>
      Community Hub
    </div>
    <a class="view-all" href="{{ route('community.index') }}">VIEW ALL ▶</a>
  </div>

  <p class="homepage-community-hub__subtitle">Discover stories, reports, news, poetry, and local voices from across India.</p>

  <div class="promo-layout row g-3 g-lg-4 align-items-stretch">
    <div class="col-12 d-flex">
      <div class="offer-coupon-wrap w-100">
        <div
          class="offer-coupon-grid-slider card-carousel auto-ad-slider homepage-community-hub-sections-slider"
          data-slide-by="card"
          data-carousel-cols="5"
          data-show-arrows="true"
          data-show-dots="false"
          data-pause-on-hover="false"
          aria-label="Community hub categories slider"
        >
          <div class="card-carousel-track">
            @foreach ($hubSections as $hubKey => $hub)
              <div class="card-carousel-item">
                @include('frontend.partials.community-hub-home-card', [
                    'hubKey' => $hubKey,
                    'hub' => $hub,
                ])
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="homepage-community-posts-wrap">
    <div class="homepage-community-posts__head">
      <h3 class="homepage-community-posts__title">Featured posts</h3>
    </div>
    <div class="offer-coupon-wrap w-100">
      <div
        class="homepage-community-posts-slider card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="5"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="false"
        aria-label="Community posts slider"
      >
        <div class="card-carousel-track">
          @include('community.partials.post-cards', [
              'posts' => $posts,
              'engagement' => $engagement,
              'listingHighlights' => [],
              'emptyMessage' => 'Community posts will appear here once published.',
              'wrapper' => 'carousel-item',
          ])
        </div>
      </div>
    </div>
  </div>
</div>
