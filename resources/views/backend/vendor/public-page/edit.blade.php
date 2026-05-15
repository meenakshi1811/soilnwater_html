@extends('backend.layouts.app')

@section('title', 'Manage Website')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Vendor Panel</p>
            <h2 class="admin-title mb-0">Manage Website</h2>
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

    <form id="publicPageForm" method="POST" action="{{ route('vendor.public-page.update') }}" enctype="multipart/form-data" class="vendor-public-editor d-flex flex-column flex-lg-row gap-4">
        @csrf
        @method('PUT')

        <div class="editor-main">
            <div class="vendor-form-card mb-4">
                <h5 class="mb-3">Hero Banner</h5>
                <div class="mb-3">
                    <label class="form-label">Main Heading</label>
                    <input type="text" name="hero_main_heading" class="form-control" value="{{ old('hero_main_heading', $vendor->hero_main_heading) }}" placeholder="e.g. Smart Digital Signage">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sub Heading</label>
                    <input type="text" name="hero_sub_heading" class="form-control" value="{{ old('hero_sub_heading', $vendor->hero_sub_heading) }}">
                </div>
                <label class="form-label">Banner Images (Slider)</label>
                <div class="d-flex flex-wrap gap-2 mb-3" id="bannerSlidesList">
                    @foreach($vendor->bannerSlides as $slide)
                        <div class="vendor-banner-thumb" data-id="{{ $slide->id }}">
                            <img src="{{ asset($slide->image_path) }}" alt="">
                            <button type="button" class="btn btn-danger btn-sm btn-remove js-remove-slide" data-id="{{ $slide->id }}"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    @endforeach
                </div>
                <label class="vendor-upload-zone d-block">
                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-secondary mb-2"></i>
                    <p class="mb-0 small text-secondary">Click to upload new slides</p>
                    <input type="file" name="banner_slides[]" class="d-none" accept="image/*" multiple id="bannerSlidesInput">
                </label>
            </div>

            <div class="vendor-form-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Custom Page Sections</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addSectionBtn">+ Add Section</button>
                </div>
                <div id="sectionsContainer">
                    @foreach($vendor->pageSections as $i => $section)
                        @include('backend.vendor.public-page._section', ['index' => $i, 'section' => $section])
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="editor-sidebar">
            <div class="vendor-form-card mb-3">
                <h6 class="fw-bold mb-3">Business Info</h6>
                <div class="mb-3">
                    <label class="form-label">Display Name</label>
                    <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $vendor->display_name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo</label>
                    @if($vendor->logo)
                        <img src="{{ asset($vendor->logo) }}" alt="" class="d-block mb-2 rounded" style="width:64px;height:64px;object-fit:cover">
                    @endif
                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-0">
                    <label class="form-label">URL Slug</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">/store/</span>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $vendor->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                    </div>
                </div>
            </div>
            <div class="vendor-form-card mb-3">
                <h6 class="fw-bold mb-3">Contact Details</h6>
                <input type="text" name="phone" class="form-control form-control-sm mb-2" placeholder="Phone" value="{{ old('phone', $vendor->phone) }}">
                <input type="email" name="email" class="form-control form-control-sm mb-2" placeholder="Email" value="{{ old('email', $vendor->email) }}">
                <input type="text" name="city" class="form-control form-control-sm mb-2" placeholder="City" value="{{ old('city', $vendor->city) }}">
                <input type="text" name="address" class="form-control form-control-sm" placeholder="Address" value="{{ old('address', $vendor->address) }}">
            </div>
            <div class="vendor-form-card">
                <h6 class="fw-bold mb-3">Social Links</h6>
                <input type="url" name="facebook_url" class="form-control form-control-sm mb-2" placeholder="Facebook URL" value="{{ old('facebook_url', $vendor->facebook_url) }}">
                <input type="url" name="instagram_url" class="form-control form-control-sm" placeholder="Instagram URL" value="{{ old('instagram_url', $vendor->instagram_url) }}">
            </div>
        </aside>
    </form>
</div>

<template id="sectionTemplate">
    @include('backend.vendor.public-page._section', ['index' => '__INDEX__', 'section' => null])
</template>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/vendor-public-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
