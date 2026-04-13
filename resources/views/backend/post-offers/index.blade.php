@extends('backend.layouts.app')

@section('title', 'Post Offer')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Admin CMS</p>
            <h2 class="admin-title mb-1">Post Offer</h2>
            <p class="mb-0 text-secondary">Create and publish a new offer for your users across different categories.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="mb-4">
            <h5 class="mb-0">Offer Details</h5>
            <p class="text-secondary mb-0 mt-1" style="font-size:0.875rem;">Fill in the details below to publish a new offer.</p>
        </div>

        <div id="offerAlert" class="alert d-none" role="alert"></div>

        <form id="offerForm" method="POST" action="{{ route('offers.store') }}" enctype="multipart/form-data" novalidate data-subcategory-url-base="{{ url('/offers/categories') }}">
            @csrf
            <input type="hidden" name="offer_id" id="offerId" value="">

            <div class="row g-4">

                {{-- Offer Title --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Offer Title <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="offerTitle"
                        class="form-control @error('title') is-invalid @enderror"
                        placeholder="e.g. Summer Sale — 30% Off All Plans"
                        value="{{ old('title') }}"
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Discount Tag --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Discount Tag <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="discount_tag"
                        id="discountTag"
                        class="form-control @error('discount_tag') is-invalid @enderror"
                        placeholder="e.g. 30% OFF, Buy 1 Get 1, Flat ₹500 Off"
                        value="{{ old('discount_tag') }}"
                    >
                    @error('discount_tag')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Coupon Code --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Coupon Code <span class="text-muted fw-normal">(Optional)</span>
                    </label>
                    <input
                        type="text"
                        name="coupon_code"
                        id="couponCode"
                        class="form-control @error('coupon_code') is-invalid @enderror"
                        placeholder="e.g. SUMMER30"
                        value="{{ old('coupon_code') }}"
                        style="text-transform: uppercase; letter-spacing: 0.05em;"
                    >
                    @error('coupon_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Valid Until --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Valid Until</label>
                    <input
                        type="date"
                        name="valid_until"
                        id="validUntil"
                        class="form-control @error('valid_until') is-invalid @enderror"
                        value="{{ old('valid_until') }}"
                        min="{{ now()->toDateString() }}"
                    >
                    @error('valid_until')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Target Category --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Target Category</label>
                    <select
                        name="category_id"
                        id="categorySelect"
                        class="form-select @error('category_id') is-invalid @enderror"
                    >
                        <option value="">— Select a category —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Sub Category --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sub Category</label>
                    <select
                        name="subcategory_id"
                        id="subcategorySelect"
                        class="form-select @error('subcategory_id') is-invalid @enderror"
                        disabled
                    >
                        <option value="">— Select a category first —</option>
                    </select>
                    @error('subcategory_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Banner Image --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Banner Image <span class="text-danger">*</span>
                    </label>
                    <div id="bannerDropzone" class="banner-dropzone" onclick="document.getElementById('bannerImage').click()">
                        <div id="bannerPreviewWrap" class="d-none position-relative">
                            <img id="bannerPreview" src="#" alt="Banner Preview" class="banner-preview-img">
                            <button type="button" class="btn btn-sm btn-danger banner-remove-btn" id="removeBannerBtn">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div id="bannerPlaceholder" class="banner-placeholder-content">
                            <i class="fa-solid fa-image fa-2x mb-2 text-secondary"></i>
                            <p class="mb-1 fw-semibold">Click or drag to upload banner</p>
                            <p class="mb-0 text-secondary" style="font-size:0.8rem;">Recommended: 1200×400px · PNG, JPG, WebP · Max 2MB</p>
                        </div>
                    </div>
                    <input
                        type="file"
                        name="banner_image"
                        id="bannerImage"
                        class="d-none @error('banner_image') is-invalid @enderror"
                        accept="image/png,image/jpeg,image/webp"
                    >
                    @error('banner_image')
                        <div class="text-danger mt-1" style="font-size:0.875rem;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Short Description --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Short Description</label>
                    <textarea
                        name="short_description"
                        id="shortDescription"
                        class="form-control @error('short_description') is-invalid @enderror"
                        rows="3"
                        placeholder="Write a brief, enticing description of this offer (max 300 characters)..."
                        maxlength="300"
                    >{{ old('short_description') }}</textarea>
                    <div class="d-flex justify-content-end mt-1">
                        <small class="text-secondary">
                            <span id="descCharCount">0</span>/300
                        </small>
                    </div>
                    @error('short_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>{{-- end .row --}}

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('post-offer') }}" class="btn btn-light px-4">Cancel</a>
                <button type="submit" id="offerSubmitBtn" class="btn btn-primary ems-btn-primary px-5">
                    <span class="btn-text">
                        <i class="fa-solid fa-paper-plane me-2"></i>Post Offer
                    </span>
                    <span class="btn-loader d-none" aria-hidden="true"></span>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/offer-and-discount.js') }}?v={{ now()->timestamp }}"></script>
@endpush
