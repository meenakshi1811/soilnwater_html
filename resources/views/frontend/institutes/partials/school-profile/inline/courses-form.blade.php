@php
  $listingContext = $listingContext ?? 'schools';
  $wings = \App\Support\InstituteCourseWings::wings(data_get($institute->profile_meta, 'course_wings'), $listingContext === 'schools');
  $icons = \App\Support\InstituteCourseWings::ICONS;
@endphp
<section>
  <p class="text-secondary small mb-3">The cards shown in the “Courses &amp; Programs Offered” section of your public page.</p>
  <form id="instCourseForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate
        data-store-url="{{ route($listingContext.'.inline.courses.add', $institute->slug) }}">
    @csrf
    <div class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" maxlength="80" placeholder="e.g. Senior Secondary">
      </div>
      <div class="col-md-6">
        <label class="form-label">Icon</label>
        <select name="icon" class="form-select">
          @foreach($icons as $cls => $label)
            <option value="{{ $cls }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Classes / grades</label>
        <input type="text" name="grades" class="form-control" maxlength="80" placeholder="e.g. Class XI to XII">
      </div>
      <div class="col-md-6">
        <label class="form-label">Photo (optional)</label>
        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" maxlength="1000" placeholder="What this wing / program covers"></textarea>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary js-inst-submit-btn">
          <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add course</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>

  <div class="sch-manage-list" data-empty-text="No courses added yet.">
    @forelse($wings as $wing)
      @include('frontend.institutes.partials.school-profile.inline.course-wing-item', [
        'institute' => $institute,
        'listingContext' => $listingContext,
        'wing' => $wing,
      ])
    @empty
      <p class="sch-manage-empty mb-0">No courses added yet.</p>
    @endforelse
  </div>
</section>
