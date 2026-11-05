@php
  $inlineEditor = $inlineEditor ?? false;
  $achievementsFormOpen = $institute->achievements->isNotEmpty();
@endphp
<section id="inst-section-achievements" class="chart-card sch-portal-section inst-profile-section mb-4">
  <header class="inst-profile-section__head sch-portal-section__head">
    <span class="sch-portal-section__icon sch-portal-section__icon--green"><i class="fa-solid fa-trophy"></i></span>
    <div class="flex-grow-1">
      <h3 class="mb-1">Achievements</h3>
      <p class="text-secondary mb-0">Highlight {{ strtolower($portalLabel) }} awards, sports wins, and academic milestones.</p>
    </div>
    @unless($inlineEditor)
    <div class="form-check form-switch inst-profile-section__switch mb-0">
      <input class="form-check-input js-public-page-section-toggle" type="checkbox" role="switch" id="achievements_section_enabled" data-section="achievements" @checked($achievementsFormOpen)>
      <label class="form-check-label" for="achievements_section_enabled">Show form</label>
    </div>
    @endunless
  </header>

  <div class="inst-public-page-form-body {{ ($inlineEditor || $achievementsFormOpen) ? '' : 'd-none' }}" data-section-body="achievements">
  <form id="instAchievementForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate data-store-url="{{ $portalRoute('achievements.store') }}">
    @csrf
    <div id="instAchievementFormAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control js-section-field" data-section="achievements">
      </div>
      <div class="col-md-3">
        <label class="form-label">Category *</label>
        <input type="text" name="category" class="form-control js-section-field" data-section="achievements" placeholder="Sports, Academics...">
      </div>
      <div class="col-md-2">
        <label class="form-label">Year *</label>
        <input type="number" name="year" class="form-control js-section-field" data-section="achievements" min="1900" max="{{ now()->year + 1 }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Image *</label>
        <input type="file" name="image" class="form-control js-section-field" data-section="achievements" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="col-md-9">
        <label class="form-label">Description *</label>
        <textarea name="description" class="form-control js-section-field" data-section="achievements" rows="2" placeholder="Brief details about the achievement"></textarea>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
          <span class="btn-text">Add achievement</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
  </div>

  <div class="sch-manage-list" id="instAchievementList" data-empty-text="No achievements added yet.">
    @forelse($institute->achievements as $achievement)
      @include('backend.institute.partials.achievement-item', ['achievement' => $achievement])
    @empty
      <p class="sch-manage-empty mb-0" id="instAchievementEmpty">No achievements added yet.</p>
    @endforelse
  </div>
</section>
