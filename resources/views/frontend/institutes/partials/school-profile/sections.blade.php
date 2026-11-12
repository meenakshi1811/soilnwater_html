@php
  use App\Support\SchoolProfilePresenter;

  $onlySection = $onlySection ?? null;
  $isProfilePreview = $isProfilePreview ?? ($onlySection === null);
  $previewLimits = SchoolProfilePresenter::PREVIEW_LIMITS;
  // (sections the caller renders elsewhere, e.g. the profile shows Jobs under Admission Info)
  $skipSections = $skipSections ?? [];
  $showSection = fn (string $key): bool => $onlySection === null
    ? ! in_array($key, $skipSections, true)
    : $onlySection === $key;
  $canEditInline = $canEditInline ?? false;
  // Public section key => inline editor key for its pencil button
  $inlinePencil = fn (string $editorKey) => $canEditInline
    ? '<button type="button" class="sch-inline-pencil js-sch-inline-edit" data-inline-edit="'.$editorKey.'" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>'
    : '';

  $classes = $institute->schoolClasses;
  $achievements = $institute->achievements;
  $performers = $institute->topPerformers;
  $gallery = $profile->galleryItems();
  $classDetailsLimit = $isProfilePreview ? ($previewLimits['class-details'] ?? null) : null;
  // (all of them: on the profile they slide, like the notes)
  $courseWingsPreview = $profile->courseWings();
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
  $allEvents = $profile->upcomingEvents();
  $reviewsLimit = $isProfilePreview ? ($previewLimits['reviews'] ?? null) : null;
  $allNewsItems = $profile->newsItems();
  $newsItems = $newsLimit !== null ? array_slice($allNewsItems, 0, $newsLimit) : $allNewsItems;
  $newsItemsOverflow = $newsLimit !== null ? array_slice($allNewsItems, $newsLimit) : [];
  $newsCarouselCols = min(max(count($newsItems), 1), 4);
  $events = $profile->upcomingEvents($eventsLimit);
  $eventsCarouselCols = min(max(count($events), 1), 3);
  $reviews = $profile->reviews($reviewsLimit);
  $jobsLimit = $isProfilePreview ? ($previewLimits['jobs'] ?? null) : null;
  $openJobsTotal = $profile->openJobsCount();
  $jobsPreview = $jobsLimit !== null ? $profile->openJobs($jobsLimit) : $profile->openJobs();
  $jobsCarouselCols = min(max($jobsPreview->count(), 1), 3);
  $listingContext = $listingContext ?? (\App\Support\SchoolInstituteHelper::listingContext($institute->user?->role));
  $appliedJobIds = [];
  if (auth()->check()) {
      $appliedJobIds = \App\Models\InstituteJobApplication::query()
          ->whereHas('job', fn ($query) => $query->where('institute_id', $institute->id))
          ->where('user_id', auth()->id())
          ->pluck('institute_job_id')
          ->all();
  }
@endphp

{{-- (the school's own course cards, or the standard ones until it saves its own) --}}
@if($showSection('courses') && ($courseWingsPreview !== [] || $classes->isNotEmpty() || $institute->gradesOfferedEntries() !== []))
  <section id="sch-courses" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Courses &amp; Programs Offered</h2>{!! $inlinePencil('courses') !!}
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'courses',
        'total' => $coursesSectionTotal,
        'limit' => $previewLimits['courses'],
        'inHead' => true,
      ])
    </div>
    @if($courseWingsPreview !== [] && $isProfilePreview)
      <div
        class="sch-profile-carousel sch-courses-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="3"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Courses and programs slider"
      >
        <div class="card-carousel-track">
          @foreach($courseWingsPreview as $wing)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.course-card', ['wing' => $wing])
            </div>
          @endforeach
        </div>
      </div>
    @elseif($courseWingsPreview !== [])
      <div class="sch-course-grid">
        @foreach($courseWingsPreview as $wing)
          @include('frontend.institutes.partials.school-profile.course-card', ['wing' => $wing])
        @endforeach
      </div>
    @endif
    @if(!$isProfilePreview && $profile->streams() !== [])
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
          <h3 class="sch-classes-detail__title mb-0">Class details</h3>{!! $inlinePencil('classes') !!}
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
    @elseif($institute->gradesOfferedEntries() !== [])
      @include('frontend.institutes.partials.school-profile.classes-offered', ['institute' => $institute])
    @endif
  </section>
@endif

@if($showSection('facilities') && collect($institute->facilities ?? [])->isNotEmpty())
  <section id="sch-facilities" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Our Facilities</h2>{!! $inlinePencil('facilities') !!}
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

{{-- The notes uploaded for this school (the notes page has them all) --}}
{{-- (once: not again where the page shows only Jobs, under Admission Info) --}}
@if($isProfilePreview && $showSection('school-notes') && $profile->schoolNotesCount() > 0)
  <section id="sch-notes" class="sch-card sch-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Notes</h2>
      <a href="{{ $profile->schoolNotesUrl() }}" class="sch-section__link">
        View all ({{ number_format($profile->schoolNotesCount()) }}) <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
    <div
      class="sch-profile-carousel sch-notes-carousel card-carousel auto-ad-slider"
      data-slide-by="card"
      data-carousel-cols="3"
      data-show-arrows="true"
      data-show-dots="false"
      data-pause-on-hover="true"
      aria-label="Notes slider"
    >
      <div class="card-carousel-track">
        @foreach($profile->schoolNotes() as $note)
          <div class="card-carousel-item">
            @include('frontend.institutes.partials.school-profile.note-card', ['note' => $note])
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

@if($showSection('gallery') && $gallery->count() > 1)
  <section id="sch-gallery" class="sch-card sch-section">
    <div class="sch-section__head sch-section__head--stack">
      <div>
        <h2 class="sch-section__title mb-1">Gallery</h2>{!! $inlinePencil('gallery') !!}
        <p class="sch-section__lead mb-0">Campus photos and videos. Videos play only when you tap Play.</p>
      </div>
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
          @foreach($galleryPreview as $item)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.gallery-item', ['item' => $item])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-gallery-grid sch-gallery-grid--full">
        @foreach($gallery as $item)
          @include('frontend.institutes.partials.school-profile.gallery-item', ['item' => $item])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('achievements') && $achievements->isNotEmpty())
  <section id="sch-achievements" class="sch-card sch-section sch-achievements-section">
    <div class="sch-section__head">
      <h2 class="sch-section__title mb-0">Achievements</h2>{!! $inlinePencil('achievements') !!}
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
      <h2 class="sch-section__title mb-0">Results</h2>{!! $inlinePencil('performers') !!}
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'results',
        'total' => $performers->count(),
        'limit' => $previewLimits['results'],
        'inHead' => true,
        'alwaysShow' => true,
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
  </section>
@endif

@if($showSection('jobs') && $openJobsTotal > 0)
  <section id="sch-jobs" class="sch-card sch-section sch-jobs-section">
    <div class="sch-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Jobs &amp; Careers</h2>{!! $inlinePencil('jobs') !!}
        <p class="sch-section__lead mb-0">Latest openings at this institution. Log in to apply online.</p>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'jobs',
        'total' => $openJobsTotal,
        'limit' => $previewLimits['jobs'],
        'label' => 'open roles',
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-jobs-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="{{ $jobsCarouselCols }}"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Job openings carousel"
      >
        <div class="card-carousel-track">
          @foreach($jobsPreview as $job)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.job-card', [
                'job' => $job,
                'institute' => $institute,
                'listingContext' => $listingContext,
                'appliedJobIds' => $appliedJobIds,
              ])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-jobs-grid sch-jobs-grid--full">
        @foreach($jobsPreview as $job)
          @include('frontend.institutes.partials.school-profile.job-card', [
            'job' => $job,
            'institute' => $institute,
            'listingContext' => $listingContext,
            'appliedJobIds' => $appliedJobIds,
          ])
        @endforeach
      </div>
    @endif
  </section>
@endif

{{-- Scholarships: on the profile a sliding row of short cards; on its own page every detail --}}
@if($showSection('scholarships') && $profile->openScholarshipsCount() > 0)
  @php
    $scholarshipsPreview = $profile->openScholarships($isProfilePreview ? $previewLimits['scholarships'] : null);
  @endphp
  <section id="sch-scholarships" class="sch-card sch-section sch-scholarships-section">
    <div class="sch-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Scholarships</h2>
        <p class="sch-section__lead mb-0">Scholarships this {{ strtolower($entityLabel ?? 'institution') }} offers: who can apply, what they give and how to apply.</p>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'scholarships',
        'total' => $profile->openScholarshipsCount(),
        'limit' => $previewLimits['scholarships'],
        'label' => 'scholarships',
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-scholarships-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="{{ min(max($scholarshipsPreview->count(), 1), 3) }}"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Scholarships carousel"
      >
        <div class="card-carousel-track">
          @foreach($scholarshipsPreview as $scholarship)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.scholarship-card', ['scholarship' => $scholarship, 'institute' => $institute])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-scholarships-list">
        @foreach($scholarshipsPreview as $scholarship)
          @include('frontend.institutes.partials.school-profile.scholarship-card', ['scholarship' => $scholarship, 'institute' => $institute, 'full' => true])
        @endforeach
      </div>
    @endif
  </section>
@endif

@if($showSection('notes-materials') && $profile->notesMaterialBooksCount() > 0)
  <section id="sch-notes-material" class="sch-card sch-section sch-books-section">
    <div class="sch-section__head sch-books-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Notes &amp; Study Material</h2>{!! $inlinePencil('books') !!}
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
        <h2 class="sch-section__title mb-1">Question Papers</h2>{!! $inlinePencil('books') !!}
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
          'alwaysShow' => true,
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
  <section id="sch-events" class="sch-card sch-section sch-events-section">
    <div class="sch-section__head">
      <div>
        <h2 class="sch-section__title mb-1">Upcoming Events</h2>
        <p class="sch-section__lead mb-0">Important dates and activities on campus.</p>
      </div>
      @include('frontend.institutes.partials.school-profile.section-view-all', [
        'institute' => $institute,
        'isProfilePreview' => $isProfilePreview,
        'sectionKey' => 'events',
        'total' => $profile->upcomingEventsCount(),
        'limit' => $previewLimits['events'],
        'inHead' => true,
        'alwaysShow' => true,
      ])
    </div>
    @if($isProfilePreview)
      <div
        class="sch-profile-carousel sch-events-carousel card-carousel auto-ad-slider"
        data-slide-by="card"
        data-carousel-cols="{{ $eventsCarouselCols }}"
        data-show-arrows="true"
        data-show-dots="false"
        data-pause-on-hover="true"
        aria-label="Upcoming events slider"
      >
        <div class="card-carousel-track">
          @foreach($events as $event)
            <div class="card-carousel-item">
              @include('frontend.institutes.partials.school-profile.event-card', ['event' => $event])
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="sch-events-grid sch-events-grid--full">
        @foreach($allEvents as $event)
          @include('frontend.institutes.partials.school-profile.event-card', ['event' => $event])
        @endforeach
      </div>
    @endif
  </section>
@endif

{{-- (the admin's reviews, or visitors': then the section always shows, with its review form) --}}
@if($showSection('reviews') && ($profile->reviewsCount() > 0 || ! $profile->useAdminReviews()))
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
          @if(filled($review['comment']))
            <p>{{ $review['comment'] }}</p>
          @endif
        </article>
      @endforeach
    </div>
    @if(! $profile->useAdminReviews())
      @if($reviews === [])
        <p class="sch-review-empty">No reviews yet. Be the first to review this {{ strtolower($entityLabel ?? 'school') }}.</p>
      @endif
      @include('frontend.institutes.partials.school-profile.review-form')
    @endif
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
