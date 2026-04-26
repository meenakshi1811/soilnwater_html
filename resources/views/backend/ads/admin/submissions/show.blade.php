@extends('backend.layouts.app')

@section('title', 'Review Ad')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Review Ad</h2>
            <p class="mb-0 text-secondary">
                <strong>{{ $ad->title }}</strong> ·
                {{ $ad->user?->full_name ?: ($ad->user?->name ?? '-') }} ·
                {{ $size['name'] ?? $ad->size_type }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">Rendered Preview</h5>
                    <span class="badge bg-{{ $ad->status === 'approved' ? 'success' : ($ad->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($ad->status) }}</span>
                </div>

                @if($ad->final_image)
                    <div class="mb-3">
                        <img src="{{ asset($ad->final_image) }}" alt="Final ad image" class="img-fluid rounded border">
                    </div>
                @endif

                <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] ?? '1 / 1' }};">
                    <div class="ads-live-preview-inner">
                        {!! $ad->rendered_html ?: '<div class="text-secondary p-3">No rendered HTML saved.</div>' !!}
                    </div>
                </div>

                @if($ad->review_note)
                    <div class="mt-3 alert alert-secondary mb-0">
                        <div class="fw-semibold mb-1">Review note</div>
                        <div>{{ $ad->review_note }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="chart-card">
                <h5 class="mb-3">Submission Details</h5>
                <div class="mb-2"><span class="text-secondary">Template:</span> <strong>{{ $ad->template?->name ?? '-' }}</strong></div>
                <div class="mb-2"><span class="text-secondary">Submitted:</span> {{ $ad->submitted_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div class="mb-2"><span class="text-secondary">Reviewed:</span> {{ $ad->reviewed_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div class="mb-3"><span class="text-secondary">User email:</span> {{ $ad->user?->email ?? '-' }}</div>

                <hr>

                @if($ad->status === 'pending')
                    <form method="POST" action="{{ route('admin.ads.submissions.approve', $ad) }}" class="mb-3">
                        @csrf
                        <label class="form-label fw-semibold">Optional note (visible to user)</label>
                        <textarea name="review_note" rows="2" class="form-control" maxlength="400" placeholder="e.g. Approved. Looks good."></textarea>
                        <button type="submit" class="btn btn-success w-100 mt-2">
                            <i class="fa-solid fa-check me-2"></i>Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.ads.submissions.reject', $ad) }}">
                        @csrf
                        <label class="form-label fw-semibold">Reject note <span class="text-danger">*</span></label>
                        <textarea name="review_note" rows="3" class="form-control @error('review_note') is-invalid @enderror" maxlength="400" placeholder="Explain what to fix (required).">{{ old('review_note') }}</textarea>
                        @error('review_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-danger w-100 mt-2">
                            <i class="fa-solid fa-xmark me-2"></i>Reject
                        </button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">This submission is already {{ $ad->status }}.</div>
                @endif

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('admin.ads.submissions.index') }}" class="btn btn-light px-4">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/ads.js') }}?v={{ now()->timestamp }}"></script>
@endpush
