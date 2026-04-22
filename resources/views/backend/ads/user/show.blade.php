@extends('backend.layouts.app')

@section('title', 'View Ad')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">View Ad</h2>
            <p class="mb-0 text-secondary">
                <strong>{{ $ad->title }}</strong> ·
                {{ $size['name'] ?? $ad->size_type }}
            </p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">Rendered Preview</h5>
                    <span class="badge bg-{{ $ad->status === 'approved' ? 'success' : ($ad->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($ad->status) }}</span>
                </div>

                <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] ?? '1 / 1' }};">
                    <div class="ads-live-preview-inner">
                        {!! $ad->rendered_html ?: '<div class="text-secondary p-3">No rendered HTML saved.</div>' !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="chart-card">
                <h5 class="mb-3">Submission Details</h5>
                <div class="mb-2"><span class="text-secondary">Template:</span> <strong>{{ $ad->template?->name ?? '-' }}</strong></div>
                <div class="mb-2"><span class="text-secondary">Submitted:</span> {{ $ad->submitted_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div class="mb-2"><span class="text-secondary">Reviewed:</span> {{ $ad->reviewed_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div class="mb-2"><span class="text-secondary">Status:</span> <strong>{{ ucfirst($ad->status) }}</strong></div>

                <hr>

                <div class="alert {{ $ad->review_note ? 'alert-secondary' : 'alert-light' }} mb-0">
                    <div class="fw-semibold mb-1">Review reason/note</div>
                    <div>{{ $ad->review_note ?: 'No reason added yet.' }}</div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('ads.index') }}" class="btn btn-light px-4">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
