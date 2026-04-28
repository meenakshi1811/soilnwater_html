@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@section('content')
<div class="admin-panel ems-page" id="adsSizeCustomizerPage">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Create Your Ad</h2>
            <p class="mb-0 text-secondary">Selected size: <strong>{{ $size['name'] }}</strong> ({{ $size['w'] }}×{{ $size['h'] }} px)</p>
        </div>
    </div>

    <div class="chart-card">
        <form method="POST" action="{{ route('ads.store', ['sizeType' => $sizeType, 'template' => $template->id]) }}" novalidate data-subcategory-url-base="{{ url('/dashboard/ads/categories') }}">
            @csrf
            <input type="hidden" name="custom_html" id="customHtmlInput" value="">
            <input type="hidden" name="generated_image_data" id="generatedImageDataInput" value="">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ad Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="140" required>
                </div>
                <div class="col-md-6">
                    <label for="categorySelect" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" id="categorySelect" class="form-select" required>
                        <option value="">— Select category —</option>
                        @foreach($categories as $category)
                            @php $categoryPrice = (float) ($category->ads_price ?? 0) @endphp
                            <option value="{{ $category->id }}" data-ads-price="{{ number_format($categoryPrice, 2, '.', '') }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="subcategorySelect" class="form-label fw-semibold">Sub Category <span class="text-danger">*</span></label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-select" disabled required>
                        <option value="">— Select a category first —</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                    <input type="text" name="location" id="adLocation" class="form-control" value="{{ old('location') }}" required>
                    <input type="hidden" name="location_lat" id="adLocationLat" value="{{ old('location_lat') }}">
                    <input type="hidden" name="location_lng" id="adLocationLng" value="{{ old('location_lng') }}">
                </div>
            </div>

            <div class="banner-mode-switch mb-3">
                <label class="banner-mode-option">
                    <input type="radio" name="design_mode" value="upload" class="banner-mode-radio" checked>
                    <span class="banner-mode-card is-active">
                        <span class="banner-mode-title">Add Ad Image</span>
                        <small class="banner-mode-text">Upload one image and auto-fit to selected size.</small>
                    </span>
                </label>
                <label class="banner-mode-option">
                    <input type="radio" name="design_mode" value="customize" class="banner-mode-radio">
                    <span class="banner-mode-card">
                        <span class="banner-mode-title">Customize</span>
                        <small class="banner-mode-text">Design inside selected size box.</small>
                    </span>
                </label>
            </div>

            <div id="uploadWrap" class="mb-3">
                <label class="form-label">Ad Image (PNG/JPG/WebP)</label>
                <input type="file" id="uploadImageInput" class="d-none" accept="image/png,image/jpeg,image/webp">
                <div id="adDropzone" class="banner-dropzone">
                    <div class="banner-placeholder-content">
                        <i class="fa-solid fa-image fa-2x mb-2 text-secondary"></i>
                        <p class="mb-1 fw-semibold">Click or drag to upload ad image</p>
                        <p class="mb-0 text-secondary" style="font-size:0.8rem;">Recommended: {{ $size['w'] }}×{{ $size['h'] }}px · PNG, JPG, WebP · Max 2MB</p>
                    </div>
                </div>
                <small class="text-secondary d-block mt-2">Image will be normalized to {{ $size['w'] }}×{{ $size['h'] }} using Intervention on save.</small>
            </div>

            <div id="customizeWrap" class="mb-3 d-none">
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addTextBtn">Add Text</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addImageBtn">Add Image</button>
                    <input type="file" id="customImageInput" class="d-none" accept="image/png,image/jpeg,image/webp">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="removeLayerBtn">Remove Selected</button>
                </div>
            </div>

            <div class="border rounded p-2 bg-light d-none" id="canvasWrap">
                <div class="small text-secondary mb-2">Final Ads Preview (what users will see)</div>
                <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] }};">
                    <div class="ads-live-preview-inner" id="adPreviewFrame" data-source-width="{{ $size['w'] }}" data-source-height="{{ $size['h'] }}">
                        <div id="adPreview" class="ads-mini-preview-inner" style="position:relative;overflow:hidden;background:#f7f7f7;width:{{ $size['w'] }}px;height:{{ $size['h'] }}px;"></div>
                    </div>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="acceptTerms" name="accept_terms" value="1" required>
                <label class="form-check-label" for="acceptTerms">I agree to Terms and Conditions</label>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('ads.create.size') }}" class="btn btn-light px-4">Back</a>
                <button type="submit" class="btn btn-primary px-5">Save Ad</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.13/dist/html-to-image.min.js"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAdLocationAutocomplete"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/ads.js') }}?v={{ now()->timestamp }}"></script>
@endpush
