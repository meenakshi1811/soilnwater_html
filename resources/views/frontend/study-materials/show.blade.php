@extends('frontend.layouts.app')
@section('meta_title', $material->title.' | Study Materials')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string)$material->description), 150) ?: 'Study material on SoilnWater')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/study-materials.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php $contents = collect($material->contents ?? []); @endphp
<div class="sm-page">
  <section class="sm-hero">
    <div class="container">
      @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <span class="sm-chip">{{ $material->materialTypeLabel() }}</span>
          <h1 class="mt-2">{{ $material->title }}</h1>
          <p class="mb-0">{{ $material->subject ?: 'General' }} · {{ $material->class_course ?: 'All classes' }} · {{ $material->board_university ?: 'All boards' }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          @auth
            <a href="{{ route('study-materials.download', $material->slug) }}" class="sm-btn sm-btn-primary"><i class="fa-solid fa-download"></i> Download</a>
            <form method="POST" action="{{ route('study-materials.bookmark', $material->slug) }}">
              @csrf
              <button class="sm-btn sm-btn-outline" type="submit"><i class="fa-solid fa-bookmark"></i> {{ $isBookmarked ? 'Saved' : 'Bookmark' }}</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="sm-btn sm-btn-primary"><i class="fa-solid fa-download"></i> Login to download</a>
          @endauth
        </div>
      </div>
    </div>
  </section>

  <div class="container sm-detail">
    <aside class="sm-sidebar">
      <h3>Contents</h3>
      @forelse($contents as $index => $item)
        <div class="small py-1 border-bottom">{{ $index + 1 }}. {{ $item }}</div>
      @empty
        <p class="sm-empty mb-0">No contents outline provided.</p>
      @endforelse
    </aside>

    <div>
      <div class="sm-panel">
        <div class="sm-viewer">
          @if($material->thumbnailUrl())
            <img src="{{ $material->thumbnailUrl() }}" alt="{{ $material->title }}" style="max-width:100%;max-height:280px;border-radius:12px">
          @else
            <i class="fa-solid fa-file-lines fa-3x" style="color:var(--sm-primary)"></i>
          @endif
          <div>
            <strong>{{ $material->file_name ?: 'Study file' }}</strong>
            <div class="sm-card__meta">{{ strtoupper((string)$material->file_type) }} · {{ $material->fileSizeLabel() }} · {{ $material->pages ? $material->pages.' pages' : '—' }}</div>
          </div>
          @auth
            <a href="{{ route('study-materials.download', $material->slug) }}" class="sm-btn sm-btn-primary">Open / Download</a>
          @endauth
        </div>
      </div>

      <div class="sm-panel">
        <div class="sm-tabs" id="materialTabs">
          <button type="button" class="sm-tab is-active" data-tab="description">Description</button>
          <button type="button" class="sm-tab" data-tab="details">Details</button>
          <button type="button" class="sm-tab" data-tab="related">Related</button>
          <button type="button" class="sm-tab" data-tab="reviews">Reviews</button>
        </div>

        <div class="sm-tab-panel is-active" data-panel="description">
          <p style="white-space:pre-line">{{ $material->description ?: 'No description provided.' }}</p>
          @if(is_array($material->tags) && count($material->tags))
            <div class="mt-2">@foreach($material->tags as $tag)<span class="sm-chip">{{ $tag }}</span>@endforeach</div>
          @endif
        </div>

        <div class="sm-tab-panel" data-panel="details">
          <ul class="list-unstyled mb-0">
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Subject</span><strong>{{ $material->subject ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Topic</span><strong>{{ $material->topic_chapter ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Exam</span><strong>{{ $material->exam_test ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Language</span><strong>{{ $material->language ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Difficulty</span><strong>{{ $material->difficulty ?: '—' }}</strong></li>
            <li class="py-1 border-bottom d-flex justify-content-between"><span>Academic year</span><strong>{{ $material->academic_year ?: '—' }}</strong></li>
            <li class="py-1 d-flex justify-content-between"><span>Views / Downloads / Saves</span><strong>{{ number_format($material->views_count) }} / {{ number_format($material->downloads_count) }} / {{ number_format($material->saves_count) }}</strong></li>
          </ul>
        </div>

        <div class="sm-tab-panel" data-panel="related">
          @forelse($related as $item)
            <a href="{{ $item->publicUrl() }}" class="sm-list-item">
              <img class="sm-list-thumb" src="{{ $item->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
              <div>
                <strong>{{ $item->title }}</strong>
                <div class="sm-card__meta">{{ $item->subject ?: $item->materialTypeLabel() }}</div>
              </div>
            </a>
          @empty
            <p class="sm-empty mb-0">No related materials.</p>
          @endforelse
        </div>

        <div class="sm-tab-panel" data-panel="reviews">
          @auth
            <form method="POST" action="{{ route('study-materials.review', $material->slug) }}" class="mb-3">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <select name="rating" class="form-select" required>
                    @for($i=5;$i>=1;$i--)
                      <option value="{{ $i }}">{{ $i }} star{{ $i>1?'s':'' }}</option>
                    @endfor
                  </select>
                </div>
                <div class="col-md-7"><input type="text" name="review" class="form-control" placeholder="Share your feedback"></div>
                <div class="col-md-2"><button class="sm-btn sm-btn-primary w-100" type="submit">Post</button></div>
              </div>
            </form>
          @endauth
          @forelse($material->reviews as $review)
            <div class="border-bottom py-2">
              <strong>{{ $review->user?->name ?: 'User' }}</strong>
              <span class="text-warning ms-1">{{ $review->rating }}★</span>
              <p class="mb-0 small">{{ $review->review }}</p>
            </div>
          @empty
            <p class="sm-empty mb-0">No reviews yet.</p>
          @endforelse
        </div>
      </div>
    </div>

    <aside class="sm-sidebar">
      <h3>Material info</h3>
      <div class="mb-2">
        <span class="sm-chip">{{ $material->materialTypeLabel() }}</span>
        @if($material->is_free)<span class="sm-chip">Free</span>@endif
        @if($material->is_verified)<span class="sm-chip">Verified</span>@endif
      </div>
      <div class="small mb-3">
        <div><i class="fa-solid fa-star text-warning"></i> {{ number_format((float)$material->average_rating, 1) }} ({{ number_format($material->reviews_count) }})</div>
        <div>{{ number_format($material->views_count) }} views · {{ number_format($material->downloads_count) }} downloads</div>
      </div>
      @auth
        <a href="{{ route('study-materials.download', $material->slug) }}" class="sm-btn sm-btn-primary w-100 mb-2"><i class="fa-solid fa-download"></i> Download</a>
        <form method="POST" action="{{ route('study-materials.bookmark', $material->slug) }}" class="mb-3">
          @csrf
          <button class="sm-btn sm-btn-outline w-100" type="submit"><i class="fa-solid fa-bookmark"></i> {{ $isBookmarked ? 'Saved' : 'Save' }}</button>
        </form>
      @endauth
      <ul class="list-unstyled small mb-3">
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Class / Course</span><strong>{{ $material->class_course ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Subject</span><strong>{{ $material->subject ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Chapter / Topic</span><strong>{{ $material->topic_chapter ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Board</span><strong>{{ $material->board_university ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Medium</span><strong>{{ $material->medium ?: $material->language ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Academic Year</span><strong>{{ $material->academic_year ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>Pages</span><strong>{{ $material->pages ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>File Type</span><strong>{{ strtoupper((string)$material->file_type) ?: '—' }}</strong></li>
        <li class="py-1 border-bottom d-flex justify-content-between gap-2"><span>File Size</span><strong>{{ $material->fileSizeLabel() }}</strong></li>
        <li class="py-1 d-flex justify-content-between gap-2"><span>Upload Date</span><strong>{{ $material->created_at?->format('d M Y') ?: '—' }}</strong></li>
      </ul>
      <h3>Uploaded by</h3>
      @if($material->educator)
        <a href="{{ $material->educator->publicUrl() }}" class="sm-list-item">
          <img class="sm-list-thumb" src="{{ $material->educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
          <div>
            <strong>{{ $material->educator->display_name }}</strong>
            <div class="sm-card__meta">{{ $material->educator->professional_headline ?: $material->educator->roleLabel() }}@if($material->educator->isVerified()) · Verified@endif</div>
          </div>
        </a>
      @endif
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('#materialTabs .sm-tab').forEach(function (tab) {
  tab.addEventListener('click', function () {
    document.querySelectorAll('#materialTabs .sm-tab').forEach(t => t.classList.remove('is-active'));
    document.querySelectorAll('[data-panel]').forEach(p => p.classList.remove('is-active'));
    tab.classList.add('is-active');
    document.querySelector('[data-panel="' + tab.dataset.tab + '"]')?.classList.add('is-active');
  });
});
</script>
@endpush
