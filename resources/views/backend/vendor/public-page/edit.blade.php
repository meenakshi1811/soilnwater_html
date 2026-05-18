@extends('backend.layouts.app')

@section('title', 'Manage Website')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page vendor-public-live-editor">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Vendor Panel</p>
            <h2 class="admin-title mb-0">Manage Website</h2>
            <p class="text-muted mb-0 small">Edit directly on the preview below — changes sync automatically.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.public-page.preview') }}" target="_blank" class="btn btn-light">
                <i class="fa-regular fa-eye me-1"></i> Live Preview
            </a>
            <button type="submit" form="publicPageForm" class="btn btn-primary ems-btn-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="publicPageForm" method="POST" action="{{ route('vendor.public-page.update') }}" enctype="multipart/form-data" class="vendor-public-editor">
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

        <div class="vendor-live-preview-card mb-4">
            <div class="vendor-live-hero">
                <h1 class="vendor-live-editable" contenteditable="true" data-sync-target="hero-main">{{ old('hero_main_heading', $vendor->hero_main_heading ?: 'Your Main Heading') }}</h1>
                <p class="vendor-live-editable" contenteditable="true" data-sync-target="hero-sub">{{ old('hero_sub_heading', $vendor->hero_sub_heading ?: 'Your sub heading appears here') }}</p>
            </div>

            <h6 class="fw-bold mb-2">Banner Images</h6>
            <div class="d-flex flex-wrap gap-2 mb-3" id="bannerSlidesList">
                @foreach($vendor->bannerSlides as $slide)
                    <div class="vendor-banner-thumb" data-id="{{ $slide->id }}">
                        <img src="{{ asset($slide->image_path) }}" alt="">
                        <button type="button" class="btn btn-danger btn-sm btn-remove js-remove-slide" data-id="{{ $slide->id }}"><i class="fa-solid fa-trash"></i></button>
                    </div>
                @endforeach
            </div>
            <label class="vendor-upload-zone d-block mb-0">
                <i class="fa-solid fa-cloud-arrow-up fa-2x text-secondary mb-2"></i>
                <p class="mb-0 small text-secondary">Click to upload new slides</p>
                <input type="file" name="banner_slides[]" class="d-none" accept="image/*" multiple id="bannerSlidesInput">
            </label>
        </div>

        <div class="vendor-live-preview-card mb-4">
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

        <div class="vendor-live-preview-card">
            <h5 class="mb-3">Business Information</h5>
            <div class="row g-3 align-items-start">
                <div class="col-md-8">
                    <h4 class="vendor-live-editable mb-2" contenteditable="true" data-sync-target="display-name">{{ old('display_name', $vendor->display_name ?: 'Business Name') }}</h4>
                    <p class="mb-1"><strong>Phone:</strong> <span class="vendor-live-editable" contenteditable="true" data-sync-target="phone">{{ old('phone', $vendor->phone ?: 'Add phone') }}</span></p>
                    <p class="mb-1"><strong>Email:</strong> <span class="vendor-live-editable" contenteditable="true" data-sync-target="email">{{ old('email', $vendor->email ?: 'Add email') }}</span></p>
                    <p class="mb-1"><strong>City:</strong> <span class="vendor-live-editable" contenteditable="true" data-sync-target="city">{{ old('city', $vendor->city ?: 'Add city') }}</span></p>
                    <p class="mb-0"><strong>Address:</strong> <span class="vendor-live-editable" contenteditable="true" data-sync-target="address">{{ old('address', $vendor->address ?: 'Add address') }}</span></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Logo</label>
                    @if($vendor->logo)
                        <img src="{{ asset($vendor->logo) }}" alt="" class="d-block mb-2 rounded" style="width:64px;height:64px;object-fit:cover">
                    @endif
                    <input type="file" name="logo" class="form-control form-control-sm mb-3" accept="image/*">
                    <label class="form-label">URL Slug</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">/store/</span>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $vendor->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                    </div>
                </div>
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
