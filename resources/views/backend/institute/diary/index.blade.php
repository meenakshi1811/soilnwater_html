@extends('backend.layouts.app')

@section('title', $config['moduleTitle'])

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page institute-portal institute-diary-page">
  <div class="ems-hero mb-4">
    <p class="ems-kicker mb-1">{{ $portalLabel }} administration</p>
    <h2 class="admin-title mb-1">{{ $config['moduleTitle'] }}</h2>
    <p class="mb-0 text-secondary">{{ $config['moduleDescription'] }}</p>
  </div>

  <nav class="sch-portal-nav mb-4" aria-label="Diary sections">
    <a href="#diary-section-holidays" class="sch-portal-nav__link is-active"><i class="fa-solid fa-calendar-days"></i> Holiday calendar</a>
    <a href="#diary-section-leave" class="sch-portal-nav__link"><i class="fa-solid fa-clipboard-list"></i> Leave rules</a>
  </nav>

  <section id="diary-section-holidays" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head mb-3">
      <span class="sch-portal-section__icon sch-portal-section__icon--blue"><i class="fa-solid fa-calendar-days"></i></span>
      <div>
        <h3 class="mb-1">Holiday calendar</h3>
        <p class="text-secondary mb-0">Plan closures and breaks for your {{ strtolower($portalLabel) }}. Holidays are scoped to this profile only.</p>
      </div>
    </header>

    <form method="get" action="{{ $portalRoute('diary.index') }}" class="sch-portal-form mb-4 inst-diary-filters">
      <input type="hidden" name="calendar_month" value="{{ $calendarMonth }}">
      <div class="row g-3 align-items-end">
        <div class="col-md-2">
          <label class="form-label" for="diaryFilterYear">Academic year</label>
          <input type="text" id="diaryFilterYear" name="academic_year" class="form-control" value="{{ $academicYear }}" placeholder="2025-26" required>
        </div>
        <div class="col-md-2">
          <label class="form-label" for="diaryFilterType">Type</label>
          <select id="diaryFilterType" name="holiday_type" class="form-select">
            <option value="">All types</option>
            @foreach($config['holidayTypes'] as $key => $label)
              <option value="{{ $key }}" @selected($holidayType === $key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label" for="diaryFilterStatus">Status</label>
          <select id="diaryFilterStatus" name="status" class="form-select">
            <option value="all" @selected($status === 'all')>All</option>
            <option value="active" @selected($status === 'active')>Active only</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive only</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="diaryFilterSearch">Search</label>
          <input type="search" id="diaryFilterSearch" name="q" class="form-control" value="{{ $search }}" placeholder="Holiday name or remarks">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-outline-primary w-100">Apply filters</button>
        </div>
      </div>
    </form>

    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <form id="diaryHolidayForm" class="sch-portal-form border rounded p-3 mb-4" novalidate>
          @csrf
          <h4 class="h6 mb-3">Add holiday</h4>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="diaryHolidayName">Holiday name</label>
              <input type="text" id="diaryHolidayName" name="name" class="form-control" maxlength="160" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="diaryHolidayType">Type</label>
              <select id="diaryHolidayType" name="holiday_type" class="form-select" required>
                @foreach($config['holidayTypes'] as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="diaryHolidayStart">Start date</label>
              <input type="date" id="diaryHolidayStart" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="diaryHolidayEnd">End date</label>
              <input type="date" id="diaryHolidayEnd" name="end_date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="diaryHolidayYear">Academic year</label>
              <input type="text" id="diaryHolidayYear" name="academic_year" class="form-control" value="{{ $academicYear }}" maxlength="20" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="diaryHolidayDesc">Description / remarks</label>
              <textarea id="diaryHolidayDesc" name="description" class="form-control" rows="2" maxlength="5000"></textarea>
            </div>
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="diaryHolidayRecurring" name="is_recurring" value="1">
                <label class="form-check-label" for="diaryHolidayRecurring">Recurring (same dates each year)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="diaryHolidayActive" name="is_active" value="1" checked>
                <label class="form-check-label" for="diaryHolidayActive">Active</label>
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary js-diary-submit-btn">
                <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add holiday</span>
                <span class="btn-loader d-none" aria-hidden="true"></span>
              </button>
            </div>
          </div>
        </form>

        <div class="sch-manage-list" id="diaryHolidayList" data-empty-text="No holidays for this academic year yet.">
          @forelse($holidays as $holiday)
            @include('backend.institute.partials.diary-holiday-item', ['holiday' => $holiday])
          @empty
            <p class="sch-manage-empty mb-0" id="diaryHolidayEmpty">No holidays for this academic year yet.</p>
          @endforelse
        </div>
      </div>
      <div class="col-lg-5">
        @include('backend.institute.partials.diary-calendar')
      </div>
    </div>
  </section>

  <section id="diary-section-leave" class="chart-card sch-portal-section mb-4">
    <header class="sch-portal-section__head mb-3">
      <span class="sch-portal-section__icon sch-portal-section__icon--green"><i class="fa-solid fa-clipboard-list"></i></span>
      <div>
        <h3 class="mb-1">Leave rules</h3>
        <p class="text-secondary mb-0">Define leave entitlements and who they apply to (students, teachers, staff, and other roles).</p>
      </div>
    </header>

    <form id="diaryLeaveForm" class="sch-portal-form border rounded p-3 mb-4" novalidate>
      @csrf
      <h4 class="h6 mb-3">Add leave rule</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="diaryLeaveType">Leave type</label>
          <select id="diaryLeaveType" name="leave_type" class="form-select" required>
            @foreach($config['leaveTypes'] as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="diaryLeaveDays">Allowed days (per year)</label>
          <input type="number" id="diaryLeaveDays" name="allowed_days" class="form-control" min="0" max="366" value="0" required>
        </div>
        <div class="col-md-4">
          <label class="form-label d-block">Paid leave</label>
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="diaryLeavePaid" name="is_paid" value="1" checked>
            <label class="form-check-label" for="diaryLeavePaid">Paid</label>
          </div>
        </div>
        <div class="col-12">
          <span class="form-label d-block">Applicable to</span>
          <div class="d-flex flex-wrap gap-3">
            @foreach($config['audiences'] as $key => $label)
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="applicable_to[]" value="{{ $key }}" id="diaryLeaveAudience{{ $key }}">
                <label class="form-check-label" for="diaryLeaveAudience{{ $key }}">{{ $label }}</label>
              </div>
            @endforeach
          </div>
        </div>
        <div class="col-12">
          <label class="form-label" for="diaryLeaveDesc">Policy notes</label>
          <textarea id="diaryLeaveDesc" name="description" class="form-control" rows="2" maxlength="5000" placeholder="Carry-forward rules, documentation required, etc."></textarea>
        </div>
        <div class="col-md-6">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="diaryLeaveActive" name="is_active" value="1" checked>
            <label class="form-check-label" for="diaryLeaveActive">Active</label>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary js-diary-submit-btn">
            <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add leave rule</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>

    <div class="sch-manage-list" id="diaryLeaveList" data-empty-text="No leave rules configured yet.">
      @forelse($leaveRules as $rule)
        @include('backend.institute.partials.diary-leave-rule-item', [
          'rule' => $rule,
          'audienceLabels' => $config['audiences'],
        ])
      @empty
        <p class="sch-manage-empty mb-0" id="diaryLeaveEmpty">No leave rules configured yet.</p>
      @endforelse
    </div>
  </section>
</div>

@include('backend.institute.partials.diary-edit-modals', ['config' => $config])
@endsection

@push('scripts')
@include('community.partials.toastr-assets')
@php
    $diaryRoutes = [
        'holidaysStore' => $portalRoute('diary.holidays.store'),
        'leaveStore' => $portalRoute('diary.leave-rules.store'),
    ];
@endphp
<script>
window.instDiaryRoutes = @json($diaryRoutes);
window.instDiaryAudiences = @json(array_keys($config['audiences']));
</script>
<script src="{{ asset('assets/js/institute-diary.js') }}?v={{ now()->timestamp }}"></script>
@endpush
