@extends('frontend.layouts.app')

@hasSection('title')
    @section('meta_title', trim($__env->yieldContent('title')))
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/school-profile-store.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $ownerRole = $ownerRole ?? 'school';
    $listingContext = $listingContext ?? ($ownerRole === 'school' ? 'schools' : 'institutes');
    $listingLabel = $ownerRole === 'school' ? 'Schools' : 'Institutes';
    $homeLabel = $ownerRole === 'school' ? 'School Home' : 'Institute Home';
    $listingIcon = $ownerRole === 'school' ? 'fa-school' : 'fa-building-columns';
@endphp
<div class="vendor-store-page" id="instituteProfilePage"
    data-enquiry-url="{{ route($listingContext.'.enquiry', $institute->slug) }}"
    data-follow-url="{{ route($listingContext.'.follow', $institute->slug) }}"
    data-bookmark-url="{{ route($listingContext.'.bookmark', $institute->slug) }}"
    data-compare-url="{{ route($listingContext.'.compare.toggle', $institute->slug) }}"
    data-brochure-url="{{ route($listingContext.'.brochure', $institute->slug) }}"
    data-helpful-url="{{ route($listingContext.'.helpful', $institute->slug) }}"
    data-login-url="{{ route('login') }}"
    data-is-auth="{{ auth()->check() ? '1' : '0' }}"
    data-share-url="{{ $institute->publicUrl() }}"
    data-share-title="{{ $institute->displayName() }}"
>
    @if(session('status'))
        <div class="container pt-3">
            <div class="alert alert-success mb-0">{{ session('status') }}</div>
        </div>
    @endif

    @include('frontend.institutes.partials.store-header', [
        'institute' => $institute,
        'ownerRole' => $ownerRole,
        'listingContext' => $listingContext,
        'activeNav' => $activeNav ?? 'home',
    ])

    @include('frontend.partials.marketplace-store-quicknav', [
        'storeHomeUrl' => $institute->publicUrl(),
        'storeHomeLabel' => $homeLabel,
        'marketListingUrl' => route($listingContext.'.index'),
        'marketListingLabel' => $listingLabel,
        'marketListingIcon' => $listingIcon,
        'activeNav' => $activeNav ?? 'home',
    ])

    @yield('institute_content')

    @include('frontend.institutes.partials.store-footer', ['institute' => $institute])
</div>
@include('community.partials.toastr-assets')
@endsection

@push('scripts')
<script src="{{ asset('assets/js/school-profile-notify.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/vendor-store.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/institute-enquiry-form.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/school-profile-actions.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/institute-profile.js') }}?v={{ now()->timestamp }}" defer></script>
@stack('institute_scripts')
@endpush
