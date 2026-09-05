@extends('backend.layouts.app')
@section('title', 'View Study Material')

@section('content')
@php
  $status = $material->status ?? 'pending';
  $contents = collect($material->contents ?? []);
  $tags = collect($material->tags ?? []);
@endphp
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
      <p class="ems-kicker mb-1">Educator Portal</p>
      <h2 class="admin-title mb-1">{{ $material->title }}</h2>
      <span class="badge bg-{{ $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($status) }}</span>
      <span class="badge bg-light text-dark border">{{ $material->materialTypeLabel() }}</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('educator.materials.edit', $material) }}" class="btn btn-primary">Edit</a>
      @if($material->isApproved())
        <a href="{{ $material->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-primary">Public page</a>
      @endif
      <a href="{{ route('educator.materials.index') }}" class="btn btn-light">Back to list</a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="chart-card mb-4">
        <h5 class="mb-3">Description</h5>
        @if(filled($material->description))
          <div>{!! $material->description !!}</div>
        @else
          <p class="text-secondary mb-0">No description provided.</p>
        @endif

        @if($tags->isNotEmpty())
          <div class="mt-3 d-flex flex-wrap gap-2">
            @foreach($tags as $tag)
              <span class="badge text-bg-light border">#{{ $tag }}</span>
            @endforeach
          </div>
        @endif
      </div>

      <div class="chart-card mb-4">
        <h5 class="mb-3">Details</h5>
        <dl class="row mb-0">
          <dt class="col-sm-4">Category</dt><dd class="col-sm-8">{{ $material->category ?: '—' }}</dd>
          <dt class="col-sm-4">Subject</dt><dd class="col-sm-8">{{ $material->subject ?: '—' }}</dd>
          <dt class="col-sm-4">Class / Course</dt><dd class="col-sm-8">{{ $material->class_course ?: '—' }}</dd>
          <dt class="col-sm-4">Board / University</dt><dd class="col-sm-8">{{ $material->board_university ?: '—' }}</dd>
          <dt class="col-sm-4">Topic / Chapter</dt><dd class="col-sm-8">{{ $material->topic_chapter ?: '—' }}</dd>
          <dt class="col-sm-4">Exam / Test</dt><dd class="col-sm-8">{{ $material->exam_test ?: '—' }}</dd>
          <dt class="col-sm-4">Language</dt><dd class="col-sm-8">{{ $material->language ?: '—' }}</dd>
          <dt class="col-sm-4">Difficulty</dt><dd class="col-sm-8">{{ $material->difficulty ?: '—' }}</dd>
          <dt class="col-sm-4">Academic year</dt><dd class="col-sm-8">{{ $material->academic_year ?: '—' }}</dd>
          <dt class="col-sm-4">Pages</dt><dd class="col-sm-8">{{ $material->pages ?: '—' }}</dd>
          <dt class="col-sm-4">Free download</dt><dd class="col-sm-8">{{ $material->is_free ? 'Yes' : 'No' }}</dd>
          <dt class="col-sm-4">Downloads</dt><dd class="col-sm-8">{{ number_format((int) $material->downloads_count) }}</dd>
          <dt class="col-sm-4">Updated</dt>
          <dd class="col-sm-8">{{ optional($material->updated_at)?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?: '—' }}</dd>
        </dl>
      </div>

      <div class="chart-card">
        <h5 class="mb-3">Table of contents</h5>
        @forelse($contents as $index => $item)
          <div class="py-2 border-bottom">{{ $index + 1 }}. {{ $item }}</div>
        @empty
          <p class="text-secondary mb-0">No contents outline provided.</p>
        @endforelse
      </div>
    </div>

    <div class="col-lg-4">
      <div class="chart-card mb-4 text-center">
        @if($material->thumbnailUrl())
          <img src="{{ $material->thumbnailUrl() }}" alt="{{ $material->title }}" class="img-fluid rounded mb-3" style="max-height:200px">
        @endif
        <h5 class="mb-2">File</h5>
        <p class="mb-1 fw-semibold">{{ $material->file_name ?: 'Study file' }}</p>
        <p class="small text-muted mb-3">{{ strtoupper((string) $material->file_type) }} · {{ $material->fileSizeLabel() }}</p>
        @if($material->fileUrl())
          <a href="{{ $material->fileUrl() }}" target="_blank" rel="noopener" class="btn btn-primary w-100">
            <i class="fa-solid fa-download me-1"></i> Open / Download file
          </a>
        @else
          <p class="text-secondary mb-0">No file uploaded.</p>
        @endif
      </div>

      <div class="chart-card">
        <h5 class="mb-3">Approval</h5>
        @if($status === 'pending')
          <p class="text-secondary mb-0 small">Waiting for admin review. You’ll get an email and portal notification when it’s decided.</p>
        @elseif($status === 'approved')
          <p class="text-success mb-0 small">Approved{{ $material->approved_at ? ' on '.$material->approved_at->format('d M Y') : '' }}.</p>
        @else
          <p class="text-danger mb-0 small">Rejected by admin. Edit and resubmit for approval.</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
