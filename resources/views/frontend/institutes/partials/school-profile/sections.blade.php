@php
  use App\Support\SchoolProfilePresenter;

  $onlySection = $onlySection ?? null;
  $isProfilePreview = $isProfilePreview ?? ($onlySection === null);
  $previewLimits = SchoolProfilePresenter::PREVIEW_LIMITS;
  $showSection = fn (string $key): bool => $onlySection === null || $onlySection === $key;

  $classes = $institute->schoolClasses;
  $achievements = $institute->achievements;
  $performers = $institute->topPerformers;
  $books = $institute->books;
  $gallery = $profile->galleryImages();
  $classesLimit = $isProfilePreview ? ($previewLimits['courses'] ?? null) : null;
  $classesPreview = $classesLimit !== null ? $classes->take($classesLimit) : $classes;
  $facilitiesLimit = $isProfilePreview ? ($previewLimits['facilities'] ?? null) : null;
  $facultyLimit = $isProfilePreview ? ($previewLimits['faculty'] ?? null) : null;
  $galleryLimit = $isProfilePreview ? ($previewLimits['gallery'] ?? null) : null;
  $galleryPreview = $galleryLimit !== null ? $gallery->take($galleryLimit) : $gallery;
  $achievementsLimit = $isProfilePreview ? ($previewLimits['achievements'] ?? null) : null;
  $achievementsPreview = $achievementsLimit !== null ? $achievements->take($achievementsLimit) : $achievements;
  $resultsLimit = $isProfilePreview ? ($previewLimits['results'] ?? null) : null;
  $performersPreview = $resultsLimit !== null ? $performers->take($resultsLimit) : $performers;
  $booksLimit = $isProfilePreview ? ($previewLimits['books'] ?? null) : null;
  $booksPreview = $booksLimit !== null ? $books->take($booksLimit) : $books;
  $newsLimit = $isProfilePreview ? ($previewLimits['news'] ?? null) : null;
  $eventsLimit = $isProfilePreview ? ($previewLimits['events'] ?? null) : null;
  $reviewsLimit = $isProfilePreview ? ($previewLimits['reviews'] ?? null) : null;
  $newsItems = $profile->newsItems($newsLimit);
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
        'total' => $classes->count(),
        'limit' => $previewLimits['courses'],
        'inHead' => true,
      ])
    </div>
    <div class="sch-course-grid">
      @foreach($profile->courseWings() as $wing)
        <article class="sch-course-card">
          <span class="sch-course-card__icon"><i class="fa-solid {{ $wing['icon'] }}" aria-hidden="true"></i></span>
          <h3>{{ $wing['title'] }}</h3>
          <p class="sch-course-card__grades">{{ $wing['grades'] }}</p>
          <p>{{ $wing['description'] }}</p>
        </article>
      @endforeach
    </div>
    <div class="sch-streams">
      <h3>Streams in Class XI &amp; XII</h3>
      <div class="sch-streams__list">
        @foreach($profile->streams() as $stream)
          <span class="sch-stream-chip">{{ $stream }}</span>
        @endforeach
      </div>
    </div>
    @if($classes->isNotEmpty())
      <div id="sch-classes-detail" class="sch-classes-detail">
        <h3>Class details</h3>
        <div class="row g-3">
          @foreach($classesPreview as $class)
            <div class="col-md-6">
              <article class="sch-class-card">
                <h4>{{ $class->displayLabel() }}</h4>
                @if($class->class_teacher)<p><i class="fa-solid fa-user-tie"></i> {{ $class->class_teacher }}</p>@endif
                @if($class->strength)<p><i class="fa-solid fa-users"></i> {{ $class->strength }} students</p>@endif
                @if($class->description)<p class="text-secondary mb-0">{{ $class->description }}</p>@endif
              </article>
            </div>
          @endforeach
        </div>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'courses',
        'total' => $classes->count(),
        'limit' => $previewLimits['courses'],
        'label' => 'classes',
      ])
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
      ])
    </div>
    <div class="sch-facility-grid">
      @foreach($profile->facilityCards($facilitiesLimit) as $facility)
        <article class="sch-facility-card">
          @if($facility['image'])
            <img src="{{ $facility['image'] }}" alt="{{ $facility['name'] }}">
          @else
            <div class="sch-facility-card__placeholder"><i class="fa-solid fa-building"></i></div>
          @endif
          <span>{{ $facility['name'] }}</span>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'facilities',
      'total' => $profile->facilitiesCount(),
      'limit' => $previewLimits['facilities'],
      'label' => 'facilities',
    ])
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
      ])
    </div>
    <div class="sch-faculty-grid">
      @foreach($profile->facultyMembers($facultyLimit) as $member)
        <article class="sch-faculty-card">
          <span class="sch-faculty-card__avatar">{{ strtoupper(substr($member['name'], 0, 1)) }}</span>
          <div>
            <h3>{{ $member['name'] }}</h3>
            <p>{{ $member['role'] }}</p>
          </div>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'faculty',
      'total' => $profile->facultyMembersCount(),
      'limit' => $previewLimits['faculty'],
      'label' => 'faculty members',
    ])
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
      ])
    </div>
    <div class="sch-gallery-grid">
      @foreach($galleryPreview as $imageUrl)
        <button type="button" class="sch-gallery-item js-sch-gallery-open" data-gallery-src="{{ $imageUrl }}">
          <img src="{{ $imageUrl }}" alt="Campus gallery image">
        </button>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'gallery',
      'total' => $gallery->count(),
      'limit' => $previewLimits['gallery'],
      'label' => 'photos',
    ])
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
      ])
    </div>
    <div class="sch-achievements-grid">
      @foreach($achievementsPreview as $achievement)
        @php
          $achievementImage = $achievement->imageUrl();
          $metaParts = array_filter([$achievement->category, $achievement->year]);
        @endphp
        <article class="sch-achievement-card {{ $achievementImage ? 'sch-achievement-card--with-image' : 'sch-achievement-card--text-only' }}">
          @if($achievementImage)
            <div class="sch-achievement-card__media">
              <img src="{{ $achievementImage }}" alt="{{ $achievement->title }}">
            </div>
          @endif
          <div class="sch-achievement-card__body">
            @if($metaParts !== [])
              <p class="sch-achievement-card__meta">{{ implode(' ', $metaParts) }}</p>
            @endif
            <h3 class="sch-achievement-card__title">{{ $achievement->title }}</h3>
            @if($achievement->description)
              <p class="sch-achievement-card__desc">{{ $achievement->description }}</p>
            @endif
          </div>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'achievements',
      'total' => $achievements->count(),
      'limit' => $previewLimits['achievements'],
      'label' => 'achievements',
    ])
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

@if($showSection('books') && $books->isNotEmpty())
  <section id="sch-books" class="sch-card sch-section sch-books-section">
    <div class="sch-section__head sch-books-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Students Corner</h2>
        <p class="sch-section__lead mb-0">Textbooks and study resources used across classes.</p>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'books',
        'total' => $books->count(),
        'limit' => $previewLimits['books'],
        'inHead' => true,
      ])
    </div>
    <div class="sch-books-grid">
      @foreach($booksPreview as $index => $book)
        @php
          $subjectKey = strtolower((string) $book->subject);
          $bookIcon = match (true) {
            str_contains($subjectKey, 'math') => 'fa-calculator',
            str_contains($subjectKey, 'phys') => 'fa-atom',
            str_contains($subjectKey, 'chem') => 'fa-flask',
            str_contains($subjectKey, 'bio') => 'fa-dna',
            str_contains($subjectKey, 'english') => 'fa-feather-pointed',
            str_contains($subjectKey, 'science') => 'fa-microscope',
            default => 'fa-book-open',
          };
          $metaParts = array_filter([$book->class_name, $book->subject]);
        @endphp
        <article class="sch-book-card sch-book-card--row sch-book-card--tone-{{ ($index % 6) + 1 }}">
          <div class="sch-book-card__media">
            @if($book->coverUrl())
              <img src="{{ $book->coverUrl() }}" alt="" class="sch-book-card__cover-img">
            @else
              <span class="sch-book-card__cover-icon" aria-hidden="true">
                <i class="fa-solid {{ $bookIcon }}"></i>
              </span>
            @endif
          </div>
          <div class="sch-book-card__body">
            @if($metaParts !== [])
              <p class="sch-book-card__meta">{{ implode(' · ', $metaParts) }}</p>
            @endif
            <h3 class="sch-book-card__title">{{ $book->title }}</h3>
            <p class="sch-book-card__author">
              <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
              <span>{{ $book->author }}</span>
            </p>
            @if($book->publisher)
              <p class="sch-book-card__publisher">{{ $book->publisher }}</p>
            @endif
            @if($book->class_name || $book->subject)
              <div class="sch-book-card__tags">
                @if($book->class_name)<span>{{ $book->class_name }}</span>@endif
                @if($book->subject)<span>{{ $book->subject }}</span>@endif
              </div>
            @endif
          </div>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'books',
      'total' => $books->count(),
      'limit' => $previewLimits['books'],
      'label' => 'resources',
    ])
  </section>
@endif

@if($showSection('news') && $profile->newsItemsCount() > 0)
  <section id="sch-news" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Latest News &amp; Announcements</h2>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'news',
        'total' => $profile->newsItemsCount(),
        'limit' => $previewLimits['news'],
        'inHead' => true,
      ])
    </div>
    <div class="sch-news-list">
      @foreach($newsItems as $news)
        <article class="sch-news-item">
          <div class="sch-news-item__date"><strong>{{ $news['day'] }}</strong><span>{{ $news['month'] }}</span></div>
          <div>
            <h3>{{ $news['title'] }}</h3>
            <p>{{ $news['excerpt'] }}</p>
          </div>
        </article>
      @endforeach
    </div>
    @include('frontend.institutes.partials.school-profile.section-view-all', [
      'institute' => $institute,
      'isProfilePreview' => $isProfilePreview,
      'sectionKey' => 'news',
      'total' => $profile->newsItemsCount(),
      'limit' => $previewLimits['news'],
      'label' => 'announcements',
    ])
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
