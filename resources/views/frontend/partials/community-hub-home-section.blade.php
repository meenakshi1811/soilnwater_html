@php
  $hubSections = $communityHubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
  $posts = collect($homepageCommunityPosts ?? []);
  $engagement = $communityEngagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
@endphp

<div class="sec homepage-community-hub-section">
  <div class="sec-head">
    <div class="sec-title">
      <span class="icon"><i class="fa-solid fa-people-group"></i></span>
      Community Hub
    </div>
    <a class="view-all" href="{{ route('community.index') }}">VIEW ALL ▶</a>
  </div>

  <p class="homepage-community-hub__subtitle">Discover stories, reports, news, poetry, and local voices from across India.</p>

  <div class="homepage-community-hub-sections">
    @foreach ($hubSections as $hubKey => $hub)
      <a
        href="{{ route('community.index', ['hub' => $hubKey]) }}"
        class="homepage-community-hub-section-card"
        style="--hub-accent: {{ $hub['accent'] }};"
      >
        <span class="homepage-community-hub-section-card__icon" aria-hidden="true">
          <i class="fa-solid {{ $hub['icon'] }}"></i>
        </span>
        <span class="homepage-community-hub-section-card__copy">
          <span class="homepage-community-hub-section-card__label">{{ $hub['label'] }}</span>
          <span class="homepage-community-hub-section-card__tagline">{{ $hub['tagline'] }}</span>
        </span>
      </a>
    @endforeach
  </div>

  <div class="homepage-community-posts-wrap">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 g-lg-4">
      @include('community.partials.post-cards', [
          'posts' => $posts,
          'engagement' => $engagement,
          'listingHighlights' => [],
          'emptyMessage' => 'Community posts will appear here once published.',
      ])
    </div>
  </div>
</div>
