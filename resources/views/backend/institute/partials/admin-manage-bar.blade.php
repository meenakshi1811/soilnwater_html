{{-- Admin panel: the school being edited, and its pages --}}
@php
  $managedIsSchool = $managedInstitute->user?->isSchool();
  $managedPages = [
    'profile.edit' => ['Profile', 'fa-id-card'],
    'public-page.edit' => ['Public page', 'fa-globe'],
    'diary.index' => ['Diary', 'fa-book-bookmark'],
    'jobs.index' => ['Jobs', 'fa-briefcase'],
  ];
@endphp
<div class="chart-card mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <a href="{{ route($managedIsSchool ? 'admin.schools.index' : 'admin.institutes.index') }}" class="small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> All {{ $managedIsSchool ? 'schools' : 'institutes' }}
      </a>
      <h2 class="h5 mb-0 mt-1">Editing {{ $managedInstitute->displayName() }}</h2>
    </div>
    @if($managedInstitute->isApproved())
      <a href="{{ $managedInstitute->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i> View public page
      </a>
    @endif
  </div>
  <nav class="nav nav-pills gap-1 mt-3" aria-label="{{ $managedIsSchool ? 'School' : 'Institute' }} pages">
    @foreach($managedPages as $suffix => [$label, $icon])
      @php
        $isCurrent = request()->routeIs('admin.*.manage.'.$suffix);
      @endphp
      <a href="{{ $portalRoute($suffix) }}" class="nav-link {{ $isCurrent ? 'active' : '' }}" @if($isCurrent) aria-current="page" @endif>
        <i class="fa-solid {{ $icon }} me-1" aria-hidden="true"></i> {{ $label }}
      </a>
    @endforeach
  </nav>
</div>
