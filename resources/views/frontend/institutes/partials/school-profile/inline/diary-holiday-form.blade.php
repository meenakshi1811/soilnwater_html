@php
  $listingContext = $listingContext ?? 'schools';
  $portalPrefix = $listingContext === 'schools' ? 'school' : 'institute';
  $types = \App\Support\InstituteDiaryConfig::holidayTypes();
  $ay = \App\Support\InstituteDiaryConfig::defaultAcademicYear();
  $holidays = $institute->diaryHolidays()->orderBy('start_date')->get();
@endphp
<section>
  <form id="instHolidayForm" class="sch-portal-form mb-4" novalidate
        data-store-url="{{ route($portalPrefix.'.diary.holidays.store') }}">
    @csrf
    <div class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Holiday name *</label>
        <input type="text" name="name" class="form-control" maxlength="160">
      </div>
      <div class="col-md-6">
        <label class="form-label">Type *</label>
        <select name="holiday_type" class="form-select">
          @foreach($types as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Start date *</label>
        <input type="date" name="start_date" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">End date *</label>
        <input type="date" name="end_date" class="form-control">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" maxlength="5000"></textarea>
      </div>
      <input type="hidden" name="academic_year" value="{{ $ay }}">
      <input type="hidden" name="is_active" value="1">
      <div class="col-12">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" name="is_recurring" value="1" id="instHolidayRecurring">
          <label class="form-check-label" for="instHolidayRecurring">Recurring every year</label>
        </div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary js-inst-submit-btn">
          <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add holiday</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
    <p class="form-text mb-0">Added for academic year {{ $ay }}.</p>
  </form>

  <div class="sch-manage-list" data-empty-text="No holidays added yet.">
    @forelse($holidays as $holiday)
      @include('backend.institute.partials.diary-holiday-item', ['holiday' => $holiday])
    @empty
      <p class="sch-manage-empty mb-0">No holidays added yet.</p>
    @endforelse
  </div>
</section>
