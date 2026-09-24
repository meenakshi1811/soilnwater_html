@php
  use App\Support\SchoolProfilePresenter;

  $onlySection = $onlySection ?? null;
  $isProfilePreview = $isProfilePreview ?? ($onlySection === null);
  $previewLimits = SchoolProfilePresenter::PREVIEW_LIMITS;
  $showSection = fn (string $key): bool => $onlySection === null || $onlySection === $key;

  $classes = $institute->schoolClasses;
  $achievements = $institute->achievements;
  $performers = $institute->topPerformers;
  $gallery = $profile->galleryImages();
  $coursesLimit = $isProfilePreview ? ($previewLimits['courses'] ?? null) : null;
  $classDetailsLimit = $isProfilePreview ? ($previewLimits['class-details'] ?? null) : null;
  $courseWingsPreview = $profile->courseWings($coursesLimit);
  $classesForPreview = $classDetailsLimit !== null
    ? $classes->sortByDesc('id')->take($classDetailsLimit)->values()
    : $classes;
  $coursesSectionTotal = max($profile->courseWingsCount(), $classes->count());
  $facilitiesLimit = $isProfilePreview ? ($previewLimits['facilities'] ?? null) : null;
  $facilitiesCarouselLimit = $isProfilePreview ? ($previewLimits['facilities-carousel'] ?? null) : null;
  $facultyLimit = $isProfilePreview ? ($previewLimits['faculty'] ?? null) : null;
  $galleryLimit = $isProfilePreview ? ($previewLimits['gallery'] ?? null) : null;
  $galleryPreview = $galleryLimit !== null ? $gallery->take($galleryLimit) : $gallery;
  $achievementsLimit = $isProfilePreview ? ($previewLimits['achievements'] ?? null) : null;
  $achievementsPreview = $achievementsLimit !== null ? $achievements->take($achievementsLimit) : $achievements;
  $resultsLimit = $isProfilePreview ? ($previewLimits['results'] ?? null) : null;
  $performersPreview = $resultsLimit !== null ? $performers->take($resultsLimit) : $performers;
  $notesLimit = $isProfilePreview ? ($previewLimits['notes-materials'] ?? null) : null;
  $questionPapersLimit = $isProfilePreview ? ($previewLimits['question-papers'] ?? null) : null;
  $notesPreview = $profile->notesMaterialBooks($notesLimit);
  $questionPapersPreview = $profile->questionPaperBooks($questionPapersLimit);
  $newsLimit = $isProfilePreview ? ($previewLimits['news'] ?? null) : null;
  $eventsLimit = $isProfilePreview ? ($previewLimits['events'] ?? null) : null;
  $reviewsLimit = $isProfilePreview ? ($previewLimits['reviews'] ?? null) : null;
  $allNewsItems = $profile->newsItems();
  $newsItems = $newsLimit !== null ? array_slice($allNewsItems, 0, $newsLimit) : $allNewsItems;
  $newsItemsOverflow = $newsLimit !== null ? array_slice($allNewsItems, $newsLimit) : [];
  $newsCarouselCols = min(max(count($newsItems), 1), 4);
  $events = $profile->upcomingEvents($eventsLimit);
  $reviews = $profile->reviews($reviewsLimit);
@endphp

@if($showSection('courses'))
  <section id="sch-courses" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Courses &amp; Programs Offered</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'courses',
        'total' => $coursesSectionTotal,
        'limit' => $previewLimits['courses'],
        'inHead' => true,
      ])
    </div>
    <div class="sch-course-grid">
      @foreach($courseWingsPreview as $wing)
        <article class="sch-course-card">
          <span class="sch-course-card__icon"><i class="fa-solid {{ $wing['icon'] }}" aria-hidden="true"></i></span>
          <h3>{{ $wing['title'] }}</h3>
          <p class="sch-course-card__grades">{{ $wing['grades'] }}</p>
          <p>{{ $wing['description'] }}</p>
        </article>
      @endforeach
    </div>
    @if(!$isProfilePreview)
      <div class="sch-streams">
        <h3>Streams in Class XI &amp; XII</h3>
        <div class="sch-streams__list">
          @foreach($profile->streams() as $stream)
            <span class="sch-stream-chip">{{ $stream }}</span>
          @endforeach
        </div>
      </div>
    @endif
    @if($classes->isNotEmpty())
      <div id="sch-classes-detail" class="sch-classes-detail">
        <div class="sch-section__head sch-classes-detail__head">
          <h3 class="sch-classes-detail__title mb-0">Class details</h3>
          @include('frontend.institutes.partials.school-profile.section-view-all', [
            'institute' => $institute,
            'isProfilePreview' => $isProfilePreview,
            'sectionKey' => 'courses',
            'total' => $classes->count(),
            'limit' => $previewLimits['class-details'],
            'inHead' => true,
            'alwaysShow' => true,
          ])
        </div>
        @if($isProfilePreview)
          <div
            class="sch-profile-carousel sch-classes-carousel card-carousel auto-ad-slider"
            data-slide-by="card"
            data-carousel-cols="3"
            data-show-arrows="true"
            data-show-dots="false"
            data-pause-on-hover="true"
            aria-label="Class details slider"
          >
            <div class="card-carousel-track">
              @foreach($classesForPreview as $class)
                <div class="card-carousel-item">
                  @include('frontend.institutes.partials.school-profile.class-card', ['class' => $class])
                </div>
              @endforeach
            </div>
          </div>
        @else
          <div class="sch-classes-grid sch-classes-grid--full">
            @foreach($classes as $class)
              @include('frontend.institutes.partials.school-profile.class-card', ['class' => $class])
            @endforeach
          </div>
        @endif
      </div>
    @endif
  </section>
@endif

@if($showSection('facilities') && collect($institute->facilities ?? [])->isNotEmpty())
  <section id="sch-facilities" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Our Facilities</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'facilities',
        'total' => $profile->facilitiesCount(),
        'limit' => $previewLimits['facilities'],
        'inHead' => true,
        'alwaysShow' => $profile->facilitiesCount() > 0,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-facilities-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="3"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Facilities slider"
      >
        <div class="card-carousel-track">
          @foreach($profile->facilityCards($facilitiesCarouselLimit) as $facility)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.facility-card', ['facility' => $facility])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-facility-grid sch-facility-grid--full">
        @foreach($profile->facilityCards() as $facility)
          @include('frontend.institutes.partials.school-profile.facility-card', ['facility' => $facility])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('faculty') && $profile->facultyMembersCount() > 0)
  <section id="sch-faculty" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Faculty</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'faculty',
        'total' => $profile->facultyMembersCount(),
        'limit' => $previewLimits['faculty'],
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-faculty-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="3"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Faculty slider"
      >
        <div class="card-carousel-track">
          @foreach($profile->facultyMembers($facultyLimit) as $member)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.faculty-card', ['member' => $member])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-faculty-grid sch-faculty-grid--full">
        @foreach($profile->facultyMembers() as $member)
          @include('frontend.institutes.partials.school-profile.faculty-card', ['member' => $member])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('gallery') && $gallery->count() > 1)
  <section id="sch-gallery" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Gallery</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'gallery',
        'total' => $gallery->count(),
        'limit' => $previewLimits['gallery'],
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-gallery-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="4"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Gallery slider"
      >
        <div class="card-carousel-track">
          @foreach($galleryPreview as $imageUrl)
            <div class="card-carousel-item">
              <button type="button" class="sch-gallery-item js-sch-gallery-open" data-gallery-src="{{ $imageUrl }}">
                <img src="{{ $imageUrl }}" alt="Campus gallery image">
              </button>
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-gallery-grid sch-gallery-grid--full">
        @foreach($gallery as $imageUrl)
          <button type="button" class="sch-gallery-item js-sch-gallery-open" data-gallery-src="{{ $imageUrl }}">
            <img src="{{ $imageUrl }}" alt="Campus gallery image">
          </button>
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('achievements') && $achievements->isNotEmpty())
  <section id="sch-achievements" class="sch-card sch-section sch-achievements-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Achievements</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'achievements',
        'total' => $achievements->count(),
        'limit' => $previewLimits['achievements'],
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-achievements-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="3"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Achievements slider"
      >
        <div class="card-carousel-track">
          @foreach($achievementsPreview as $achievement)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.achievement-card', [
                'achievement' => $achievement,
                'carousel' => true,
              ])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-achievements-grid sch-achievements-grid--full">
        @foreach($achievements as $achievement)
          @include('frontend.institutes.partials.school-profile.achievement-card', ['achievement' => $achievement])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('results') && $performers->isNotEmpty())
  <section id="sch-results" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Placement / Results</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'results',
        'total' => $performers->count(),
        'limit' => $previewLimits['results'],
        'inHead' => true,
      ])
    </div>
    <div class="row g-3">
      @foreach($performersPreview as $performer)
        <div class="col-md-6 col-lg-4">
          <article class="sch-performer-card">
            <div class="sch-performer-card__avatar">
              @if($performer->photoUrl())
                <img src="{{ $performer->photoUrl() }}" alt="{{ $performer->student_name }}">
              @else
                <span>{{ strtoupper(substr($performer->student_name, 0, 1)) }}</span>
              @endif
            </div>
            <div>
              <h3>{{ $performer->student_name }}</h3>
              @if($performer->class_name)<p>{{ $performer->class_name }}</p>@endif
              <strong>{{ $performer->achievement_title }}</strong>
              @if($performer->score)<p class="mb-0">{{ $performer->score }}</p>@endif
            </div>
          </article>
        </div>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'results',
      'total' => $performers->count(),
      'limit' => $previewLimits['results'],
      'label' => 'results',
    ])
  </section>
@endif

@if($showSection('notes-materials') && $profile->notesMaterialBooksCount() > 0)
  <section id="sch-notes-material" class="sch-card sch-section sch-books-section">
    <div class="sch-section__head sch-books-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Notes &amp; Study Material</h2>
        <p class="sch-section__lead mb-0">Textbooks, notes, and study resources used across classes.</p>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'notes-materials',
        'total' => $profile->notesMaterialBooksCount(),
        'limit' => $previewLimits['notes-materials'],
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-books-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="3"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Notes and study material slider"
      >
        <div class="card-carousel-track">
          @foreach($notesPreview as $index => $book)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.book-card', ['book' => $book, 'index' => $index, 'carousel' => true])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-books-grid sch-books-grid--full">
        @foreach($profile->notesMaterialBooks() as $index => $book)
          @include('frontend.institutes.partials.school-profile.book-card', ['book' => $book, 'index' => $index])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('question-papers') && $profile->questionPaperBooksCount() > 0)
  <section id="sch-question-papers" class="sch-card sch-section sch-books-section">
    <div class="sch-section__head sch-books-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Question Papers</h2>
        <p class="sch-section__lead mb-0">Sample papers and previous-year papers for exam preparation.</p>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'question-papers',
        'total' => $profile->questionPaperBooksCount(),
        'limit' => $previewLimits['question-papers'],
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-books-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="3"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Question papers slider"
      >
        <div class="card-carousel-track">
          @foreach($questionPapersPreview as $index => $book)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.book-card', ['book' => $book, 'index' => $index, 'carousel' => true])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-books-grid sch-books-grid--full">
        @foreach($profile->questionPaperBooks() as $index => $book)
          @include('frontend.institutes.partials.school-profile.book-card', ['book' => $book, 'index' => $index])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('news') && $profile->newsItemsCount() > 0)
  <section id="sch-news" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Articles &amp; News</h2>
      @if($isProfilePreview && count($newsItemsOverflow) > 0)
        <button type="button" class="sch-section__link js-sch-articles-view-all">
          View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </button>
      @elseif($isProfilePreview)
        @include('frontend.institutes.partials.school-profile.section-view-all', [
          'institute' => $institute,
          'isProfilePreview' => $isProfilePreview,
          'sectionKey' => 'articles',
          'total' => $profile->newsItemsCount(),
          'limit' => $previewLimits['news'],
          'inHead' => true,
        ])
      @endif
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-news-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="{{ $newsCarouselCols }}"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Articles and news slider"
      >
        <div class="card-carousel-track">
          @foreach($newsItems as $news)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.news-card', ['news' => $news])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-news-list sch-news-list--full">
        @foreach($allNewsItems as $news)
          @include('frontend.institutes.partials.school-profile.news-card', ['news' => $news])
        @endforeach
      </div>
    @endif
    @if($isProfilePreview && count($newsItemsOverflow) > 0)
      @include('frontend.institutes.partials.school-profile.articles-modal', ['articles' => $newsItemsOverflow])
    @endif
  </section>
@endif

@if($showSection('events') && $profile->upcomingEventsCount() > 0)
  <section id="sch-events" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Upcoming Events</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'events',
        'total' => $profile->upcomingEventsCount(),
        'limit' => $previewLimits['events'],
        'inHead' => true,
      ])
    </div>
    <div class="sch-event-list">
      @foreach($events as $event)
        <article class="sch-event-item">
          <div class="sch-event-item__date"><strong>{{ $event['day'] }}</strong><span>{{ $event['month'] }}</span></div>
          <i class="fa-solid fa-arrow-right-long sch-event-item__arrow" aria-hidden="true"></i>
          <div>
            <h3>{{ $event['title'] }}</h3>
            <p>{{ $event['schedule'] }}</p>
          </div>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'events',
      'total' => $profile->upcomingEventsCount(),
      'limit' => $previewLimits['events'],
      'label' => 'events',
    ])
  </section>
@endif

@if($showSection('reviews') && $profile->reviewsCount() > 0)
  <section id="sch-reviews" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Top Reviews</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'reviews',
        'total' => $profile->reviewsCount(),
        'limit' => $previewLimits['reviews'],
        'inHead' => true,
      ])
    </div>
    <div class="sch-review-list">
      @foreach($reviews as $review)
        <article class="sch-review-card">
          <div class="sch-review-card__head">
            <span class="sch-review-card__avatar sch-review-card__avatar--{{ $review['tone'] }}">{{ $review['initial'] }}</span>
            <div>
              <h3>{{ $review['name'] }} <small>({{ $review['relation'] }})</small></h3>
              <div class="sch-review-card__meta">
                <span class="sch-stars">
                  @for($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star {{ $i <= round($review['rating']) ? 'is-filled' : '' }}"></i>
                  @endfor
                </span>
                <strong>{{ number_format($review['rating'], 1) }}</strong>
                <span>{{ $review['ago'] }}</span>
              </div>
            </div>
          </div>
          <p>{{ $review['comment'] }}</p>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'reviews',
      'total' => $profile->reviewsCount(),
      'limit' => $previewLimits['reviews'],
      'label' => 'reviews',
    ])
  </section>
@endif
