@extends('frontend.layouts.app')

@hasSection('title')
    @section('meta_title', trim($__env->yieldContent('title')))
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $vendorCategories = $vendorCategories ?? collect();
@endphp
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your store is published.</div>
    @endif

    @include('frontend.store.partials.store-header', [
        'vendor' => $vendor,
        'vendorCategories' => $vendorCategories,
        'activeNav' => $activeNav ?? '',
    ])

    @include('frontend.partials.marketplace-store-quicknav', [
        'storeHomeUrl' => route('store.show', $vendor->slug),
        'storeHomeLabel' => 'Store Home',
        'marketListingUrl' => route('frontend.vendors.index'),
        'marketListingLabel' => 'Vendors',
        'marketListingIcon' => 'fa-shop',
        'activeNav' => $activeNav ?? '',
    ])

    @include('frontend.premium.partials.profile-status', ['profile' => $vendor, 'type' => 'vendor'])

    @yield('store_content')

    @include('frontend.store.partials.store-footer', ['vendor' => $vendor])
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/vendor-store.js') }}?v={{ now()->timestamp }}" defer></script>
@stack('store_scripts')
@endpush
