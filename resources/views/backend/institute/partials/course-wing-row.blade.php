{{-- A "Courses & Programs Offered" card ($index numbers the fields; the template uses __INDEX__) --}}
@php
  $wing = $wing ?? [];
  $wingIcon = $wing['icon'] ?? 'fa-book-open';
@endphp
<div class="inst-wing-row js-wing-row">
  <div class="row g-2">
    <div class="col-12">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_title">Title *</label>
      <input type="text" class="form-control form-control-sm" id="course_wing_{{ $index }}_title" name="course_wings[{{ $index }}][title]" value="{{ $wing['title'] ?? '' }}" maxlength="80" placeholder="e.g. Primary Wing">
    </div>
    <div class="col-6">
      <label class="form-label small mb-1" for="course_wing_{{ $index }}_grades">Classes</label>
      <input type="text" class="form-control form-control-sm" id="course_wing_{{ $index }}_grades" name="course_wings[{{ $index }}][grades]" value="{{ $wing['grades'] ?? '' }}" maxlength="80" placeholder="e.g. Nursery to Class V">
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
  </div>
  <button type="button" class="btn btn-sm btn-link text-danger px-0 mt-1 js-wing-remove">Remove</button>
</div>
