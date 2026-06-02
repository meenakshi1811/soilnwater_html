@extends('frontend.layouts.app')

@hasSection('title')
    @section('meta_title', trim($__env->yieldContent('title')))
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
    $consultantCategories = $consultantCategories ?? collect();
@endphp
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your consultant is published.</div>
    @endif

    @include('frontend.consultant.partials.store-header', [
        'consultant' => $consultant,
        'consultantCategories' => $consultantCategories,
        'activeNav' => $activeNav ?? '',
    ])

    @yield('consultant_content')

    @include('frontend.consultant.partials.store-footer', ['consultant' => $consultant])
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>if(window.toastr){window.toastr.options={closeButton:true,progressBar:true,positionClass:'toast-top-right',timeOut:4000,extendedTimeOut:2000};}</script>
<script src="{{ asset('assets/js/vendor-store.js') }}?v={{ now()->timestamp }}" defer></script>
@stack('consultant_scripts')
@endpush
