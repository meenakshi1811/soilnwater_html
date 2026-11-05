@php
  $inlineEditor = $inlineEditor ?? false;
  $performersFormOpen = $institute->topPerformers->isNotEmpty();
@endphp
<section id="inst-section-performers" class="chart-card sch-portal-section inst-profile-section mb-4">
  <header class="inst-profile-section__head sch-portal-section__head">
    <span class="sch-portal-section__icon sch-portal-section__icon--gold"><i class="fa-solid fa-medal"></i></span>
    <div class="flex-grow-1">
      <h3 class="mb-1">Results</h3>
      <p class="text-secondary mb-0">Showcase toppers, board exam results, and academic highlights.</p>
    </div>
    @unless($inlineEditor)
    <div class="form-check form-switch inst-profile-section__switch mb-0">
      <input class="form-check-input js-public-page-section-toggle" type="checkbox" role="switch" id="performers_section_enabled" data-section="performers" @checked($performersFormOpen)>
      <label class="form-check-label" for="performers_section_enabled">Show form</label>
    </div>
    @endunless
  </header>

  <div class="inst-public-page-form-body {{ ($inlineEditor || $performersFormOpen) ? '' : 'd-none' }}" data-section-body="performers">
  <form id="instPerformerForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate data-store-url="{{ $portalRoute('performers.store') }}">
    @csrf
    <div id="instPerformerFormAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Student name *</label>
        <input type="text" name="student_name" class="form-control js-section-field" data-section="performers">
      </div>
      <div class="col-md-2">
        <label class="form-label">Class *</label>
        <input type="text" name="class_name" class="form-control js-section-field" data-section="performers" placeholder="Class 12">
      </div>
      <div class="col-md-3">
        <label class="form-label">Result / highlight *</label>
        <input type="text" name="achievement_title" class="form-control js-section-field" data-section="performers" placeholder="Class 10 board topper">
      </div>
      <div class="col-md-2">
        <label class="form-label">Score / marks *</label>
        <input type="text" name="score" class="form-control js-section-field" data-section="performers" placeholder="98.4%">
      </div>
      <div class="col-md-2">
        <label class="form-label">Rank *</label>
        <input type="number" name="rank" class="form-control js-section-field" data-section="performers" min="1" placeholder="1">
      </div>
      <div class="col-md-3">
        <label class="form-label">Academic year *</label>
        <input type="text" name="academic_year" class="form-control js-section-field" data-section="performers" placeholder="2025-26">
      </div>
      <div class="col-md-3">
        <label class="form-label">Photo *</label>
        <input type="file" name="photo" class="form-control js-section-field" data-section="performers" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
          <span class="btn-text">Add result</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
  </div>

  <div class="sch-manage-list" id="instPerformerList" data-empty-text="No results added yet.">
    @forelse($institute->topPerformers as $performer)
      @include('backend.institute.partials.performer-item', ['performer' => $performer])
    @empty
      <p class="sch-manage-empty mb-0" id="instPerformerEmpty">No results added yet.</p>
    @endforelse
  </div>
</section>
