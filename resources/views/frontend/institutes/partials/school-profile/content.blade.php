@php
  $classes = $institute->schoolClasses;
  $achievements = $institute->achievements;
  $performers = $institute->topPerformers;
  $books = $institute->books;
  $gallery = $profile->galleryImages();
  $notices = $institute->activeNotices;
  $reviews = $profile->reviews();
@endphp

<div class="sch-content-grid">
  <div class="sch-content-main">
    @if($aboutText)
      <section id="sch-about" class="sch-card sch-section">
        <h2 class="sch-section__title">About the School</h2>
        <div class="sch-about">
          <div class="sch-about__text js-sch-about-text {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{!! nl2br(e($aboutText)) !!}</div>
          @if($aboutNeedsToggle)
            <button type="button" class="sch-read-more js-sch-read-more">Read More <i class="fa-solid fa-chevron-down"></i></button>
          @endif
          <ul class="sch-check-list">
            @foreach($profile->aboutHighlights() as $point)
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
            @endforeach
          </ul>
        </div>
      </section>
    @endif

    <section id="sch-courses" class="sch-card sch-section">
      <div class="sch-section__head">
        <h2 class="sch-section__title mb-0">Courses &amp; Programs Offered</h2>
        <a href="#sch-classes-detail" class="sch-section__link js-sch-nav-link">View All Courses</a>
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
            @foreach($classes as $class)
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
      @endif
    </section>

    @if(collect($institute->facilities ?? [])->isNotEmpty())
      <section id="sch-facilities" class="sch-card sch-section">
        <div class="sch-section__head">
          <h2 class="sch-section__title mb-0">Our Facilities</h2>
          <a href="#sch-facilities" class="sch-section__link">View All</a>
        </div>
        <div class="sch-facility-grid">
          @foreach($profile->facilityCards() as $facility)
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
      </section>
    @endif

    @if($profile->facultyMembers())
      <section id="sch-faculty" class="sch-card sch-section">
        <h2 class="sch-section__title">Faculty</h2>
        <div class="sch-faculty-grid">
          @foreach($profile->facultyMembers() as $member)
            <article class="sch-faculty-card">
              <span class="sch-faculty-card__avatar">{{ strtoupper(substr($member['name'], 0, 1)) }}</span>
              <div>
                <h3>{{ $member['name'] }}</h3>
                <p>{{ $member['role'] }}</p>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    @endif

    @if($gallery->count() > 1)
      <section id="sch-gallery" class="sch-card sch-section">
        <h2 class="sch-section__title">Gallery</h2>
        <div class="sch-gallery-grid">
          @foreach($gallery as $imageUrl)
            <button type="button" class="sch-gallery-item js-sch-gallery-open" data-gallery-src="{{ $imageUrl }}">
              <img src="{{ $imageUrl }}" alt="Campus gallery image">
            </button>
          @endforeach
        </div>
      </section>
    @endif

    @if($achievements->isNotEmpty())
      <section id="sch-achievements" class="sch-card sch-section">
        <h2 class="sch-section__title">Achievements</h2>
        <div class="row g-3">
          @foreach($achievements as $achievement)
            <div class="col-md-6">
              <article class="sch-achievement-card">
                @if($achievement->imageUrl())
                  <img src="{{ $achievement->imageUrl() }}" alt="">
                @endif
                <div>
                  <div class="sch-achievement-card__meta">
                    @if($achievement->category)<span>{{ $achievement->category }}</span>@endif
                    @if($achievement->year)<span>{{ $achievement->year }}</span>@endif
                  </div>
                  <h3>{{ $achievement->title }}</h3>
                  @if($achievement->description)<p>{{ $achievement->description }}</p>@endif
                </div>
              </article>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    @if($performers->isNotEmpty())
      <section id="sch-results" class="sch-card sch-section">
        <h2 class="sch-section__title">Placement / Results</h2>
        <div class="row g-3">
          @foreach($performers as $performer)
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

    @if($books->isNotEmpty())
      <section id="sch-books" class="sch-card sch-section">
        <h2 class="sch-section__title">Students Corner</h2>
        <div class="row g-3">
          @foreach($books as $book)
            <div class="col-md-6 col-lg-4">
              <article class="sch-book-card">
                @if($book->coverUrl())
                  <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}">
                @endif
                <div>
                  <h3>{{ $book->title }}</h3>
                  <p>By {{ $book->author }}</p>
                  @if($book->class_name || $book->subject)
                    <div class="sch-book-card__tags">
                      @if($book->class_name)<span>{{ $book->class_name }}</span>@endif
                      @if($book->subject)<span>{{ $book->subject }}</span>@endif
                    </div>
                  @endif
                </div>
              </article>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    <section id="sch-admission" class="sch-card sch-section">
      <h2 class="sch-section__title">Admission Info</h2>
      <p class="sch-section__lead">Admissions are open for select grades. Submit an enquiry below or contact the admission office during operating hours.</p>
      <ul class="sch-check-list">
        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Online enquiry and campus visit scheduling available</li>
        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Document checklist shared after initial enquiry</li>
        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Entrance assessment for senior grades where applicable</li>
      </ul>
    </section>

    @if($notices->isNotEmpty())
      <section id="sch-news" class="sch-card sch-section">
        <div class="sch-section__head">
          <h2 class="sch-section__title mb-0">Latest News &amp; Announcements</h2>
          <a href="#sch-events" class="sch-section__link js-sch-nav-link">View All</a>
        </div>
        <div class="sch-news-list">
          @foreach($profile->newsItems() as $news)
            <article class="sch-news-item">
              <div class="sch-news-item__date"><strong>{{ $news['day'] }}</strong><span>{{ $news['month'] }}</span></div>
              <div>
                <h3>{{ $news['title'] }}</h3>
                <p>{{ $news['excerpt'] }}</p>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    @endif

    @if($profile->upcomingEvents())
      <section id="sch-events" class="sch-card sch-section">
        <div class="sch-section__head">
          <h2 class="sch-section__title mb-0">Upcoming Events</h2>
          <a href="#sch-events" class="sch-section__link">View All</a>
        </div>
        <div class="sch-event-list">
          @foreach($profile->upcomingEvents() as $event)
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
      </section>
    @endif

    @if($reviews)
      <section id="sch-reviews" class="sch-card sch-section">
        <div class="sch-section__head">
          <h2 class="sch-section__title mb-0">Top Reviews</h2>
          <a href="#sch-reviews" class="sch-section__link">View All</a>
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
      </section>
    @endif

    <section id="sch-contact" class="sch-card sch-section sch-enquiry-section">
      <h2 class="sch-section__title">Enquiry &amp; Contact</h2>
      @guest
        <p class="mb-0">Please <a href="{{ route('login') }}">login</a> to send an enquiry to this school.</p>
      @else
        <form id="schoolEnquiryForm" class="sch-enquiry-form">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_name">Your name</label>
              <input type="text" class="form-control" id="school_enquiry_name" name="name" value="{{ $authUser->name }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_email">Email</label>
              <input type="email" class="form-control" id="school_enquiry_email" name="email" value="{{ $authUser->email }}">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_phone">Phone</label>
              <input type="text" class="form-control" id="school_enquiry_phone" name="phone" value="{{ $authUser->phone_number }}">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="school_enquiry_subject">Subject</label>
              <input type="text" class="form-control" id="school_enquiry_subject" name="subject" placeholder="Admission enquiry">
            </div>
            <div class="col-12">
              <label class="form-label" for="school_enquiry_message">Message</label>
              <textarea class="form-control" id="school_enquiry_message" name="message" rows="4" required placeholder="Tell us about your enquiry..."></textarea>
            </div>
            <div class="col-12">
              <div id="schoolEnquiryFeedback" class="alert d-none" role="alert"></div>
              <button type="submit" class="sch-btn sch-btn-primary js-school-enquiry-submit">
                <span class="js-enquiry-btn-text">Send enquiry</span>
                <span class="js-enquiry-btn-sending d-none">Sending...</span>
              </button>
            </div>
          </div>
        </form>
      @endguest
    </section>
  </div>

  <div class="sch-content-aside">
    <div class="sch-card sch-glance-card">
      <h2>At a Glance</h2>
      <ul class="sch-glance-list">
        @foreach($profile->atAGlance() as $item)
          <li>
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            <div>
              <span>{{ $item['label'] }}</span>
              <strong>{{ $item['value'] }}</strong>
            </div>
          </li>
        @endforeach
      </ul>
    </div>

    @if($profile->mapEmbedUrl())
      <div class="sch-card sch-map-card">
        <h2>Location Map</h2>
        <div class="sch-map-wrap">
          <iframe src="{{ $profile->mapEmbedUrl() }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="School location map"></iframe>
        </div>
        @if($profile->directionsUrl())
          <a href="{{ $profile->directionsUrl() }}" target="_blank" rel="noopener" class="sch-btn sch-btn-outline sch-btn-block">
            <i class="fa-solid fa-diamond-turn-right" aria-hidden="true"></i> Get Directions
          </a>
        @endif
      </div>
    @endif
  </div>
</div>
