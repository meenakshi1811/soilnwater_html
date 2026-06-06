@extends('backend.layouts.app')

@section('title', 'Review Public Page')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/admin-service-page-review.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="service-page-review" data-review-page>
    <div class="service-page-review__toolbar">
        <div>
            <a href="{{ route('admin.consultants.index') }}" class="service-page-review__back"><i class="fa-solid fa-arrow-left"></i> Back to consultants</a>
            <p class="ems-kicker mb-1">Public Page Review</p>
            <h2 class="admin-title mb-1">{{ $consultant->publicDisplayName() }}</h2>
            <p class="text-muted mb-0">Review the submitted page exactly as it will appear to visitors, then approve or decline it.</p>
        </div>
        <div class="service-page-review__actions">
            <button type="button" class="btn btn-outline-danger js-decline-public-page" data-url="{{ route('admin.consultants.decline-public-page', $consultant) }}"><i class="fa-solid fa-xmark me-1"></i> Decline</button>
            <button type="button" class="btn btn-success js-approve-public-page" data-url="{{ route('admin.consultants.approve-public-page', $consultant) }}"><i class="fa-solid fa-check me-1"></i> Approve &amp; Publish</button>
        </div>
    </div>
    <div class="service-page-review__frame-wrap">
        <div class="service-page-review__browser-bar"><span></span><span></span><span></span><div>{{ route('consultant.show', data_get($consultant->pending_page_data, 'profile.slug', $consultant->slug)) }}</div></div>
        <iframe class="service-page-review__frame" src="{{ route('admin.consultants.public-page.preview', $consultant) }}" title="Submitted public page preview for {{ $consultant->publicDisplayName() }}"></iframe>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ asset('assets/js/admin-service-page-review.js') }}?v={{ now()->timestamp }}"></script>
@endpush
