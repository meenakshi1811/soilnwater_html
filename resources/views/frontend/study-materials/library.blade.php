@extends('frontend.layouts.app')
@section('meta_title', 'Study Material Library | SoilnWater')
@section('meta_description', 'Browse free notes, worksheets, question papers and study guides from verified teachers and tutors.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/study-materials.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="sm-page">
  <section class="sm-hero">
    <div class="container">
      <h1>Study Material Library</h1>
      <p>Discover notes, papers, worksheets and guides shared by teachers and tutors across boards and classes.</p>
      <div class="mt-3 d-flex gap-2 flex-wrap">
        <a href="{{ route('study-materials.notes') }}" class="sm-btn sm-btn-primary"><i class="fa-solid fa-note-sticky"></i> All notes</a>
      </div>
    </div>
  </section>

  <div class="container sm-layout">
    <aside class="sm-sidebar">
      <h3>Filters</h3>
      <form method="GET" action="{{ route('study-materials.library') }}">
        <div class="sm-filter-group">
          <label>Search</label>
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Search materials">
        </div>
        <div class="sm-filter-group">
          <label>Category</label>
          <input type="text" name="category" class="form-control" value="{{ $filters['category'] }}">
        </div>
        <div class="sm-filter-group">
          <label>Type</label>
          <input type="text" name="material_type" class="form-control" value="{{ $filters['material_type'] }}">
        </div>
        <div class="sm-filter-group">
          <label>Subject</label>
          <input type="text" name="subject" class="form-control" value="{{ $filters['subject'] }}">
        </div>
        <div class="sm-filter-group">
          <label>Class / Course</label>
          <input type="text" name="class_course" class="form-control" value="{{ $filters['class_course'] }}">
        </div>
        <button class="sm-btn sm-btn-primary w-100" type="submit">Apply filters</button>
      </form>

      <h3 class="mt-4">Categories</h3>
      <div>
        @forelse($categories as $category)
          <a class="sm-chip {{ ($filters['category'] ?? '') === $category->category ? 'is-active' : '' }}" href="{{ route('study-materials.library', ['category' => $category->category]) }}">{{ $category->category }} ({{ $category->total }})</a>
        @empty
          <p class="sm-empty mb-0">No categories yet.</p>
        @endforelse
      </div>

      <h3 class="mt-4">Material types</h3>
      <div>
        @forelse($materialTypes as $type)
          <a class="sm-chip {{ ($filters['material_type'] ?? '') === $type->material_type ? 'is-active' : '' }}" href="{{ route('study-materials.library', ['material_type' => $type->material_type]) }}">{{ ucwords(str_replace('_',' ', $type->material_type)) }} ({{ $type->total }})</a>
        @empty
          <p class="sm-empty mb-0">No types yet.</p>
        @endforelse
      </div>
    </aside>

    <div>
      <section class="sm-panel">
        <h3 class="sm-section-title"><i class="fa-solid fa-fire text-danger me-1"></i> Trending</h3>
        <div class="sm-grid">
          @forelse($trending as $item)
            <a href="{{ $item->publicUrl() }}" class="sm-card">
              <img class="sm-card__thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
              <div class="sm-card__body">
                <h4>{{ $item->title }}</h4>
                <div class="sm-card__meta">{{ $item->subject ?: $item->materialTypeLabel() }} · {{ number_format($item->downloads_count) }} downloads</div>
              </div>
            </a>
          @empty
            <p class="sm-empty">No trending materials yet.</p>
          @endforelse
        </div>
      </section>

      <section class="sm-panel">
        <h3 class="sm-section-title"><i class="fa-solid fa-clock-rotate-left me-1"></i> Recently added</h3>
        @forelse($recent as $item)
          <a href="{{ $item->publicUrl() }}" class="sm-list-item">
            <img class="sm-list-thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
            <div>
              <strong>{{ $item->title }}</strong>
              <div class="sm-card__meta">{{ $item->educator?->display_name }} · {{ $item->created_at?->diffForHumans() }}</div>
            </div>
            <span class="sm-chip">{{ $item->materialTypeLabel() }}</span>
          </a>
        @empty
          <p class="sm-empty mb-0">No materials published yet.</p>
        @endforelse
      </section>

      <section class="sm-panel">
        <h3 class="sm-section-title">Browse results</h3>
        <div class="sm-grid">
          @forelse($materials as $item)
            <a href="{{ $item->publicUrl() }}" class="sm-card">
              <img class="sm-card__thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
              <div class="sm-card__body">
                <h4>{{ $item->title }}</h4>
                <div class="sm-card__meta">{{ $item->class_course ?: 'All classes' }} · {{ $item->subject ?: 'General' }}</div>
              </div>
            </a>
          @empty
            <p class="sm-empty">No materials match your filters.</p>
          @endforelse
        </div>
        <div class="mt-3">{{ $materials->links() }}</div>
      </section>

      <section class="sm-panel">
        <h3 class="sm-section-title">Top contributors</h3>
        <div class="row g-3">
          @forelse($topContributors as $row)
            <div class="col-md-6">
              <a href="{{ $row->educator?->publicUrl() }}" class="sm-list-item">
                <img class="sm-list-thumb" src="{{ $row->educator?->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
                <div>
                  <strong>{{ $row->educator?->display_name ?: 'Educator' }}</strong>
                  <div class="sm-card__meta">{{ $row->materials_count }} materials · {{ number_format($row->downloads_sum) }} downloads</div>
                </div>
              </a>
            </div>
          @empty
            <p class="sm-empty mb-0">Contributors will appear here.</p>
          @endforelse
        </div>
      </section>
    </div>
  </div>
</div>
@endsection
