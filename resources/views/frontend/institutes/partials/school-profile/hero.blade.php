<section id="sch-overview" class="sch-top-block">
  <div class="sch-card sch-hero">
    <div class="sch-hero__media">
      <img src="{{ $profile->heroImage() }}" alt="{{ $profile->displayName() }}" class="sch-hero__image">
      @if($profile->galleryItems()->count() > 1)
        <a href="#sch-gallery" class="sch-hero__gallery-btn js-sch-nav-link">
          <i class="fa-solid fa-camera" aria-hidden="true"></i> View Gallery
        </a>
      @endif
    </div>

    <div class="sch-hero__body">
      <div class="sch-hero__badges">
        @if($institute->is_verified)
          <span class="sch-badge sch-badge--verified"><i class="fa-solid fa-circle-check"></i> Verified Institution</span>
        @endif
        @if($profile->isPremium())
          <span class="sch-badge sch-badge--premium"><i class="fa-solid fa-crown"></i> Premium Member</span>
        @endif
      </div>

      <div class="sch-hero__title-row">
        <h1>{{ $profile->displayName() }}</h1>
        <button
          type="button"
          class="sch-bookmark js-sch-bookmark {{ !empty($engagement['is_bookmarked']) ? 'is-active' : '' }}"
          aria-label="Bookmark school"
          data-url="{{ route(($listingContext ?? 'schools').'.bookmark', $institute->slug) }}"
        >
          <i class="{{ !empty($engagement['is_bookmarked']) ? 'fa-solid' : 'fa-regular' }} fa-bookmark" aria-hidden="true"></i>
        </button>
      </div>

      <p class="sch-hero__meta">{{ $profile->subtitleLine() }}</p>

      @if($aboutText)
        <div class="sch-hero__summary">
          <p class="js-sch-about-preview {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{{ \Illuminate\Support\Str::limit(strip_tags($aboutText), 180) }}</p>
          @if($aboutNeedsToggle)
            <a href="#sch-about" class="sch-read-more js-sch-nav-link">Read More</a>
          @endif
        </div>
      @endif

      <div class="sch-hero__facts">
        @if($institute->locationLabel())
          <div><span>City / State</span><strong>{{ $institute->locationLabel() }}</strong></div>
        @endif
        @if($profile->establishedYear())
          <div><span>Founded</span><strong>{{ $profile->establishedYear() }}</strong></div>
        @endif
        <div><span>Affiliation</span><strong>{{ $profile->affiliationLabel() }}</strong></div>
        @if($profile->schoolCode() !== '—')
          <div><span>School Code</span><strong>{{ $profile->schoolCode() }}</strong></div>
        @endif
        <div><span>School Type</span><strong>{{ $profile->schoolType() }}</strong></div>
      </div>
    </div>

    @include('frontend.institutes.partials.school-profile.hero-actions')
  </div>
</section>
