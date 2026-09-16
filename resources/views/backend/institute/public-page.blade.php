@extends('backend.layouts.app')

@section('title', 'Public Page Content')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page institute-portal institute-public-page">
  <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
      <p class="ems-kicker mb-1">School public website</p>
      <h2 class="admin-title mb-1">Manage public page content</h2>
      <p class="mb-0 text-secondary">Add notices, classes, top performers, achievements, and books shown on your public school profile.</p>
    </div>
    @if($institute->isApproved())
      <a href="{{ $institute->publicUrl() }}" target="_blank" class="btn btn-outline-primary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View public page
      </a>
    @endif
  </div>

  <div id="instPublicPageAlert" class="alert d-none" role="alert"></div>

  <nav class="sch-portal-nav mb-4" aria-label="Public page sections">
    <a href="#inst-section-notices" class="sch-portal-nav__link is-active"><i class="fa-solid fa-bullhorn"></i> Notices</a>
    <a href="#inst-section-classes" class="sch-portal-nav__link"><i class="fa-solid fa-chalkboard"></i> Classes</a>
    <a href="#inst-section-performers" class="sch-portal-nav__link"><i class="fa-solid fa-medal"></i> Top performers</a>
    <a href="#inst-section-achievements" class="sch-portal-nav__link"><i class="fa-solid fa-trophy"></i> Achievements</a>
    <a href="#inst-section-books" class="sch-portal-nav__link"><i class="fa-solid fa-book"></i> Books</a>
  </nav>

  <section id="inst-section-notices" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head">
      <span class="sch-portal-section__icon"><i class="fa-solid fa-bullhorn"></i></span>
      <div>
        <h3 class="mb-1">Notice board</h3>
        <p class="text-secondary mb-0">Publish announcements for parents and students. Active notices appear on your public profile.</p>
      </div>
    </header>

    <form id="instNoticeForm" class="sch-portal-form mb-4" novalidate>
      @csrf
      <div class="row g-3">
        <div class="col-md-4">
          <label for="instNoticeTitle" class="form-label">Title <span class="text-muted">(optional)</span></label>
          <input type="text" id="instNoticeTitle" name="title" class="form-control" maxlength="120" placeholder="e.g. Annual day celebration">
        </div>
        <div class="col-md-4">
          <label for="instNoticeExpires" class="form-label">Expiry date</label>
          <input type="date" id="instNoticeExpires" name="expires_at" class="form-control" min="{{ now()->toDateString() }}" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
            <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> Publish notice</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
        <div class="col-12">
          <label for="instNoticeMessage" class="form-label">Notice message</label>
          <textarea id="instNoticeMessage" name="message" class="form-control" rows="3" maxlength="5000" required placeholder="Share exam dates, holidays, admission updates, or events."></textarea>
        </div>
      </div>
    </form>

    <div class="sch-manage-list" id="instNoticeList" data-empty-text="No notices published yet.">
      @forelse($institute->notices as $notice)
        @include('backend.institute.partials.notice-item', ['notice' => $notice])
      @empty
        <p class="sch-manage-empty mb-0" id="instNoticeEmpty">No notices published yet.</p>
      @endforelse
    </div>
  </section>

  <section id="inst-section-classes" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head">
      <span class="sch-portal-section__icon sch-portal-section__icon--blue"><i class="fa-solid fa-chalkboard"></i></span>
      <div>
        <h3 class="mb-1">Classes</h3>
        <p class="text-secondary mb-0">List your classes with teacher, strength, and room details.</p>
      </div>
    </header>

    <form id="instClassForm" class="sch-portal-form mb-4" novalidate>
      @csrf
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Class name</label>
          <input type="text" name="name" class="form-control" required placeholder="Class 10">
        </div>
        <div class="col-md-2">
          <label class="form-label">Section</label>
          <input type="text" name="section" class="form-control" placeholder="A">
        </div>
        <div class="col-md-3">
          <label class="form-label">Class teacher</label>
          <input type="text" name="class_teacher" class="form-control" placeholder="Mrs. Sharma">
        </div>
        <div class="col-md-2">
          <label class="form-label">Strength</label>
          <input type="number" name="strength" class="form-control" min="1" max="500" placeholder="40">
        </div>
        <div class="col-md-2">
          <label class="form-label">Room</label>
          <input type="text" name="room" class="form-control" placeholder="201">
        </div>
        <div class="col-md-10">
          <label class="form-label">Description</label>
          <input type="text" name="description" class="form-control" placeholder="Optional notes about the class">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
            <span class="btn-text">Add class</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>

    <div class="sch-manage-list" id="instClassList" data-empty-text="No classes added yet.">
      @forelse($institute->schoolClasses as $class)
        @include('backend.institute.partials.class-item', ['class' => $class])
      @empty
        <p class="sch-manage-empty mb-0" id="instClassEmpty">No classes added yet.</p>
      @endforelse
    </div>
  </section>

  <section id="inst-section-performers" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head">
      <span class="sch-portal-section__icon sch-portal-section__icon--gold"><i class="fa-solid fa-medal"></i></span>
      <div>
        <h3 class="mb-1">Top performers</h3>
        <p class="text-secondary mb-0">Showcase star students, toppers, and competition winners.</p>
      </div>
    </header>

    <form id="instPerformerForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Student name</label>
          <input type="text" name="student_name" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Class</label>
          <input type="text" name="class_name" class="form-control" placeholder="Class 12">
        </div>
        <div class="col-md-3">
          <label class="form-label">Achievement</label>
          <input type="text" name="achievement_title" class="form-control" required placeholder="Board exam topper">
        </div>
        <div class="col-md-2">
          <label class="form-label">Score / marks</label>
          <input type="text" name="score" class="form-control" placeholder="98.4%">
        </div>
        <div class="col-md-2">
          <label class="form-label">Rank</label>
          <input type="number" name="rank" class="form-control" min="1" placeholder="1">
        </div>
        <div class="col-md-3">
          <label class="form-label">Academic year</label>
          <input type="text" name="academic_year" class="form-control" placeholder="2025-26">
        </div>
        <div class="col-md-3">
          <label class="form-label">Photo</label>
          <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
            <span class="btn-text">Add performer</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>

    <div class="sch-manage-list" id="instPerformerList" data-empty-text="No top performers added yet.">
      @forelse($institute->topPerformers as $performer)
        @include('backend.institute.partials.performer-item', ['performer' => $performer])
      @empty
        <p class="sch-manage-empty mb-0" id="instPerformerEmpty">No top performers added yet.</p>
      @endforelse
    </div>
  </section>

  <section id="inst-section-achievements" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head">
      <span class="sch-portal-section__icon sch-portal-section__icon--green"><i class="fa-solid fa-trophy"></i></span>
      <div>
        <h3 class="mb-1">Achievements</h3>
        <p class="text-secondary mb-0">Highlight school awards, sports wins, and academic milestones.</p>
      </div>
    </header>

    <form id="instAchievementForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Category</label>
          <input type="text" name="category" class="form-control" placeholder="Sports, Academics...">
        </div>
        <div class="col-md-2">
          <label class="form-label">Year</label>
          <input type="number" name="year" class="form-control" min="1900" max="{{ now()->year + 1 }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Image</label>
          <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="col-md-9">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Brief details about the achievement"></textarea>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
            <span class="btn-text">Add achievement</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>

    <div class="sch-manage-list" id="instAchievementList" data-empty-text="No achievements added yet.">
      @forelse($institute->achievements as $achievement)
        @include('backend.institute.partials.achievement-item', ['achievement' => $achievement])
      @empty
        <p class="sch-manage-empty mb-0" id="instAchievementEmpty">No achievements added yet.</p>
      @endforelse
    </div>
  </section>

  <section id="inst-section-books" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head">
      <span class="sch-portal-section__icon sch-portal-section__icon--purple"><i class="fa-solid fa-book"></i></span>
      <div>
        <h3 class="mb-1">Books &amp; authors</h3>
        <p class="text-secondary mb-0">List prescribed textbooks and reading materials by class.</p>
      </div>
    </header>

    <form id="instBookForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Book title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Author</label>
          <input type="text" name="author" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Class</label>
          <input type="text" name="class_name" class="form-control" placeholder="Class 8">
        </div>
        <div class="col-md-3">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-control" placeholder="Mathematics">
        </div>
        <div class="col-md-3">
          <label class="form-label">Publisher</label>
          <input type="text" name="publisher" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">ISBN</label>
          <input type="text" name="isbn" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Cover image</label>
          <input type="file" name="cover_image" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
            <span class="btn-text">Add book</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>

    <div class="sch-manage-list" id="instBookList" data-empty-text="No books added yet.">
      @forelse($institute->books as $book)
        @include('backend.institute.partials.book-item', ['book' => $book])
      @empty
        <p class="sch-manage-empty mb-0" id="instBookEmpty">No books added yet.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
window.instPublicContentRoutes = @json([
    'notices' => $portalRoute('notices.store'),
    'achievements' => $portalRoute('achievements.store'),
    'performers' => $portalRoute('performers.store'),
    'classes' => $portalRoute('classes.store'),
    'books' => $portalRoute('books.store'),
]);
</script>
<script src="{{ asset('assets/js/institute-public-content.js') }}?v={{ now()->timestamp }}"></script>
@endpush
