@extends('frontend.institutes.layout')

@section('title', $institute->displayName().' – '.$entityLabel.' Diary')

@section('meta_description', \Illuminate\Support\Str::limit('Holiday calendar and leave policy for '.$institute->displayName(), 160))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('institute_content')
<section class="vendor-store-section public-diary-page py-4 py-lg-5">
  <div class="container">
    <div class="public-diary-page__head mb-4">
      <p class="text-uppercase small fw-semibold text-secondary mb-1">{{ $entityLabel }} diary</p>
      <h1 class="h3 mb-2">{{ $institute->displayName() }}</h1>
      <p class="text-secondary mb-0">Official holiday calendar and leave guidelines published by this {{ strtolower($entityLabel) }}.</p>
    </div>

    <form method="get" action="{{ $diaryUrl }}" class="row g-3 align-items-end mb-4">
      <input type="hidden" name="calendar_month" value="{{ $calendarMonth }}">
      <div class="col-sm-4 col-md-3">
        <label class="form-label" for="publicDiaryYear">Academic year</label>
        <select id="publicDiaryYear" name="academic_year" class="form-select" onchange="this.form.submit()">
          @foreach($academicYears as $year)
            <option value="{{ $year }}" @selected($year === $academicYear)>{{ $year }}</option>
          @endforeach
        </select>
      </div>
    </form>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="vendor-store-card public-diary-card h-100">
          <h2 class="h5 mb-3"><i class="fa-solid fa-calendar-days me-2 text-success"></i>Holiday calendar</h2>
          @if($holidays->isEmpty())
            <p class="text-secondary mb-0">No holidays published for academic year {{ $academicYear }} yet.</p>
          @else
            <ul class="list-unstyled public-diary-holiday-list mb-0">
              @foreach($holidays as $holiday)
                <li class="public-diary-holiday-list__item">
                  <div class="d-flex flex-wrap justify-content-between gap-2">
                    <strong>{{ $holiday->name }}</strong>
                    <span class="text-secondary small">
                      {{ $holiday->start_date->format('d M Y') }}
                      @if($holiday->end_date && ! $holiday->start_date->isSameDay($holiday->end_date))
                        – {{ $holiday->end_date->format('d M Y') }}
                      @endif
                    </span>
                  </div>
                  <div class="public-diary-holiday-list__meta">
                    <span>{{ $holiday->holidayTypeLabel() }}</span>
                    @if($holiday->is_recurring)<span>Recurring</span>@endif
                  </div>
                  @if($holiday->description)
                    <p class="small text-secondary mb-0 mt-1">{{ $holiday->description }}</p>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
      <div class="col-lg-5">
        <div class="vendor-store-card public-diary-card h-100">
          <h2 class="h5 mb-3">Month view</h2>
          @include('frontend.institutes.partials.public-diary-calendar')
        </div>
      </div>
    </div>

    <div class="vendor-store-card public-diary-card mt-4">
      <h2 class="h5 mb-3"><i class="fa-solid fa-clipboard-list me-2 text-success"></i>Leave guidelines</h2>
      @if($leaveRules->isEmpty())
        <p class="text-secondary mb-0">Leave policies have not been published yet.</p>
      @else
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0 public-diary-leave-table">
            <thead class="table-light">
              <tr>
                <th>Leave type</th>
                <th>Days allowed</th>
                <th>Applicable to</th>
                <th>Paid</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leaveRules as $rule)
                <tr>
                  <td>
                    <strong>{{ $rule->leaveTypeLabel() }}</strong>
                    @if($rule->description)
                      <div class="small text-secondary">{{ $rule->description }}</div>
                    @endif
                  </td>
                  <td>{{ $rule->allowed_days }}</td>
                  <td>{{ $rule->applicableToLabels($audiences) }}</td>
                  <td>{{ $rule->is_paid ? 'Yes' : 'No' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</section>
@endsection
