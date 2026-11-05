{{-- Admin panel: the teacher / tutor being edited, and their pages --}}
@php
  $managedPages = [
    'profile.edit' => ['Profile', 'fa-id-card'],
    'tuition.edit' => ['Tuition', 'fa-indian-rupee-sign'],
  ];
@endphp
<div class="chart-card mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <a href="{{ route('admin.educators.index') }}" class="small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> All teachers / tutors
      </a>
      <h2 class="h5 mb-0 mt-1">Editing {{ $managedEducator->display_name ?: ($managedEducator->user?->name ?? 'Teacher / Tutor') }}</h2>
    </div>
    @if($managedEducator->isApproved())
      <a href="{{ $managedEducator->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i> View public page
      </a>
    @endif
  </div>
  <nav class="nav nav-pills gap-1 mt-3" aria-label="Teacher / Tutor pages">
    @foreach($managedPages as $suffix => [$label, $icon])
      @php
        $isCurrent = request()->routeIs('admin.educators.manage.'.$suffix);
      @endphp
      <a href="{{ $educatorRoute($suffix) }}" class="nav-link {{ $isCurrent ? 'active' : '' }}" @if($isCurrent) aria-current="page" @endif>
        <i class="fa-solid {{ $icon }} me-1" aria-hidden="true"></i> {{ $label }}
      </a>
    @endforeach
  </nav>
</div>
