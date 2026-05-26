@extends('backend.layouts.app')

@section('title', 'Manage Website')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/vendor-portal.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
    $heroMainColor = old('hero_main_style.color', $vendor->hero_main_style['color'] ?? '#ffffff');
    $heroSubColor = old('hero_sub_style.color', $vendor->hero_sub_style['color'] ?? '#ffffff');
@endphp
<div class="admin-panel ems-page vendor-public-live-editor">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Vendor Panel</p>
            <h2 class="admin-title mb-0">{{ $vendor->publicDisplayName() }}</h2>
            <p class="text-muted small mb-0">Edit your store below, click <strong>Save Changes</strong>, then open <strong>Live Preview</strong> to see the published look.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.public-page.preview') }}" target="_blank" class="btn btn-outline-secondary">
                <i class="fa-solid fa-up-right-from-square me-1"></i> Live Preview
            </a>
            <button type="submit" form="publicPageForm" class="btn btn-primary ems-btn-primary" id="publicPageSaveBtn">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="publicPageForm" method="POST" action="{{ route('vendor.public-page.update') }}" enctype="multipart/form-data" class="vendor-store-page vendor-preview-edit-mode" data-banner-delete-url="{{ url('vendor/banner-slides') }}/">
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
        <input type="hidden" name="hero_main_style[color]" data-style-input="hero-main" data-style-prop="color" value="{{ $heroMainColor }}">
        <input type="hidden" name="hero_main_style[fontSize]" data-style-input="hero-main" data-style-prop="fontSize" value="{{ old('hero_main_style.fontSize', $vendor->hero_main_style['fontSize'] ?? '') }}">
        <input type="hidden" name="hero_main_style[fontFamily]" data-style-input="hero-main" data-style-prop="fontFamily" value="{{ old('hero_main_style.fontFamily', $vendor->hero_main_style['fontFamily'] ?? '') }}">
        <input type="hidden" name="hero_main_style[fontWeight]" data-style-input="hero-main" data-style-prop="fontWeight" value="{{ old('hero_main_style.fontWeight', $vendor->hero_main_style['fontWeight'] ?? '') }}">
        <input type="hidden" name="hero_sub_style[color]" data-style-input="hero-sub" data-style-prop="color" value="{{ $heroSubColor }}">
        <input type="hidden" name="hero_sub_style[fontSize]" data-style-input="hero-sub" data-style-prop="fontSize" value="{{ old('hero_sub_style.fontSize', $vendor->hero_sub_style['fontSize'] ?? '') }}">
        <input type="hidden" name="hero_sub_style[fontFamily]" data-style-input="hero-sub" data-style-prop="fontFamily" value="{{ old('hero_sub_style.fontFamily', $vendor->hero_sub_style['fontFamily'] ?? '') }}">
        <input type="hidden" name="hero_sub_style[fontWeight]" data-style-input="hero-sub" data-style-prop="fontWeight" value="{{ old('hero_sub_style.fontWeight', $vendor->hero_sub_style['fontWeight'] ?? '') }}">

        <div class="vendor-editor-panel mb-3">
            <div class="vendor-editor-panel-head">
                <i class="fa-solid fa-store text-primary"></i>
                <div>
                    <strong>Store header</strong>
                    <p class="mb-0 small text-muted">This matches your live store. Click the logo box to upload or change your logo.</p>
                </div>
            </div>
            <header class="vendor-store-header vendor-header-preview">
                <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <label class="vendor-logo-dropzone mb-0" for="logoInput" title="Click to upload your store logo">
                            <span class="vendor-logo-dropzone-inner" id="logoPreviewWrap">
                                @if($vendor->logo)
                                    <img src="{{ asset($vendor->logo) }}" alt="Store logo" class="vendor-logo-dropzone-img" id="logoPreviewImg">
                                @else
                                    <span class="vendor-logo-dropzone-placeholder" id="logoPlaceholder">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Add logo</span>
                                    </span>
                                @endif
                                <span class="vendor-logo-dropzone-hint"><i class="fa-solid fa-camera"></i> {{ $vendor->logo ? 'Change' : 'Upload' }}</span>
                            </span>
                            <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                        </label>
                        <strong class="fs-4 mb-0 vendor-store-name-fallback {{ $vendor->logo ? 'd-none' : '' }}" id="storeNamePreview">{{ $vendor->publicDisplayName() }}</strong>
                    </div>
                    <nav class="vendor-store-nav d-none d-md-flex text-muted small">
                        <span>Home</span>
                        <span>Products</span>
                        <span>Contact</span>
                    </nav>
                </div>
            </header>
            <div class="vendor-store-url-row px-3 pb-3">
                <label class="form-label small text-muted mb-1">Your store link</label>
                <div class="input-group input-group-sm" style="max-width:560px; width:100%;">
                    <span class="input-group-text">{{ url('/store') }}/</span>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $vendor->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="your-store-name">
                </div>
            </div>
        </div>

        <div class="vendor-editor-panel mb-4">
            <div class="vendor-editor-panel-head">
                <i class="fa-solid fa-panorama text-primary"></i>
                <div>
                    <strong>Hero banner</strong>
                    <p class="mb-0 small text-muted">Upload banner images and customize the main heading text below.</p>
                </div>
            </div>
            <section class="vendor-store-hero">
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
                        <label class="btn btn-warning btn-sm fw-bold mb-0">
                            <i class="fa-solid fa-upload me-1"></i> Upload banner images
                            <input type="file" name="banner_slides[]" class="d-none" accept="image/*" multiple id="bannerSlidesInput">
                        </label>
                        <small class="text-white ms-2" id="bannerUploadStatus">No new files selected</small>
                    </div>
                </div>
            </section>
        </div>

        <div class="vendor-banner-thumbs-wrap mb-4">
            <div class="small text-muted mb-2"><i class="fa-solid fa-images me-1"></i> Banner thumbnails — click to preview, × to remove</div>
            <div class="vendor-banner-thumbs" id="bannerThumbs"></div>
        </div>

        <div class="vendor-form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Hero heading text</h5>
                    <p class="text-muted small mb-0">This text will appear below the banner thumbnails and on the frontend as a separate section.</p>
                </div>
            </div>
            <div class="vendor-section-style-panel mb-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <p class="small fw-semibold mb-0 text-primary"><i class="fa-solid fa-palette me-1"></i> Styling tools</p>
                    <span class="badge bg-primary-subtle text-primary border" data-hero-active-label>Click heading or subheading below</span>
                </div>
                <p class="small text-muted mb-2">Click the <strong>heading</strong> or <strong>subheading</strong> below — styles apply to whichever you clicked last.</p>
                <div class="row g-2 align-items-end">
                    <div class="col-md-3 col-6">
                        <label class="form-label small mb-1">Text color</label>
                        <input type="color" class="form-control form-control-color form-control-sm w-100" data-hero-style="color" value="{{ $heroMainColor }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small mb-1">Font size</label>
                        <select class="form-select form-select-sm" data-hero-style="fontSize">
                            <option value="">Default</option>
                            <option value="16px">16px</option>
                            <option value="20px">20px</option>
                            <option value="24px">24px</option>
                            <option value="32px">32px</option>
                            <option value="42px">42px</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-dark" data-hero-toggle="fontWeight" data-hero-toggle-value="700" title="Bold"><i class="fa-solid fa-bold"></i></button>
                    </div>
                </div>
            </div>
            <p class="text-muted small mb-1">Click text to edit</p>
            <h1 class="vendor-live-editable" contenteditable="true" data-sync-target="hero-main" data-hero-editable="main" style="@if(!empty($vendor->hero_main_style)){{ collect($vendor->hero_main_style)->map(fn($v,$k)=>$k.':'.$v)->implode(';') }}@endif">{{ old('hero_main_heading', $vendor->hero_main_heading ?: 'Your Main Heading') }}</h1>
            <p class="lead mb-0 vendor-live-editable" data-sync-target="hero-sub" data-sync-html="1" data-hero-editable="sub" id="heroSubHeadingEditor" style="@if(!empty($vendor->hero_sub_style)){{ collect($vendor->hero_sub_style)->map(fn($v,$k)=>$k.':'.$v)->implode(';') }}@endif">{!! html_entity_decode(old('hero_sub_heading', $vendor->hero_sub_heading ?: 'Your sub heading appears here')) !!}</p>
            <textarea id="heroSubHeadingCkEditor" class="form-control mt-2" rows="4">{!! old('hero_sub_heading', $vendor->hero_sub_heading ?: 'Your sub heading appears here') !!}</textarea>
        </div>

        <div class="vendor-form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">About Us page content</h5>
                    <p class="text-muted small mb-0">This content is for a <strong>separate About Us page</strong>, not your store home page sections.</p>
                </div>
            </div>
            <div class="alert alert-warning small mb-3">
                <i class="fa-solid fa-circle-info me-1"></i>
                Visitors open this from Store Header <strong>About Us</strong> and it opens in a new page.
            </div>
            <textarea name="description" id="vendorAboutUsEditor" class="form-control" rows="10">{{ old('description', $vendor->description) }}</textarea>
        </div>

        <div class="vendor-form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Social media links</h5>
                    <p class="text-muted small mb-0">Add your social URLs and see how they will appear on your public store page.</p>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" class="form-control" data-social-input="facebook" value="{{ old('facebook_url', $vendor->facebook_url) }}" placeholder="https://facebook.com/yourpage">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Instagram URL</label>
                    <input type="url" class="form-control" data-social-input="instagram" value="{{ old('instagram_url', $vendor->instagram_url) }}" placeholder="https://instagram.com/yourhandle">
                </div>
            </div>
            <div class="p-3 rounded border bg-light">
                <p class="small text-muted mb-2">Frontend preview</p>
                <div id="socialLinksPreview" class="d-flex flex-column gap-1">
                    <a href="#" target="_blank" class="small d-none" data-social-preview="facebook">Facebook</a>
                    <a href="#" target="_blank" class="small d-none" data-social-preview="instagram">Instagram</a>
                    <span class="small text-muted" data-social-empty>Social links will appear here when URLs are added.</span>
                </div>
            </div>
        </div>

        <div class="vendor-form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Custom page sections</h5>
                    <p class="text-muted small mb-0">Add sections with images and styled text. Save, then check Live Preview.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" id="sectionTypeSelect" style="min-width: 220px;">
                        <option value="image_text">Image + Text card</option>
                        <option value="image_grid">Image grid (8 images)</option>
                        <option value="text_only">Only text section</option>
                        <option value="brochure">Brochure section</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addSectionBtn"><i class="fa-solid fa-plus me-1"></i> Add section</button>
                </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    if (window.toastr) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
            extendedTimeOut: 2000
        };
    }

    if (window.ClassicEditor) {
        var aboutUsEditor = document.querySelector('#vendorAboutUsEditor');
        if (aboutUsEditor) {
            ClassicEditor
                .create(aboutUsEditor)
                .catch(function (error) {
                    console.error(error);
                });
        }

        var heroSubHeadingEditor = document.querySelector('#heroSubHeadingCkEditor');
        if (heroSubHeadingEditor) {
            ClassicEditor
                .create(heroSubHeadingEditor, {
                    toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
                })
                .then(function (editor) {
                    editor.model.document.on('change:data', function () {
                        var preview = document.querySelector('#heroSubHeadingEditor');
                        if (preview) preview.innerHTML = editor.getData();
                        var input = document.querySelector('[data-sync-input="hero-sub"]');
                        if (input) input.value = editor.getData().trim();
                    });
                })
                .catch(function (error) {
                    console.error(error);
                });
        }
    }
</script>
<script src="{{ asset('assets/js/vendor-public-page.js') }}?v={{ now()->timestamp }}"></script>
@endpush
