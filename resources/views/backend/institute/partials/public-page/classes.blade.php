@php
  $inlineEditor = $inlineEditor ?? false;
  $classesFormOpen = $institute->schoolClasses->isNotEmpty();
@endphp
<section id="inst-section-classes" class="chart-card sch-portal-section inst-profile-section mb-4">
  <header class="inst-profile-section__head sch-portal-section__head">
    <span class="sch-portal-section__icon sch-portal-section__icon--blue"><i class="fa-solid fa-chalkboard"></i></span>
    <div class="flex-grow-1">
      <h3 class="mb-1">Classes</h3>
      <p class="text-secondary mb-0">List your classes with teacher, strength, and room details.</p>
    </div>
    @unless($inlineEditor)
    <div class="form-check form-switch inst-profile-section__switch mb-0">
      <input class="form-check-input js-public-page-section-toggle" type="checkbox" role="switch" id="classes_section_enabled" data-section="classes" @checked($classesFormOpen)>
      <label class="form-check-label" for="classes_section_enabled">Show form</label>
    </div>
    @endunless
  </header>

  <div class="inst-public-page-form-body {{ ($inlineEditor || $classesFormOpen) ? '' : 'd-none' }}" data-section-body="classes">
  <form id="instClassForm" class="sch-portal-form mb-4" novalidate data-store-url="{{ $portalRoute('classes.store') }}">
    @csrf
    <div id="instClassFormAlert" class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Class name *</label>
        <input type="text" name="name" class="form-control js-section-field" data-section="classes" placeholder="Class 10">
      </div>
      <div class="col-md-2">
        <label class="form-label">Section *</label>
        <input type="text" name="section" class="form-control js-section-field" data-section="classes" placeholder="A">
      </div>
      <div class="col-md-3">
        <label class="form-label">Class teacher *</label>
        <input type="text" name="class_teacher" class="form-control js-section-field" data-section="classes" placeholder="Mrs. Sharma">
      </div>
      <div class="col-md-2">
        <label class="form-label">Strength *</label>
        <input type="number" name="strength" class="form-control js-section-field" data-section="classes" min="1" max="500" placeholder="40">
      </div>
      <div class="col-md-2">
        <label class="form-label">Room *</label>
        <input type="text" name="room" class="form-control js-section-field" data-section="classes" placeholder="201">
      </div>
      <div class="col-md-10">
        <label class="form-label">Description *</label>
        <input type="text" name="description" class="form-control js-section-field" data-section="classes" placeholder="Notes about the class">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 js-inst-submit-btn">
          <span class="btn-text">Add class</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
  </div>

  <div class="sch-manage-list" id="instClassList" data-empty-text="No classes added yet.">
    @forelse($institute->schoolClasses as $class)
      @include('backend.institute.partials.class-item', ['class' => $class])
    @empty
      <p class="sch-manage-empty mb-0" id="instClassEmpty">No classes added yet.</p>
    @endforelse
  </div>
</section>
