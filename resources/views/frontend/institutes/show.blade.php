@extends('frontend.institutes.layout')

@section('title', $institute->displayName().' – '.(($ownerRole ?? 'school') === 'school' ? 'School' : 'Institute'))

@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($institute->about ?: $institute->tagline ?: $institute->description ?: 'School profile on SoilnWater'), 160))

@php
    $photo = $institute->logoUrl() ?: asset('assets/images/logo_soilnwater.webp');
    $bannerSlides = collect($institute->galleryUrls());
    if ($bannerSlides->isEmpty()) {
        $bannerSlides = collect([$photo]);
    }
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
    $establishedYear = $institute->establishedYear();
    $entityLabel = ($ownerRole ?? 'school') === 'school' ? 'School' : 'Institute';
@endphp

@section('institute_content')
<section class="vendor-store-hero">
    <div id="schoolHeroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
        <div class="carousel-inner h-100">
            @foreach($bannerSlides as $i => $slideUrl)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <img src="{{ $slideUrl }}" alt="{{ $institute->displayName() }} banner {{ $i + 1 }}" class="vendor-store-hero__image">
                </div>
            @endforeach
        </div>
        @if($bannerSlides->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#schoolHeroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#schoolHeroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
</section>

<section class="vendor-hero-text-section">
    <div class="container">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div class="flex-grow-1">
                <div class="school-hero-badges mb-2">
                    <span class="school-hero-badge">{{ $institute->institutionTypeLabel() }}</span>
                    @if($institute->is_verified)
                        <span class="school-hero-badge school-hero-badge--verified"><i class="fa-solid fa-circle-check"></i> Verified</span>
                    @endif
                    @if($institute->board_affiliation)
                        <span class="school-hero-badge school-hero-badge--muted">{{ $institute->board_affiliation }}</span>
                    @endif
                </div>
                <h1>{{ $institute->displayName() }}</h1>
                @if($institute->tagline)
                    <div class="lead mb-2 opacity-90 vendor-hero-subheading">{{ $institute->tagline }}</div>
                @endif
                @if($institute->formattedAddress())
                    <p class="mb-0 text-secondary">
                        <i class="fa-solid fa-location-dot me-1"></i>
                        {{ $institute->formattedAddress() }}
                    </p>
                @endif
            </div>
            @if($institute->phone)
                <a href="tel:{{ $institute->phone }}" class="btn btn-store-primary align-self-center">
                    <i class="fa-solid fa-phone me-1"></i> Call {{ $entityLabel }}
                </a>
            @endif
        </div>
    </div>
</section>

@if($notices->isNotEmpty())
    <section class="vendor-store-section school-notice-section" id="sch-notices" aria-label="Notice Board">
        <div class="container">
            <div class="sch-notice-section__frame">
                @include('frontend.institutes.partials.notice-board', ['notices' => $notices, 'featured' => true])
            </div>
        </div>
    </section>
@endif

<section id="sch-overview" class="vendor-store-section alt">
    <div class="container">
        <p class="vendor-store-eyebrow mb-1">Overview</p>
        <h2 class="vendor-store-section-title mb-4">{{ $entityLabel }} at a glance</h2>
        <div class="row g-3 g-md-4 school-stat-grid">
            @if($establishedYear)
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="school-stat-card">
                        <span class="school-stat-card__value">{{ $establishedYear }}</span>
                        <span class="school-stat-card__label">Established</span>
                    </div>
                </div>
            @endif
            @if($institute->board_affiliation)
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="school-stat-card">
                        <span class="school-stat-card__value">{{ $institute->board_affiliation }}</span>
                        <span class="school-stat-card__label">Board</span>
                    </div>
                </div>
            @endif
            <div class="col-6 col-md-4 col-xl-2">
                <div class="school-stat-card">
                    <span class="school-stat-card__value">{{ $classes->count() ?: $grades->count() ?: '—' }}</span>
                    <span class="school-stat-card__label">{{ $classes->isNotEmpty() ? 'Classes listed' : 'Grades offered' }}</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="school-stat-card">
                    <span class="school-stat-card__value">{{ $performers->count() }}</span>
                    <span class="school-stat-card__label">Top performers</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="school-stat-card">
                    <span class="school-stat-card__value">{{ $achievements->count() }}</span>
                    <span class="school-stat-card__label">Achievements</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="school-stat-card">
                    <span class="school-stat-card__value">{{ $books->count() }}</span>
                    <span class="school-stat-card__label">Books listed</span>
                </div>
            </div>
        </div>
        @if($institute->website_url)
            <div class="mt-4">
                <a href="{{ $institute->website_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="fa-solid fa-globe me-1"></i> Visit official website
                </a>
            </div>
        @endif
    </div>
</section>

@if(filled($aboutText))
    <section id="sch-about" class="vendor-store-section">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">About us</p>
            <h2 class="vendor-store-section-title mb-4">About our {{ strtolower($entityLabel) }}</h2>
            <div class="content-body">
                <div class="school-about-text js-sch-about-text {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{!! nl2br(e($aboutText)) !!}</div>
                @if($aboutNeedsToggle)
                    <button type="button" class="btn btn-link px-0 js-sch-read-more">
                        Read More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
        </div>
    </section>
@endif

@if($classes->isNotEmpty() || $grades->isNotEmpty())
    <section id="sch-classes" class="vendor-store-section alt">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">Academics</p>
            <h2 class="vendor-store-section-title mb-4">Classes</h2>
            @if($classes->isNotEmpty())
                <div class="row g-4">
                    @foreach($classes as $class)
                        <div class="col-md-6 col-xl-4">
                            <article class="vendor-store-professional-card h-100 school-class-card">
                                <div class="vendor-store-professional-card__header">
                                    <h3>{{ $class->displayLabel() }}</h3>
                                    @if($class->strength)
                                        <span>{{ $class->strength }} students</span>
                                    @endif
                                </div>
                                <div class="school-class-card__meta">
                                    @if($class->class_teacher)
                                        <p class="mb-1"><i class="fa-solid fa-user-tie me-1"></i> {{ $class->class_teacher }}</p>
                                    @endif
                                    @if($class->room)
                                        <p class="mb-1"><i class="fa-solid fa-door-open me-1"></i> Room {{ $class->room }}</p>
                                    @endif
                                    @if($class->description)
                                        <p class="mb-0 text-secondary">{{ $class->description }}</p>
                                    @endif
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @elseif($grades->isNotEmpty())
                <div class="school-chip-list">
                    @foreach($grades as $grade)
                        <span class="school-chip">{{ $grade }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif

@if($performers->isNotEmpty())
    <section id="sch-performers" class="vendor-store-section">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">Excellence</p>
            <h2 class="vendor-store-section-title mb-4">Top performers</h2>
            <div class="row g-4">
                @foreach($performers as $performer)
                    <div class="col-md-6 col-lg-4">
                        <article class="school-performer-card h-100">
                            <div class="school-performer-card__photo-wrap">
                                @if($performer->photoUrl())
                                    <img src="{{ $performer->photoUrl() }}" alt="{{ $performer->student_name }}" class="school-performer-card__photo">
                                @else
                                    <span class="school-performer-card__photo school-performer-card__photo--placeholder"><i class="fa-solid fa-user-graduate"></i></span>
                                @endif
                                @if($performer->rank)
                                    <span class="school-performer-card__rank">#{{ $performer->rank }}</span>
                                @endif
                            </div>
                            <div class="school-performer-card__body">
                                <h3>{{ $performer->student_name }}</h3>
                                @if($performer->class_name)
                                    <p class="text-secondary mb-1">{{ $performer->class_name }}</p>
                                @endif
                                <p class="fw-semibold mb-1">{{ $performer->achievement_title }}</p>
                                @if($performer->score)
                                    <p class="mb-1">{{ $performer->score }}</p>
                                @endif
                                @if($performer->academic_year)
                                    <p class="small text-secondary mb-0">{{ $performer->academic_year }}</p>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($achievements->isNotEmpty())
    <section id="sch-achievements" class="vendor-store-section alt">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">Highlights</p>
            <h2 class="vendor-store-section-title mb-4">Achievements</h2>
            <div class="row g-4">
                @foreach($achievements as $achievement)
                    <div class="col-md-6 col-lg-4">
                        <article class="school-achievement-card h-100">
                            @if($achievement->imageUrl())
                                <img src="{{ $achievement->imageUrl() }}" alt="" class="school-achievement-card__image">
                            @else
                                <div class="school-achievement-card__image school-achievement-card__image--placeholder">
                                    <i class="fa-solid fa-trophy"></i>
                                </div>
                            @endif
                            <div class="school-achievement-card__body">
                                <div class="school-achievement-card__meta">
                                    @if($achievement->category)<span>{{ $achievement->category }}</span>@endif
                                    @if($achievement->year)<span>{{ $achievement->year }}</span>@endif
                                </div>
                                <h3>{{ $achievement->title }}</h3>
                                @if($achievement->description)
                                    <p class="text-secondary mb-0">{{ $achievement->description }}</p>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($books->isNotEmpty())
    <section id="sch-books" class="vendor-store-section">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">Library</p>
            <h2 class="vendor-store-section-title mb-4">Books &amp; authors</h2>
            <div class="row g-4">
                @foreach($books as $book)
                    <div class="col-md-6 col-lg-4">
                        <article class="school-book-card h-100">
                            @if($book->coverUrl())
                                <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}" class="school-book-card__cover">
                            @else
                                <div class="school-book-card__cover school-book-card__cover--placeholder"><i class="fa-solid fa-book"></i></div>
                            @endif
                            <div class="school-book-card__body">
                                <h3>{{ $book->title }}</h3>
                                <p class="text-secondary mb-2">By {{ $book->author }}</p>
                                <div class="school-book-card__tags">
                                    @if($book->class_name)<span>{{ $book->class_name }}</span>@endif
                                    @if($book->subject)<span>{{ $book->subject }}</span>@endif
                                </div>
                                @if($book->publisher)
                                    <p class="small text-secondary mb-0 mt-2">{{ $book->publisher }}</p>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($facilities->isNotEmpty())
    <section id="sch-facilities" class="vendor-store-section alt">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">Campus</p>
            <h2 class="vendor-store-section-title mb-4">Facilities</h2>
            <div class="row g-3">
                @foreach($facilities as $facility)
                    <div class="col-md-6 col-lg-4">
                        <div class="school-facility-item">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>{{ $facility }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($gallery->count() > 1)
    <section id="sch-gallery" class="vendor-store-section">
        <div class="container">
            <p class="vendor-store-eyebrow mb-1">Gallery</p>
            <h2 class="vendor-store-section-title mb-4">Campus gallery</h2>
            <div class="row g-3">
                @foreach($gallery as $imageUrl)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ $imageUrl }}" class="school-gallery-item" target="_blank" rel="noopener" data-bs-toggle="modal" data-bs-target="#schoolGalleryModal" data-gallery-src="{{ $imageUrl }}">
                            <img src="{{ $imageUrl }}" alt="Gallery image" class="section-img">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section id="sch-contact" class="vendor-contact-page py-5 py-lg-6">
    <div class="container">
        <div class="contact-hero shadow-sm overflow-hidden mb-4 mb-lg-5">
            <div class="contact-hero__bg"></div>
            <div class="contact-hero__content p-4 p-lg-5">
                <p class="text-uppercase fw-semibold mb-2 contact-eyebrow">Let's Connect</p>
                <h2 class="display-6 fw-bold mb-3 text-white">{{ $institute->displayName() }}</h2>
                <p class="mb-0 contact-subtitle">Reach out for admissions, campus visits, and general enquiries.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="contact-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="h4 fw-bold mb-4">{{ $entityLabel }} details</h3>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <p class="mb-1 fw-semibold">Address</p>
                                <p class="mb-0 text-muted">{{ $institute->formattedAddress() ?: 'Address details are not available yet.' }}</p>
                            </div>
                        </div>
                        @if($institute->phone)
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <span class="contact-icon"><i class="fa-solid fa-phone"></i></span>
                                <div>
                                    <p class="mb-1 fw-semibold">Phone</p>
                                    <p class="mb-0"><a href="tel:{{ $institute->phone }}">{{ $institute->phone }}</a></p>
                                </div>
                            </div>
                        @endif
                        @if($institute->email)
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <span class="contact-icon"><i class="fa-solid fa-envelope"></i></span>
                                <div>
                                    <p class="mb-1 fw-semibold">Email</p>
                                    <p class="mb-0"><a href="mailto:{{ $institute->email }}">{{ $institute->email }}</a></p>
                                </div>
                            </div>
                        @endif
                        @if($institute->facebook_url || $institute->instagram_url || $institute->youtube_url)
                            <div class="school-socials mt-3">
                                @if($institute->facebook_url)<a href="{{ $institute->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>@endif
                                @if($institute->instagram_url)<a href="{{ $institute->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>@endif
                                @if($institute->youtube_url)<a href="{{ $institute->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>@endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="enquiry-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <div class="enquiry-badge mb-3">Quick Enquiry</div>
                        <h3 class="h5 fw-bold mb-2">Send your enquiry</h3>
                        <p class="text-muted mb-4">Share your admission or information request and the {{ strtolower($entityLabel) }} team will get back to you.</p>

                        @guest
                            <p class="text-secondary mb-0">Please <a href="{{ route('login') }}">login</a> to send an enquiry.</p>
                        @else
                            <form
                                id="instituteEnquiryForm"
                                method="post"
                                action="{{ route('institutes.enquiry', $institute->slug) }}"
                                novalidate
                            >
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
                                        <button type="submit" class="btn btn-store-primary js-institute-enquiry-submit">
                                            <span class="js-enquiry-btn-text">Send enquiry</span>
                                            <span class="js-enquiry-btn-sending d-none">Sending...</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<div class="modal fade" id="schoolGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Gallery preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 text-center">
                <img id="schoolGalleryModalImg" src="" alt="Gallery image" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('institute_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-gallery-src]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var img = document.getElementById('schoolGalleryModalImg');
            if (img) {
                img.src = link.getAttribute('data-gallery-src') || '';
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.vendor-contact-page{background:linear-gradient(180deg,#f8faff 0%,#f3f6ff 100%)}
.contact-hero{position:relative;border-radius:1rem;background:#1e3a5f;color:#fff}
.contact-hero__bg{position:absolute;inset:0;background:linear-gradient(135deg,#1e3a5f 0%,#2d5a87 100%);opacity:.95}
.contact-hero__content{position:relative;z-index:1}
.contact-eyebrow{letter-spacing:.08em;color:rgba(255,255,255,.75)}
.contact-subtitle{color:rgba(255,255,255,.85)}
.contact-icon{width:42px;height:42px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:#eef4ff;color:#2d5a87;flex-shrink:0}
.enquiry-badge{display:inline-block;padding:.35rem .75rem;border-radius:999px;background:#fff3ea;color:#c45a00;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
</style>
@endpush
