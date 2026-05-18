@extends('backend.layouts.app')

@section('title', 'Manage Website')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/vendor-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page vendor-public-live-editor">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Vendor Panel</p>
            <h2 class="admin-title mb-0">Manage Website</h2>
            <p class="text-muted small mb-0">This editor matches your public preview. Click any highlighted text to edit.</p>
        </div>
        <button type="submit" form="publicPageForm" class="btn btn-primary ems-btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="publicPageForm" method="POST" action="{{ route('vendor.public-page.update') }}" enctype="multipart/form-data" class="vendor-store-page vendor-preview-edit-mode">
        @csrf
        @method('PUT')

        <input type="text" name="hero_main_heading" value="{{ old('hero_main_heading', $vendor->hero_main_heading) }}" data-sync-input="hero-main" hidden>
        <input type="text" name="hero_sub_heading" value="{{ old('hero_sub_heading', $vendor->hero_sub_heading) }}" data-sync-input="hero-sub" hidden>
        <input type="text" name="display_name" value="{{ old('display_name', $vendor->display_name) }}" data-sync-input="display-name" hidden>
        <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" data-sync-input="phone" hidden>
        <input type="email" name="email" value="{{ old('email', $vendor->email) }}" data-sync-input="email" hidden>
        <input type="text" name="city" value="{{ old('city', $vendor->city) }}" data-sync-input="city" hidden>
        <input type="text" name="address" value="{{ old('address', $vendor->address) }}" data-sync-input="address" hidden>
        <input type="url" name="facebook_url" value="{{ old('facebook_url', $vendor->facebook_url) }}" hidden>
        <input type="url" name="instagram_url" value="{{ old('instagram_url', $vendor->instagram_url) }}" hidden>

        <header class="vendor-store-header mb-3">
            <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
                <h4 class="vendor-live-editable mb-0" contenteditable="true" data-sync-target="display-name">{{ old('display_name', $vendor->display_name ?: 'Business Name') }}</h4>
                <div class="d-flex gap-2 align-items-center">
                    <span class="input-group input-group-sm" style="max-width:280px;">
                        <span class="input-group-text">/store/</span>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $vendor->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                    </span>
                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/*" style="max-width:220px;">
                </div>
            </div>
        </header>

        <section class="vendor-store-hero mb-4">
            @if($vendor->bannerSlides->count())
                <div id="storeHeroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                    <div class="carousel-inner h-100" id="bannerSlidesList">
                        @foreach($vendor->bannerSlides as $i => $slide)
                            <div class="carousel-item {{ $i === 0 ? 'active' : '' }} vendor-banner-slide" data-id="{{ $slide->id }}" style="background-image:url('{{ asset($slide->image_path) }}')">
                                <button type="button" class="btn btn-danger btn-sm vendor-slide-remove js-remove-slide" data-id="{{ $slide->id }}"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="carousel-inner h-100" id="bannerSlidesList"></div>
            @endif
            <div class="hero-overlay">
                <div class="container">
                    <h1 class="vendor-live-editable" contenteditable="true" data-sync-target="hero-main">{{ old('hero_main_heading', $vendor->hero_main_heading ?: 'Your Main Heading') }}</h1>
                    <p class="lead mb-3 vendor-live-editable" contenteditable="true" data-sync-target="hero-sub">{{ old('hero_sub_heading', $vendor->hero_sub_heading ?: 'Your sub heading appears here') }}</p>
                    <label class="btn btn-warning btn-sm fw-bold mb-0">
                        Upload Banner Images
                        <input type="file" name="banner_slides[]" class="d-none" accept="image/*" multiple id="bannerSlidesInput">
                    </label>
                    <small class="text-white ms-2" id="bannerUploadStatus">No files selected</small>
                </div>
            </div>
        </section>

        <div class="vendor-form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Page Sections</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addSectionBtn">+ Add Section</button>
            </div>
            <div id="sectionsContainer">
                @foreach($vendor->pageSections as $i => $section)
                    @include('backend.vendor.public-page._section', ['index' => $i, 'section' => $section])
                @endforeach
            </div>
        </div>
    </form>
</div>
<template id="sectionTemplate">
    @include('backend.vendor.public-page._section', ['index' => '__INDEX__', 'section' => null])
</template>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/vendor-public-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
