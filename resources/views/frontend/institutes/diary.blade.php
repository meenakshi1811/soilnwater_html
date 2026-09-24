@extends('frontend.layouts.app')

@section('meta_title', $institute->displayName().' · '.$entityLabel.' Diary | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit('Holiday calendar and leave guidelines for '.$institute->displayName(), 160))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/school-profile-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  $listingIndexRoute = $listingContext === 'schools' ? 'schools.index' : 'institutes.index';
  $listingLabel = $listingContext === 'schools' ? 'Schools' : 'Institutes';
  $profileUrl = $institute->publicUrl();
  $holidayCount = $holidays->count();
  $leaveCount = $leaveRules->count();
@endphp

<div class="sch-page sch-diary-page" id="schoolDiaryPage">
  <div class="container-fluid sch-container">
    <nav class="sch-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route($listingIndexRoute) }}">{{ $listingLabel }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ $profileUrl }}">{{ $institute->displayName() }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="sch-breadcrumb__current" aria-current="page">Diary</span>
    </nav>

    <div class="sch-grid {{ $navItems === [] ? 'sch-grid--no-nav' : '' }}">
      @if($navItems !== [])
        @include('frontend.institutes.partials.school-profile.nav', [
          'navItems' => $navItems,
          'institute' => $institute,
          'activeNavId' => $activeNavId ?? 'sch-diary',
        ])
      @endif

      <div class="sch-body">
        <header class="sch-card sch-diary-hero">
          <div class="sch-diary-hero__main">
            @if($institute->logoUrl())
              <img src="{{ $institute->logoUrl() }}" alt="" class="sch-diary-hero__logo" width="64" height="64">
            @endif
            <div>
              <p class="sch-diary-hero__kicker">{{ $entityLabel }} diary</p>
              <h1 class="sch-diary-hero__title">{{ $institute->displayName() }}</h1>
              <p class="sch-diary-hero__desc mb-0">Official holiday calendar and leave guidelines for students, parents, and staff.</p>
            </div>
          </div>
          <div class="sch-diary-hero__actions">
            <a href="{{ $profileUrl }}" class="sch-btn sch-btn-outline">
              <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile
            </a>
            @if($navItems === [])
              <a href="{{ $profileUrl }}#sch-contact" class="sch-btn sch-btn-primary">Enquire Now</a>
            @endif
          </div>
        </header>

        <div class="sch-diary-stats">
          <article class="sch-diary-stat sch-diary-stat--blue">
            <span class="sch-diary-stat__icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>
            <div>
              <strong>{{ $holidayCount }}</strong>
              <span>Holidays (AY {{ $academicYear }})</span>
            </div>
          </article>
          <article class="sch-diary-stat sch-diary-stat--green">
            <span class="sch-diary-stat__icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span>
            <div>
              <strong>{{ $leaveCount }}</strong>
              <span>Leave policies</span>
            </div>
          </article>
          <article class="sch-diary-stat sch-diary-stat--gold">
            <span class="sch-diary-stat__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span>
            <div>
              <strong>{{ $academicYear }}</strong>
              <span>Academic year</span>
            </div>
          </article>
        </div>

        <div class="sch-card sch-diary-toolbar">
          <form method="get" action="{{ $diaryUrl }}" class="sch-diary-toolbar__form">
            <input type="hidden" name="calendar_month" value="{{ $calendarMonth }}">
            <label for="publicDiaryYear" class="sch-diary-toolbar__label">Academic year</label>
            <select id="publicDiaryYear" name="academic_year" class="form-select sch-diary-toolbar__select" onchange="this.form.submit()">
              @foreach($academicYears as $year)
                <option value="{{ $year }}" @selected($year === $academicYear)>{{ $year }}</option>
              @endforeach
            </select>
          </form>
        </div>

        <div class="sch-diary-layout">
          <section class="sch-card sch-diary-panel" aria-labelledby="schDiaryHolidaysTitle">
            <header class="sch-diary-panel__head">
              <h2 id="schDiaryHolidaysTitle" class="sch-section__title mb-0">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Holiday calendar
              </h2>
            </header>
            @if($holidays->isEmpty())
              <div class="sch-diary-empty">
                <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                <p>No holidays published for academic year {{ $academicYear }} yet.</p>
              </div>
            @else
              <ul class="sch-diary-holidays">
                @foreach($holidays as $holiday)
                  <li class="sch-diary-holiday">
                    <div class="sch-diary-holiday__date" aria-hidden="true">
                      <span class="sch-diary-holiday__day">{{ $holiday->start_date->format('d') }}</span>
                      <span class="sch-diary-holiday__mon">{{ $holiday->start_date->format('M') }}</span>
                    </div>
                    <div class="sch-diary-holiday__body">
                      <div class="sch-diary-holiday__top">
                        <h3 class="sch-diary-holiday__name">{{ $holiday->name }}</h3>
                        <span class="sch-diary-holiday__range">
                          {{ $holiday->start_date->format('d M Y') }}
                          @if($holiday->end_date && ! $holiday->start_date->isSameDay($holiday->end_date))
                            – {{ $holiday->end_date->format('d M Y') }}
                          @endif
                        </span>
                      </div>
                      <div class="sch-diary-holiday__tags">
                        <span class="sch-diary-tag">{{ $holiday->holidayTypeLabel() }}</span>
                        @if($holiday->is_recurring)<span class="sch-diary-tag sch-diary-tag--soft">Recurring</span>@endif
                        <span class="sch-diary-tag sch-diary-tag--muted">{{ $holiday->durationDays() }} day{{ $holiday->durationDays() === 1 ? '' : 's' }}</span>
                      </div>
                      @if($holiday->description)
                        <p class="sch-diary-holiday__note">{{ $holiday->description }}</p>
                      @endif
                    </div>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>

          <aside class="sch-card sch-diary-panel sch-diary-panel--calendar" aria-labelledby="schDiaryMonthTitle">
            <header class="sch-diary-panel__head">
              <h2 id="schDiaryMonthTitle" class="sch-section__title mb-0">Month view</h2>
            </header>
            @include('frontend.institutes.partials.public-diary-calendar')
          </aside>
        </div>

        <section class="sch-card sch-diary-panel" aria-labelledby="schDiaryLeaveTitle">
          <header class="sch-diary-panel__head">
            <h2 id="schDiaryLeaveTitle" class="sch-section__title mb-0">
              <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i> Leave guidelines
            </h2>
          </header>
          @if($leaveRules->isEmpty())
            <div class="sch-diary-empty">
              <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
              <p>Leave policies have not been published yet.</p>
            </div>
          @else
            <div class="sch-diary-leave-cards d-lg-none">
              @foreach($leaveRules as $rule)
                <article class="sch-diary-leave-card">
                  <h3 class="sch-diary-leave-card__title">{{ $rule->leaveTypeLabel() }}</h3>
                  <dl class="sch-diary-leave-card__meta">
                    <div><dt>Days allowed</dt><dd>{{ $rule->allowed_days }}</dd></div>
                    <div><dt>Applicable to</dt><dd>{{ $rule->applicableToLabels($audiences) }}</dd></div>
                    <div><dt>Paid leave</dt><dd>{{ $rule->is_paid ? 'Yes' : 'No' }}</dd></div>
                  </dl>
                  @if($rule->description)
                    <p class="sch-diary-leave-card__note">{{ $rule->description }}</p>
                  @endif
                </article>
              @endforeach
            </div>
            <div class="table-responsive d-none d-lg-block">
              <table class="table sch-diary-leave-table align-middle mb-0">
                <thead>
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
                          <div class="sch-diary-leave-table__note">{{ $rule->description }}</div>
                        @endif
                      </td>
                      <td><span class="sch-diary-leave-table__days">{{ $rule->allowed_days }}</span></td>
                      <td>{{ $rule->applicableToLabels($audiences) }}</td>
                      <td>
                        @if($rule->is_paid)
                          <span class="sch-diary-pill sch-diary-pill--yes">Paid</span>
                        @else
                          <span class="sch-diary-pill sch-diary-pill--no">Unpaid</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </section>
      </div>
    </div>
  </div>
</div>

@if($profile)
  @include('frontend.institutes.partials.school-profile.modals', [
    'institute' => $institute,
    'shareUrl' => $shareUrl,
    'profile' => $profile,
    'engagement' => $engagement,
    'listingContext' => $listingContext,
  ])
@endif
@include('community.partials.toastr-assets')
@endsection

@push('scripts')
@if($profile)
<script src="{{ asset('assets/js/school-profile-notify.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/institute-enquiry-form.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/profile-section-nav.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/school-profile-page.js') }}?v={{ now()->timestamp }}" defer></script>
@endif
@endpush
