@extends('frontend.layouts.app')
@section('meta_title', 'All Posted Notes | SoilnWater')
@section('meta_description', 'Browse all approved study notes with filters by subject, class, board and contributor.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/study-materials.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="sm-page">
  <section class="sm-hero">
    <div class="container d-flex justify-content-between align-items-end flex-wrap gap-3">
      <div>
        <h1>All Posted Notes</h1>
        <p>Filter and explore classroom notes shared by educators.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('study-materials.notes', array_merge(request()->query(), ['view' => 'list'])) }}" class="sm-btn {{ $viewMode === 'list' ? 'sm-btn-primary' : 'sm-btn-outline' }}"><i class="fa-solid fa-list"></i> List</a>
        <a href="{{ route('study-materials.notes', array_merge(request()->query(), ['view' => 'grid'])) }}" class="sm-btn {{ $viewMode === 'grid' ? 'sm-btn-primary' : 'sm-btn-outline' }}"><i class="fa-solid fa-grid-2"></i> Grid</a>
      </div>
    </div>
  </section>

  <div class="container">
    <div class="sm-stats">
      <div class="sm-stat-card"><strong>{{ number_format($stats['total']) }}</strong><span>Total notes</span></div>
      <div class="sm-stat-card"><strong>{{ number_format($stats['subjects']) }}</strong><span>Subjects</span></div>
      <div class="sm-stat-card"><strong>{{ number_format($stats['downloads']) }}</strong><span>Downloads</span></div>
      <div class="sm-stat-card"><strong>{{ number_format($stats['contributors']) }}</strong><span>Contributors</span></div>
    </div>

    <div class="sm-layout">
      <aside class="sm-sidebar">
        <h3>Filters</h3>
        <form method="GET" action="{{ route('study-materials.notes') }}">
          <input type="hidden" name="view" value="{{ $viewMode }}">
          <div class="sm-filter-group"><label>Search</label><input type="text" name="q" class="form-control" value="{{ $filters['q'] }}"></div>
          <div class="sm-filter-group"><label>Subject</label><input type="text" name="subject" class="form-control" value="{{ $filters['subject'] }}"></div>
          <div class="sm-filter-group"><label>Class</label><input type="text" name="class_course" class="form-control" value="{{ $filters['class_course'] }}"></div>
          <div class="sm-filter-group"><label>Board</label><input type="text" name="board_university" class="form-control" value="{{ $filters['board_university'] }}"></div>
          <div class="sm-filter-group"><label>Language</label><input type="text" name="language" class="form-control" value="{{ $filters['language'] }}"></div>
          <button class="sm-btn sm-btn-primary w-100" type="submit">Apply</button>
        </form>

        <h3 class="mt-4">Popular subjects</h3>
        @forelse($popularSubjects as $subject)
          <a class="sm-chip" href="{{ route('study-materials.notes', ['subject' => $subject->subject, 'view' => $viewMode]) }}">{{ $subject->subject }} ({{ $subject->total }})</a>
        @empty
          <p class="sm-empty mb-0">No subjects yet.</p>
        @endforelse

        <h3 class="mt-4">Top contributors</h3>
        @forelse($topContributors as $row)
          <a class="sm-list-item" href="{{ $row->educator?->publicUrl() }}">
            <img class="sm-list-thumb" src="{{ $row->educator?->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
            <div>
              <strong>{{ $row->educator?->display_name }}</strong>
              <div class="sm-card__meta">{{ $row->materials_count }} notes</div>
            </div>
          </a>
        @empty
          <p class="sm-empty mb-0">No contributors yet.</p>
        @endforelse
      </aside>

      <section class="sm-panel">
        @if($viewMode === 'grid')
          <div class="sm-grid">
            @forelse($materials as $item)
              <a href="{{ $item->publicUrl() }}" class="sm-card">
                <img class="sm-card__thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
                <div class="sm-card__body">
                  <h4>{{ $item->title }}</h4>
                  <div class="sm-card__meta">{{ $item->subject ?: 'Notes' }} · {{ number_format($item->downloads_count) }} downloads</div>
                </div>
              </a>
            @empty
              <p class="sm-empty">No notes found.</p>
            @endforelse
          </div>
        @else
          @forelse($materials as $item)
            <a href="{{ $item->publicUrl() }}" class="sm-list-item">
              <img class="sm-list-thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
              <div>
                <strong>{{ $item->title }}</strong>
                <div class="sm-card__meta">
                  {{ $item->educator?->display_name }} · {{ $item->class_course ?: 'All classes' }} · {{ $item->subject ?: 'General' }}
                </div>
              </div>
              <div class="text-end small text-muted">
                <div>{{ number_format($item->downloads_count) }} downloads</div>
                <div>{{ number_format((float)$item->average_rating, 1) }} ★</div>
              </div>
            </a>
          @empty
            <p class="sm-empty mb-0">No notes found.</p>
          @endforelse
        @endif
        <div class="mt-3">{{ $materials->links() }}</div>
      </section>
    </div>
  </div>
</div>
@endsection
