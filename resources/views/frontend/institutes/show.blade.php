@extends('frontend.layouts.app')

@section('meta_title', $institute->displayName().' | Schools & Institutes | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($institute->about ?: $institute->tagline ?: $institute->description ?: 'School profile on SoilnWater'), 160))

@php
  $photo = $institute->logoUrl() ?: asset('assets/images/logo_soilnwater.webp');
  $bannerPhoto = $institute->galleryUrls()[0] ?? $photo;
  $grades = collect($institute->grades_offered ?? []);
  $facilities = collect($institute->facilities ?? []);
  $gallery = collect($institute->galleryUrls());
  $notices = collect($institute->activeNotices ?? []);
  $achievements = collect($institute->achievements ?? []);
  $performers = collect($institute->topPerformers ?? []);
  $classes = collect($institute->schoolClasses ?? []);
  $books = collect($institute->books ?? []);
  $authUser = auth()->user();
  $aboutText = trim((string) ($institute->about ?: $institute->description));
  $aboutNeedsToggle = strlen($aboutText) > 320;
  $shareUrl = $institute->publicUrl();
  $establishedYear = $institute->establishedYear();

  $navItems = collect([
    ['id' => 'sch-overview', 'label' => 'Overview', 'icon' => 'fa-school'],
    ['id' => 'sch-about', 'label' => 'About', 'icon' => 'fa-circle-info', 'show' => filled($aboutText)],
    ['id' => 'sch-classes', 'label' => 'Classes', 'icon' => 'fa-chalkboard', 'show' => $classes->isNotEmpty() || $grades->isNotEmpty()],
    ['id' => 'sch-performers', 'label' => 'Top Performers', 'icon' => 'fa-medal', 'show' => $performers->isNotEmpty()],
    ['id' => 'sch-achievements', 'label' => 'Achievements', 'icon' => 'fa-trophy', 'show' => $achievements->isNotEmpty()],
    ['id' => 'sch-books', 'label' => 'Books & Authors', 'icon' => 'fa-book', 'show' => $books->isNotEmpty()],
    ['id' => 'sch-facilities', 'label' => 'Facilities', 'icon' => 'fa-building', 'show' => $facilities->isNotEmpty()],
    ['id' => 'sch-gallery', 'label' => 'Gallery', 'icon' => 'fa-images', 'show' => $gallery->count() > 1],
    ['id' => 'sch-contact', 'label' => 'Contact', 'icon' => 'fa-envelope'],
  ])->filter(fn ($item) => ($item['show'] ?? true))->values()->all();
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div
  class="sch-page"
  id="instituteProfilePage"
  data-enquiry-url="{{ route(($listingContext ?? 'schools').'.enquiry', $institute->slug) }}"
  data-login-url="{{ route('login') }}"
  data-is-auth="{{ auth()->check() ? '1' : '0' }}"
  data-share-url="{{ $shareUrl }}"
  data-share-title="{{ $institute->displayName() }}"
>
  <div class="container-fluid sch-container">
    @if(session('status'))
      <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <nav class="sch-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route(($listingContext ?? 'schools').'.index') }}">{{ ($ownerRole ?? 'school') === 'school' ? 'Schools' : 'Institutes' }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="sch-breadcrumb__current" aria-current="page">{{ $institute->displayName() }}</span>
    </nav>

    <div class="sch-nav-mobile">
      <div class="sch-nav-mobile__inner">
        @foreach($navItems as $item)
          <a href="#{{ $item['id'] }}" class="sch-nav-mobile__link js-sch-nav-link">
            <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
            {{ $item['label'] }}
          </a>
        @endforeach
      </div>
    </div>

    <div class="sch-profile-top">
      <div class="sch-profile-banner" style="--sch-banner-image: url('{{ $bannerPhoto }}');">
        <div class="sch-profile-banner__overlay"></div>
        <div class="sch-profile-banner__content">
          <img src="{{ $photo }}" alt="{{ $institute->displayName() }}" class="sch-profile-banner__logo" onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_soilnwater.webp') }}';">
          <div>
            <div class="sch-profile-banner__badges">
              <span class="sch-badge sch-badge--primary">{{ $institute->institutionTypeLabel() }}</span>
              @if($institute->is_verified)
                <span class="sch-badge sch-badge--success"><i class="fa-solid fa-circle-check"></i> Verified</span>
              @endif
              @if($institute->board_affiliation)
                <span class="sch-badge sch-badge--light">{{ $institute->board_affiliation }}</span>
              @endif
            </div>
            <h1 class="sch-profile-banner__title">{{ $institute->displayName() }}</h1>
            @if($institute->tagline)
              <p class="sch-profile-banner__tagline">{{ $institute->tagline }}</p>
            @endif
            <p class="sch-profile-banner__location mb-0">
              <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
              {{ $institute->address ? $institute->address.', ' : '' }}{{ $institute->locationLabel() }} {{ $institute->pincode }}
            </p>
          </div>
        </div>
      </div>

      @if($notices->isNotEmpty())
        @include('frontend.institutes.partials.notice-board', ['notices' => $notices])
      @endif
    </div>

    <div class="sch-grid">
      <aside class="sch-nav" aria-label="School profile sections">
        <div class="sch-nav__inner">
          <ul class="sch-nav__list">
            @foreach($navItems as $loopIndex => $item)
              <li>
                <a href="#{{ $item['id'] }}" class="sch-nav__link js-sch-nav-link {{ $loopIndex === 0 ? 'is-active' : '' }}">
                  <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                  {{ $item['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
          <div class="sch-nav__share">
            <button type="button" class="sch-btn sch-btn-outline js-sch-share">
              <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Share
            </button>
          </div>
        </div>
      </aside>

      <main class="sch-main">
        <section id="sch-overview" class="sch-section sch-overview">
          <h2 class="sch-section__title"><i class="fa-solid fa-school" aria-hidden="true"></i> School Overview</h2>
          <div class="sch-stats">
            @if($establishedYear)
              <div class="sch-stat">
                <span class="sch-stat__value">{{ $establishedYear }}</span>
                <span class="sch-stat__label">Established</span>
              </div>
            @endif
            @if($institute->board_affiliation)
              <div class="sch-stat">
                <span class="sch-stat__value">{{ $institute->board_affiliation }}</span>
                <span class="sch-stat__label">Board</span>
              </div>
            @endif
            <div class="sch-stat">
              <span class="sch-stat__value">{{ $classes->count() ?: $grades->count() ?: '—' }}</span>
              <span class="sch-stat__label">{{ $classes->isNotEmpty() ? 'Classes listed' : 'Grades offered' }}</span>
            </div>
            <div class="sch-stat">
              <span class="sch-stat__value">{{ $performers->count() }}</span>
              <span class="sch-stat__label">Top performers</span>
            </div>
            <div class="sch-stat">
              <span class="sch-stat__value">{{ $achievements->count() }}</span>
              <span class="sch-stat__label">Achievements</span>
            </div>
            <div class="sch-stat">
              <span class="sch-stat__value">{{ $books->count() }}</span>
              <span class="sch-stat__label">Books listed</span>
            </div>
          </div>
          @if($institute->website_url)
            <p class="mb-0 mt-3">
              <a href="{{ $institute->website_url }}" target="_blank" rel="noopener" class="sch-link">
                <i class="fa-solid fa-globe"></i> Visit official website
              </a>
            </p>
          @endif
        </section>

        @if(filled($aboutText))
          <section id="sch-about" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> About Our School</h2>
            <div class="sch-about-text js-sch-about-text {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{!! nl2br(e($aboutText)) !!}</div>
            @if($aboutNeedsToggle)
              <button type="button" class="sch-read-more js-sch-read-more">
                Read More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
              </button>
            @endif
          </section>
        @endif

        @if($classes->isNotEmpty() || $grades->isNotEmpty())
          <section id="sch-classes" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-chalkboard" aria-hidden="true"></i> Classes</h2>
            @if($classes->isNotEmpty())
              <div class="sch-class-grid">
                @foreach($classes as $class)
                  <article class="sch-class-card">
                    <div class="sch-class-card__head">
                      <h3 class="sch-class-card__title">{{ $class->displayLabel() }}</h3>
                      @if($class->strength)
                        <span class="sch-class-card__badge">{{ $class->strength }} students</span>
                      @endif
                    </div>
                    <ul class="sch-class-card__meta list-unstyled mb-0">
                      @if($class->class_teacher)
                        <li><i class="fa-solid fa-user-tie"></i> {{ $class->class_teacher }}</li>
                      @endif
                      @if($class->room)
                        <li><i class="fa-solid fa-door-open"></i> Room {{ $class->room }}</li>
                      @endif
                    </ul>
                    @if($class->description)
                      <p class="sch-class-card__desc mb-0">{{ $class->description }}</p>
                    @endif
                  </article>
                @endforeach
              </div>
            @elseif($grades->isNotEmpty())
              <div class="sch-chip-list">
                @foreach($grades as $grade)
                  <span class="sch-chip">{{ $grade }}</span>
                @endforeach
              </div>
            @endif
          </section>
        @endif

        @if($performers->isNotEmpty())
          <section id="sch-performers" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-medal" aria-hidden="true"></i> Top Performers</h2>
            <div class="sch-performer-grid">
              @foreach($performers as $performer)
                <article class="sch-performer-card">
                  <div class="sch-performer-card__photo-wrap">
                    @if($performer->photoUrl())
                      <img src="{{ $performer->photoUrl() }}" alt="{{ $performer->student_name }}" class="sch-performer-card__photo">
                    @else
                      <span class="sch-performer-card__photo sch-performer-card__photo--placeholder"><i class="fa-solid fa-user-graduate"></i></span>
                    @endif
                    @if($performer->rank)
                      <span class="sch-performer-card__rank">#{{ $performer->rank }}</span>
                    @endif
                  </div>
                  <div class="sch-performer-card__body">
                    <h3 class="sch-performer-card__name">{{ $performer->student_name }}</h3>
                    @if($performer->class_name)
                      <p class="sch-performer-card__class mb-1">{{ $performer->class_name }}</p>
                    @endif
                    <p class="sch-performer-card__achievement mb-1">{{ $performer->achievement_title }}</p>
                    @if($performer->score)
                      <p class="sch-performer-card__score mb-0">{{ $performer->score }}</p>
                    @endif
                    @if($performer->academic_year)
                      <p class="sch-performer-card__year mb-0">{{ $performer->academic_year }}</p>
                    @endif
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endif

        @if($achievements->isNotEmpty())
          <section id="sch-achievements" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-trophy" aria-hidden="true"></i> Achievements</h2>
            <div class="sch-achievement-grid">
              @foreach($achievements as $achievement)
                <article class="sch-achievement-card">
                  @if($achievement->imageUrl())
                    <img src="{{ $achievement->imageUrl() }}" alt="" class="sch-achievement-card__image">
                  @else
                    <div class="sch-achievement-card__image sch-achievement-card__image--placeholder">
                      <i class="fa-solid fa-trophy"></i>
                    </div>
                  @endif
                  <div class="sch-achievement-card__body">
                    <div class="sch-achievement-card__meta">
                      @if($achievement->category)<span>{{ $achievement->category }}</span>@endif
                      @if($achievement->year)<span>{{ $achievement->year }}</span>@endif
                    </div>
                    <h3 class="sch-achievement-card__title">{{ $achievement->title }}</h3>
                    @if($achievement->description)
                      <p class="sch-achievement-card__desc mb-0">{{ $achievement->description }}</p>
                    @endif
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endif

        @if($books->isNotEmpty())
          <section id="sch-books" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-book" aria-hidden="true"></i> Books &amp; Authors</h2>
            <div class="sch-book-grid">
              @foreach($books as $book)
                <article class="sch-book-card">
                  @if($book->coverUrl())
                    <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}" class="sch-book-card__cover">
                  @else
                    <div class="sch-book-card__cover sch-book-card__cover--placeholder"><i class="fa-solid fa-book"></i></div>
                  @endif
                  <div class="sch-book-card__body">
                    <h3 class="sch-book-card__title">{{ $book->title }}</h3>
                    <p class="sch-book-card__author mb-1">By {{ $book->author }}</p>
                    <div class="sch-book-card__tags">
                      @if($book->class_name)<span>{{ $book->class_name }}</span>@endif
                      @if($book->subject)<span>{{ $book->subject }}</span>@endif
                    </div>
                    @if($book->publisher)
                      <p class="sch-book-card__publisher mb-0">{{ $book->publisher }}</p>
                    @endif
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endif

        @if($facilities->isNotEmpty())
          <section id="sch-facilities" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-building" aria-hidden="true"></i> Facilities</h2>
            <div class="sch-facility-grid">
              @foreach($facilities as $facility)
                <div class="sch-facility-item">
                  <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                  <span>{{ $facility }}</span>
                </div>
              @endforeach
            </div>
          </section>
        @endif

        @if($gallery->count() > 1)
          <section id="sch-gallery" class="sch-section">
            <h2 class="sch-section__title"><i class="fa-solid fa-images" aria-hidden="true"></i> Gallery</h2>
            <div class="sch-gallery-grid">
              @foreach($gallery as $imageUrl)
                <a href="{{ $imageUrl }}" class="sch-gallery-item" target="_blank" rel="noopener">
                  <img src="{{ $imageUrl }}" alt="School gallery image">
                </a>
              @endforeach
            </div>
          </section>
        @endif

        <section id="sch-contact" class="sch-section">
          <h2 class="sch-section__title"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Contact &amp; Enquiry</h2>
          <div class="row g-4">
            <div class="col-md-5">
              <ul class="sch-contact-list list-unstyled mb-0">
                @if($institute->phone)
                  <li><i class="fa-solid fa-phone"></i> <a href="tel:{{ $institute->phone }}">{{ $institute->phone }}</a></li>
                @endif
                @if($institute->whatsapp)
                  <li><i class="fa-brands fa-whatsapp"></i> <a href="https://wa.me/{{ preg_replace('/\D/', '', $institute->whatsapp) }}" target="_blank" rel="noopener">{{ $institute->whatsapp }}</a></li>
                @endif
                @if($institute->email)
                  <li><i class="fa-solid fa-envelope"></i> <a href="mailto:{{ $institute->email }}">{{ $institute->email }}</a></li>
                @endif
                @if($institute->contact_person)
                  <li><i class="fa-solid fa-user"></i> {{ $institute->contact_person }}</li>
                @endif
              </ul>
              @if($institute->facebook_url || $institute->instagram_url || $institute->youtube_url)
                <div class="sch-socials mt-3">
                  @if($institute->facebook_url)<a href="{{ $institute->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>@endif
                  @if($institute->instagram_url)<a href="{{ $institute->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>@endif
                  @if($institute->youtube_url)<a href="{{ $institute->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>@endif
                </div>
              @endif
            </div>
            <div class="col-md-7">
              @guest
                <p class="text-secondary mb-0">Please <a href="{{ route('login') }}">login</a> to send an enquiry to this school.</p>
              @else
                <form id="instituteEnquiryForm" class="sch-enquiry-form">
                  @csrf
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label" for="enquiry_name">Your name</label>
                      <input type="text" class="form-control" id="enquiry_name" name="name" value="{{ $authUser->name }}" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="enquiry_email">Email</label>
                      <input type="email" class="form-control" id="enquiry_email" name="email" value="{{ $authUser->email }}">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="enquiry_phone">Phone</label>
                      <input type="text" class="form-control" id="enquiry_phone" name="phone" value="{{ $authUser->phone_number }}">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="enquiry_subject">Subject</label>
                      <input type="text" class="form-control" id="enquiry_subject" name="subject" placeholder="Admission enquiry">
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="enquiry_message">Message</label>
                      <textarea class="form-control" id="enquiry_message" name="message" rows="4" required placeholder="Tell us about your enquiry..."></textarea>
                    </div>
                    <div class="col-12">
                      <div id="instituteEnquiryFeedback" class="alert d-none" role="alert"></div>
                      <button type="submit" class="sch-btn sch-btn-primary js-institute-enquiry-submit">
                        <span class="js-enquiry-btn-text">Send enquiry</span>
                        <span class="js-enquiry-btn-sending d-none">Sending...</span>
                      </button>
                    </div>
                  </div>
                </form>
              @endguest
            </div>
          </div>
        </section>
      </main>

      <aside class="sch-sidebar">
        <div class="sch-sidebar-card">
          <img src="{{ $photo }}" alt="" class="sch-sidebar-card__logo">
          <h3 class="sch-sidebar-card__title">{{ $institute->displayName() }}</h3>
          <p class="sch-sidebar-card__location mb-3"><i class="fa-solid fa-location-dot"></i> {{ $institute->locationLabel() }}</p>
          @if($institute->phone)
            <a href="tel:{{ $institute->phone }}" class="sch-btn sch-btn-primary w-100 mb-2"><i class="fa-solid fa-phone"></i> Call school</a>
          @endif
          <a href="#sch-contact" class="sch-btn sch-btn-outline w-100 js-sch-nav-link">Send enquiry</a>
        </div>

        @if($notices->isNotEmpty())
          <div class="sch-sidebar-card">
            <h4 class="sch-sidebar-card__heading"><i class="fa-solid fa-bullhorn"></i> Latest notice</h4>
            @php $latestNotice = $notices->first(); @endphp
            <p class="sch-sidebar-card__notice-title mb-1"><strong>{{ $latestNotice->displayTitle() }}</strong></p>
            <p class="sch-sidebar-card__notice-text mb-0">{{ $latestNotice->excerpt(120) }}</p>
          </div>
        @endif

        @if($performers->isNotEmpty())
          <div class="sch-sidebar-card">
            <h4 class="sch-sidebar-card__heading"><i class="fa-solid fa-medal"></i> Star student</h4>
            @php $star = $performers->first(); @endphp
            <p class="mb-1"><strong>{{ $star->student_name }}</strong></p>
            <p class="text-secondary mb-0 small">{{ $star->achievement_title }}</p>
          </div>
        @endif
      </aside>
    </div>
  </div>
</div>

<div class="modal fade" id="schNoticeModal" tabindex="-1" aria-labelledby="schNoticeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="schNoticeModalLabel">Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-secondary small mb-2" id="schNoticeModalExpiry"></p>
        <div id="schNoticeModalBody"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/institute-profile.js') }}?v={{ now()->timestamp }}"></script>
@endpush
