{{-- A "Courses & Programs Offered" card ($index numbers the fields; the template uses __INDEX__).
     Its photo (optional) shows instead of the icon (institute-profile.js shows a picked one). --}}
@php
  $wing = $wing ?? [];
  $wingIcon = $wing['icon'] ?? 'fa-book-open';
  // (a school's examples, or an institute's)
  $wingForSchool = $institute->user?->isSchool() ?? true;
  $wingImage = filled($wing['image'] ?? null) ? (string) $wing['image'] : '';
  $wingImageUrl = $wingImage !== '' ? asset($wingImage) : null;
@endphp
<div class="inst-wing-row js-wing-row">
  <div class="row g-2">
    <div class="col-12">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_title">Title *</label>
      <input type="text" class="form-control form-control-sm" id="course_wing_{{ $index }}_title" name="course_wings[{{ $index }}][title]" value="{{ $wing['title'] ?? '' }}" maxlength="80" placeholder="{{ $wingForSchool ? 'e.g. Primary Wing' : 'e.g. JEE Foundation' }}">
    </div>
    <div class="col-6">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_grades">Classes</label>
      <input type="text" class="form-control form-control-sm" id="course_wing_{{ $index }}_grades" name="course_wings[{{ $index }}][grades]" value="{{ $wing['grades'] ?? '' }}" maxlength="80" placeholder="{{ $wingForSchool ? 'e.g. Nursery to Class V' : 'e.g. Class 9 to 10' }}">
    </div>
    <div class="col-6">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_icon">Icon</label>
      <select class="form-select form-select-sm" id="course_wing_{{ $index }}_icon" name="course_wings[{{ $index }}][icon]">
        @foreach(\App\Support\InstituteCourseWings::ICONS as $iconClass => $iconName)
          <option value="{{ $iconClass }}" @selected($wingIcon === $iconClass)>{{ $iconName }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-12">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_description">Description</label>
      <textarea class="form-control form-control-sm" id="course_wing_{{ $index }}_description" name="course_wings[{{ $index }}][description]" rows="2" maxlength="300" placeholder="What students learn here">{{ $wing['description'] ?? '' }}</textarea>
    </div>
    <div class="col-12">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_image">Photo <span class="text-secondary">(instead of the icon)</span></label>
      <div class="inst-wing-row__photo">
        <span class="inst-wing-row__thumb js-wing-thumb">
          <img src="{{ $wingImageUrl }}" alt="" class="{{ $wingImageUrl ? '' : 'd-none' }}">
          <i class="fa-regular fa-image {{ $wingImageUrl ? 'd-none' : '' }}" aria-hidden="true"></i>
        </span>
        <div class="inst-wing-row__photo-fields">
          <input type="file" class="form-control form-control-sm js-wing-image" id="course_wing_{{ $index }}_image" name="course_wing_images[{{ $index }}]" accept="image/jpeg,image/png,image/webp">
          <button type="button" class="btn btn-sm btn-link text-danger px-0 js-wing-image-clear {{ $wingImageUrl ? '' : 'd-none' }}">Remove photo</button>
        </div>
      </div>
      <input type="hidden" class="js-wing-image-existing" name="course_wings[{{ $index }}][image]" value="{{ $wingImage }}">
    </div>
  </div>
  <button type="button" class="btn btn-sm btn-link text-danger px-0 mt-1 js-wing-remove">Remove</button>
</div>
