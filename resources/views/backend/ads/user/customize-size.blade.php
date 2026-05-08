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
        <form method="POST" action="{{ route('ads.store', ['sizeType' => $sizeType, 'template' => $template->id??null]) }}" novalidate data-subcategory-url-base="{{ url('/dashboard/ads/categories') }}">
            @csrf
            <input type="hidden" name="custom_html" id="customHtmlInput" value="">
            <input type="hidden" name="generated_image_data" id="generatedImageDataInput" value="">
            <input type="hidden" name="ad_image_input_type" id="adImageInputType" value="1">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold required-label">Ad Title <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="140" required>
                </div>
                <div class="col-md-6">
                    <label for="categorySelect" class="form-label fw-semibold required-label">Category <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <select name="category_id" id="categorySelect" class="form-select" required>
                        <option value="">— Select category —</option>
                        @foreach($categories as $category)
                            @php $categoryPrice = $size['category_prices'][$category->id] ?? null; @endphp
                            <option value="{{ $category->id }}" data-ad-price="{{ $categoryPrice !== null ? (float) $categoryPrice : '' }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @if((bool) ($size['is_paid'] ?? false))
                        <div id="adCategoryPriceNote" class="form-text text-primary fw-semibold">Select a category to see this size price.</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label for="subcategorySelect" class="form-label fw-semibold required-label">Sub Category <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-select" disabled required>
                        <option value="">— Select a category first —</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold required-label">Location <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <input type="text" name="location" id="adLocation" class="form-control" value="{{ old('location') }}" required>
                    <input type="hidden" name="location_lat" id="adLocationLat" value="{{ old('location_lat') }}">
                    <input type="hidden" name="location_lng" id="adLocationLng" value="{{ old('location_lng') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold required-label">Valid Upto <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until') }}" min="{{ now()->toDateString() }}" required>
                    @error('valid_until')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @if(auth()->user()?->isStaff())
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Is Sponsored</label>
                    <select name="is_sponsored" class="form-select">
                        <option value="0" {{ old('is_sponsored', '0') === '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('is_sponsored') === '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                @endif
            </div>

            <label class="form-label fw-semibold required-label">
                Ad Image <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i>
            </label>
            <p class="text-secondary mb-3" style="font-size:0.9rem;">
                Add your own ad image or customize the final creative with full designer controls, similar to Post Offer.
            </p>

            <div class="banner-mode-switch mb-4">
                <label class="banner-mode-option">
                    <input type="radio" name="design_mode" value="upload" class="banner-mode-radio" checked>
                    <span class="banner-mode-card is-active">
                        <span class="banner-mode-title">Add Ad Image</span>
                        <small class="banner-mode-text">Use your ready-made creative (PNG/JPG/WebP).</small>
                    </span>
                </label>
                <label class="banner-mode-option">
                    <input type="radio" name="design_mode" value="customize" class="banner-mode-radio">
                    <span class="banner-mode-card">
                        <span class="banner-mode-title">Customize Ad</span>
                        <small class="banner-mode-text">Design using text, images, colors, and drag/drop controls.</small>
                    </span>
                </label>
            </div>
            <div id="adImageError" class="invalid-feedback d-block" style="display:none;"></div>

            <div id="uploadWrap" class="mb-3">
                <label class="form-label">Ad Image (PNG/JPG/WebP)</label>
                <input type="file" id="uploadImageInput" class="d-none" accept="image/png,image/jpeg,image/webp"
                    data-required-width="{{ $size['w'] }}"
                    data-required-height="{{ $size['h'] }}">
                <div id="adDropzone" class="banner-dropzone">
                    <div id="adDropzonePreviewWrap" class="d-none position-relative">
                        <img id="adDropzonePreview" src="#" alt="Ad image preview" class="banner-preview-img">
                    </div>
                    <div id="adDropzonePlaceholder" class="banner-placeholder-content">
                        <i class="fa-solid fa-image fa-2x mb-2 text-secondary"></i>
                        <p class="mb-1 fw-semibold">Click or drag to upload ad image</p>
                        <p class="mb-0 text-secondary" style="font-size:0.8rem;">Recommended: {{ $size['w'] }}×{{ $size['h'] }}px · PNG, JPG, WebP · Max 2MB</p>
                    </div>
                </div>
                {{-- <small class="text-secondary d-block mt-2">Image will be normalized to {{ $size['w'] }}×{{ $size['h'] }} using Intervention on save.</small>
                <div id="uploadImagePositionControls" class="mt-3 d-none">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label mb-1">Image Left / Right</label>
                            <input type="range" id="uploadImagePosX" class="form-range" min="0" max="100" value="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-1">Image Top / Bottom</label>
                            <input type="range" id="uploadImagePosY" class="form-range" min="0" max="100" value="50">
                        </div>
                    </div>
                </div> --}}
            </div>

            <div id="customizeWrap" class="mb-3 d-none">
                <div class="customize-panel-header mb-3">
                    <h6 class="mb-1">Customize Ad Studio</h6>
                    <p class="mb-0">Build your ad with layered text and images. Tip: drag layers to reposition, and double-click text to edit.</p>
                </div>
                <div class="row g-3 customize-panel-grid">
                    <div class="col-12">
                        <div class="small text-uppercase fw-semibold text-secondary border-bottom pb-1 mb-0">Background & Layers</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Background Color</label>
                        <input type="color" id="adBgColorInput" class="form-control form-control-color w-100" value="#f7f7f7">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Background Image (optional)</label>
                        <input type="file" id="adBgImageInput" class="form-control" accept="image/png,image/jpeg,image/webp">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Clear Background</label>
                        <button type="button" class="btn btn-outline-secondary w-100 text-nowrap px-3" id="clearAdBgBtn">Remove BG Image</button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Add Text Block</label>
                        <button type="button" class="btn btn-outline-primary w-100 px-3" id="addTextBtn">+ Add Text</button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Add Images</label>
                        <input type="file" id="customImageInput" class="form-control ps-3" accept="image/png,image/jpeg,image/webp" multiple>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Remove Selected</label>
                        <button type="button" class="btn btn-outline-danger w-100" id="removeLayerBtn">Remove Layer</button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Layer Text</label>
                        <textarea id="layerTextInput" class="form-control ps-3" rows="2" maxlength="120" placeholder="Edit selected text block"></textarea>
                        <small class="text-secondary d-inline-block mt-1"><span id="layerTextCharCount">0</span>/120</small>
                    </div> 
                    <div class="col-12">
                        <div class="small text-uppercase fw-semibold text-secondary border-bottom pb-2 mb-1 mt-2">Text Styling</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Font Size</label>
                        <input type="number" id="layerFontSizeInput" class="form-control ps-3" min="10" max="180" value="30">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">Text Style</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="layerBoldInput">
                            <label class="form-check-label" for="layerBoldInput">Bold</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Text Color</label>
                        <input type="color" id="layerTextColorInput" class="form-control form-control-color w-100" value="#111111">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Alignment</label>
                        <select id="layerTextAlignInput" class="form-select ps-3">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label">Font Family</label>
                        <select id="layerFontFamilyInput" class="form-select ps-3">
                            <option value="Arial">Arial</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                            <option value="Trebuchet MS">Trebuchet MS</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="small text-uppercase fw-semibold text-secondary border-bottom pb-2 mb-1 mt-2">Image Sizing</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Image Width</label>
                        <input type="number" id="layerImageWidthInput" class="form-control ps-3" min="20" max="2000" value="220" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Image Height</label>
                        <input type="number" id="layerImageHeightInput" class="form-control ps-3" min="20" max="2000" value="220" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quick Scale</label>
                        <input type="range" id="layerImageScaleInput" class="form-range" min="5" max="100" step="1" value="25" disabled>
                    </div>
                </div>
            </div>

            <div class="border rounded p-2 bg-light d-none" id="canvasWrap" style="max-width:100%;">
                <div class="small text-secondary mb-1">Customization Canvas (edit directly on this area)</div>
                <div class="small text-muted mb-2">Exact export size: {{ $size['w'] }} × {{ $size['h'] }} px</div>
                <div class="ads-canvas-scroll" style="overflow:auto;max-width:100%;padding:6px;background:#eef1f5;border:1px dashed #c8ced8;border-radius:8px;">
                    <div class="ad-preview-frame" id="adPreviewFrame" data-source-width="{{ $size['w'] }}" data-source-height="{{ $size['h'] }}" style="width:{{ $size['w'] }}px;height:{{ $size['h'] }}px;min-width:{{ $size['w'] }}px;min-height:{{ $size['h'] }}px;box-shadow:0 4px 16px rgba(15,23,42,.18);position:relative;display:block;overflow:hidden;">
                        <div id="adPreview" class="ad-preview-canvas" style="position:relative;overflow:hidden;background:#f7f7f7;width:{{ $size['w'] }}px;height:{{ $size['h'] }}px;"></div>
                    </div>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="acceptTerms" name="accept_terms" value="1" required>
                <label class="form-check-label" for="acceptTerms">I agree to Terms and Conditions</label>
            </div>
            <p class="text-secondary small mt-2 mb-0">
                Note: Your ad will be sent to admin for verification. It will be published after approval.
            </p>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('ads.create.size') }}" class="btn btn-light px-4">Back</a>
                <button type="submit" id="adSubmitButton" class="btn btn-primary px-5">Save Ad</button>
            </div>
        </form>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        {{-- <div class="mt-4 p-3 border rounded bg-white">
            <h6 class="mb-3">Upload and Export PDF</h6>

            <input type="file" id="upload-image" class="form-control mb-3" accept="image/*">

            <div id="capture-area"
                style="width:879px;height:118px;margin:auto;border:1px solid #ccc;overflow:hidden;background:#f5f5f5;">

                <img id="preview-image"
                    src=""
                    style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
            </div>

            <button type="button" id="capture-screenshot" class="btn btn-danger mt-3">
                Download PDF
            </button>
        </div> --}}

    </div>
</div>
@endsection



@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAdLocationAutocomplete"></script>
<script>
let uploadedImage = null;

const addAdImageInput = document.getElementById('uploadImageInput');
    const addAdImageError = document.getElementById('adImageError');
    const dropzonePreview = document.getElementById('adDropzonePreview');
    const dropzonePreviewWrap = document.getElementById('adDropzonePreviewWrap');
    const dropzonePlaceholder = document.getElementById('adDropzonePlaceholder');

if (addAdImageInput && addAdImageError) {
    const requiredWidth = Number(addAdImageInput.dataset.requiredWidth || 0);
    const requiredHeight = Number(addAdImageInput.dataset.requiredHeight || 0);

    const showImageSizeError = (message) => {
        addAdImageError.textContent = message;
        addAdImageError.style.display = 'block';
    };

    const clearImageSizeError = () => {
        addAdImageError.textContent = '';
        addAdImageError.style.display = 'none';
    };

    addAdImageInput.addEventListener('change', function (event) {
        const activeMode = document.querySelector('input[name="design_mode"]:checked')?.value || 'upload';
        if (activeMode !== 'upload') return;

        clearImageSizeError();
        const file = event.target.files && event.target.files[0];
        if (!file) return;

        const img = new Image();
        const objectUrl = URL.createObjectURL(file);

        img.onload = function () {
            const isExactSize = img.naturalWidth === requiredWidth && img.naturalHeight === requiredHeight;
            URL.revokeObjectURL(objectUrl);

            if (!isExactSize) {
                showImageSizeError(`Invalid image size. Required size is exactly ${requiredWidth}×${requiredHeight} pixels.`);
                addAdImageInput.value = '';
                if (dropzonePreview) {
                    dropzonePreview.setAttribute('src', '#');
                }
                if (dropzonePreviewWrap) {
                    dropzonePreviewWrap.classList.add('d-none');
                }
                if (dropzonePlaceholder) {
                    dropzonePlaceholder.classList.remove('d-none');
                }
                // alert(`Please upload a new image with exact size ${requiredWidth}×${requiredHeight}px.`);
            }
        };

        img.onerror = function () {
            URL.revokeObjectURL(objectUrl);
            showImageSizeError('Unable to read this image. Please upload a valid image file.');
            addAdImageInput.value = '';
        };

        img.src = objectUrl;
    });
}

$('#upload-image').on('change', function (event) {
    const file = event.target.files[0];
    if (!file) return;

    const imageUrl = URL.createObjectURL(file);

    uploadedImage = new Image();

    uploadedImage.onload = function () {
        $('#preview-image').attr('src', imageUrl);
    };

    uploadedImage.src = imageUrl;
});

$('#capture-screenshot').on('click', function () {
    if (!uploadedImage) {
        console.warn('[PDFUpload] Please upload an image first.');
        return;
    }

    const finalWidth = 879;
    const finalHeight = 118;

    const canvas = document.createElement('canvas');
    canvas.width = finalWidth;
    canvas.height = finalHeight;

    const ctx = canvas.getContext('2d');

    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, finalWidth, finalHeight);

    const imgRatio = uploadedImage.naturalWidth / uploadedImage.naturalHeight;
    const boxRatio = finalWidth / finalHeight;

    let drawWidth, drawHeight, drawX, drawY;

    // object-fit: cover
    if (imgRatio > boxRatio) {
        drawHeight = finalHeight;
        drawWidth = drawHeight * imgRatio;
    } else {
        drawWidth = finalWidth;
        drawHeight = drawWidth / imgRatio;
    }

    drawX = (finalWidth - drawWidth) / 2;
    drawY = (finalHeight - drawHeight) / 2;

    ctx.drawImage(uploadedImage, drawX, drawY, drawWidth, drawHeight);

    const link = document.createElement('a');
    link.download = 'clear-image.png';
    link.href = canvas.toDataURL('image/png', 1.0);
    link.click();
});



function pushScreenshotToServer(dataURL) {
    $.ajax({
        url: "push-screenshot.php",
        type: "POST",
        data: {
            image: dataURL
        },
        success: function () {
            console.log('Screenshot pushed to server.');
        },
        error: function (xhr) {
            console.error('Upload failed:', xhr.responseText);
        }
    });
}

    (function () {
        const page = document.getElementById('adsSizeCustomizerPage');
        if (!page) return;
        const form = page.querySelector('form[action*="/dashboard/ads/create/"]');
        if (!form) return;

        const previewFrame = document.getElementById('adPreviewFrame');
        const preview = document.getElementById('adPreview');
        const canvasWrap = document.getElementById('canvasWrap');
        const customHtmlInput = document.getElementById('customHtmlInput');
        const generatedImageDataInput = document.getElementById('generatedImageDataInput');
        const uploadInput = document.getElementById('uploadImageInput');
        const dropzone = document.getElementById('adDropzone');
        const dropzonePreviewWrap = document.getElementById('adDropzonePreviewWrap');
        const dropzonePreview = document.getElementById('adDropzonePreview');
        const dropzonePlaceholder = document.getElementById('adDropzonePlaceholder');
        const uploadImagePositionControls = document.getElementById('uploadImagePositionControls');
        const uploadImagePosX = document.getElementById('uploadImagePosX');
        const uploadImagePosY = document.getElementById('uploadImagePosY');
        const categorySelect = document.getElementById('categorySelect');
        const subcategorySelect = document.getElementById('subcategorySelect');
        const submitButton = document.getElementById('adSubmitButton');
        const adImageInputType = document.getElementById('adImageInputType');
        const adBgColorInput = document.getElementById('adBgColorInput');
        const adBgImageInput = document.getElementById('adBgImageInput');
        const clearAdBgBtn = document.getElementById('clearAdBgBtn');
        const layerTextInput = document.getElementById('layerTextInput');
        const layerTextCharCount = document.getElementById('layerTextCharCount');
        const layerFontSizeInput = document.getElementById('layerFontSizeInput');
        const layerBoldInput = document.getElementById('layerBoldInput');
        const layerTextColorInput = document.getElementById('layerTextColorInput');
        const layerTextAlignInput = document.getElementById('layerTextAlignInput');
        const layerFontFamilyInput = document.getElementById('layerFontFamilyInput');
        const layerImageWidthInput = document.getElementById('layerImageWidthInput');
        const layerImageHeightInput = document.getElementById('layerImageHeightInput');
        const layerImageScaleInput = document.getElementById('layerImageScaleInput');

        const sizeW = Number(previewFrame?.dataset.sourceWidth || 0);
        const sizeH = Number(previewFrame?.dataset.sourceHeight || 0);
        let selectedLayer = null;
        let currentMode = 'upload';
        let uploadedImageFile = null;
        let uploadedImagePositionX = 50;
        let uploadedImagePositionY = 50;

        function toast(type, message) {
            const normalizedType = type === 'danger' ? 'error' : type;

            if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                window.FormHelper.showToast(normalizedType, message);
                return;
            }

            if (window.toastr && typeof window.toastr[normalizedType] === 'function') {
                window.toastr[normalizedType](message);
                return;
            }

            console[normalizedType === 'error' ? 'error' : 'log'](
                message || (normalizedType === 'error' ? 'Something went wrong.' : 'Done')
            );
        }

        function clearFieldErrors() {
            form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
            form.querySelectorAll('.js-inline-error').forEach((el) => el.remove());
            const adImageError = document.getElementById('adImageError');
            if (adImageError) {
                adImageError.textContent = '';
                adImageError.style.display = 'none';
            }
        }

        function showFieldError(fieldName, message) {
            if (!fieldName || !message) return;

            if (fieldName === 'generated_image_data' || fieldName === 'custom_html') {
                const adImageError = document.getElementById('adImageError');
                if (adImageError) {
                    adImageError.textContent = message;
                    adImageError.style.display = 'block';
                }
                return;
            }

            const normalizedFieldName = (fieldName === 'location_lat' || fieldName === 'location_lng') ? 'location' : fieldName;
            const field = form.querySelector(`[name="${normalizedFieldName}"]`);
            if (!field) {
                const adImageError = document.getElementById('adImageError');
                if (adImageError) {
                    adImageError.textContent = message;
                    adImageError.style.display = 'block';
                }
                return;
            }

            field.classList.add('is-invalid');
            const error = document.createElement('div');
            error.className = 'invalid-feedback d-block js-inline-error';
            error.textContent = message;
            field.insertAdjacentElement('afterend', error);
        }


        function updateSubmitButtonState() {
            if (!submitButton) return;
            submitButton.textContent = 'Save Ad';
        }


        function setSubmitLoadingState(isLoading) {
            if (!submitButton) return;

            if (!submitButton.dataset.defaultLabel) {
                submitButton.dataset.defaultLabel = submitButton.textContent.trim() || 'Save Ad';
            }

            submitButton.disabled = !!isLoading;
            if (isLoading) {
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';
                return;
            }

            updateSubmitButtonState();
        }

        function setMode(mode) {
            currentMode = mode === 'customize' ? 'customize' : 'upload';
            if (adImageInputType) {
                adImageInputType.value = currentMode === 'upload' ? '1' : '2';
            }
            document.getElementById('uploadWrap').classList.toggle('d-none', mode !== 'upload');
            document.getElementById('customizeWrap').classList.toggle('d-none', mode !== 'customize');
            document.querySelectorAll('.banner-mode-card').forEach((card) => card.classList.remove('is-active'));
            const activeCard = document.querySelector('input[name="design_mode"]:checked')?.closest('.banner-mode-option')?.querySelector('.banner-mode-card');
            if (activeCard) activeCard.classList.add('is-active');

            if (mode === 'customize') {
                canvasWrap.classList.remove('d-none');
                if (!preview.querySelector('[data-custom-stage="1"]')) {
                    selectedLayer = null;
                    preview.innerHTML = '';
                    const stage = document.createElement('div');
                    stage.setAttribute('data-custom-stage', '1');
                    stage.style.position = 'absolute';
                    stage.style.inset = '0';
                    stage.style.border = 'none';
                    stage.style.background = 'transparent';
                    stage.style.pointerEvents = 'none';
                    preview.appendChild(stage);
                }
            }
            else if (!preview.querySelector('img')) canvasWrap.classList.add('d-none');
        }

        function updateUploadedImagePosition() {
            const uploadedPreviewImage = preview.querySelector('img[data-upload-image="1"]');
            if (!uploadedPreviewImage) return;
            uploadedPreviewImage.style.objectPosition = `${uploadedImagePositionX}% ${uploadedImagePositionY}%`;
        }

        function isTextLayer(node) {
            return !!node && node.getAttribute('data-layer-type') === 'text';
        }

        function isImageLayer(node) {
            return !!node && node.getAttribute('data-layer-type') === 'image';
        }

        function updateControlPanelFromSelection() {
            if (!selectedLayer) {
                if (layerTextInput) layerTextInput.value = '';
                if (layerTextCharCount) layerTextCharCount.textContent = '0';
                if (layerImageWidthInput) layerImageWidthInput.disabled = true;
                if (layerImageHeightInput) layerImageHeightInput.disabled = true;
                if (layerImageScaleInput) layerImageScaleInput.disabled = true;
                return;
            }

            if (isTextLayer(selectedLayer)) {
                if (layerTextInput) layerTextInput.value = selectedLayer.textContent || '';
                if (layerTextCharCount) layerTextCharCount.textContent = String((selectedLayer.textContent || '').length);
                if (layerFontSizeInput) layerFontSizeInput.value = String(parseInt(selectedLayer.style.fontSize, 10) || 30);
                if (layerBoldInput) layerBoldInput.checked = (selectedLayer.style.fontWeight || '400') === '700';
                if (layerTextColorInput) layerTextColorInput.value = rgbToHex(selectedLayer.style.color) || '#111111';
                if (layerTextAlignInput) layerTextAlignInput.value = selectedLayer.style.textAlign || 'left';
                if (layerFontFamilyInput) layerFontFamilyInput.value = (selectedLayer.style.fontFamily || 'Arial').replace(/"/g, '');
            }

            if (layerImageWidthInput) layerImageWidthInput.disabled = !isImageLayer(selectedLayer);
            if (layerImageHeightInput) layerImageHeightInput.disabled = !isImageLayer(selectedLayer);
            if (layerImageScaleInput) layerImageScaleInput.disabled = !isImageLayer(selectedLayer);

            if (isImageLayer(selectedLayer)) {
                const w = selectedLayer.offsetWidth || 0;
                const h = selectedLayer.offsetHeight || 0;
                if (layerImageWidthInput) layerImageWidthInput.value = String(w);
                if (layerImageHeightInput) layerImageHeightInput.value = String(h);
                if (layerImageScaleInput && sizeW > 0) {
                    const scale = Math.round((w / sizeW) * 100);
                    layerImageScaleInput.value = String(Math.max(5, Math.min(100, scale)));
                }
            }
        }

        function rgbToHex(value) {
            if (!value) return '';
            if (value.startsWith('#')) return value;
            const match = value.match(/(\d+),\s*(\d+),\s*(\d+)/);
            if (!match) return '';
            return '#' + [match[1], match[2], match[3]]
                .map((num) => Number(num).toString(16).padStart(2, '0'))
                .join('');
        }

        document.querySelectorAll('input[name="design_mode"]').forEach((radio) => {
            radio.addEventListener('change', () => setMode(radio.value));
        });

        function makeDraggable(node) {
            let sx = 0, sy = 0, ox = 0, oy = 0, dragging = false;
            node.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return;
                if (node.getAttribute('data-editing') === '1') return;
                e.preventDefault();
                dragging = true;
                sx = e.clientX; sy = e.clientY;
                ox = parseFloat(node.style.left || '20');
                oy = parseFloat(node.style.top || '20');
                selectedLayer = node;
            });
            window.addEventListener('mousemove', (e) => {
                if (!dragging) return;
                node.style.left = Math.max(0, Math.min(sizeW - node.offsetWidth, ox + e.clientX - sx)) + 'px';
                node.style.top = Math.max(0, Math.min(sizeH - node.offsetHeight, oy + e.clientY - sy)) + 'px';
            });
            window.addEventListener('mouseup', () => dragging = false);
        }

        function addLayer(node) {
            node.style.position = 'absolute';
            node.style.left = '20px';
            node.style.top = '20px';
            node.style.zIndex = String(Date.now() % 100000);
            makeDraggable(node);
            node.addEventListener('click', (e) => { e.stopPropagation(); selectedLayer = node; });
            preview.appendChild(node);
            selectedLayer = node;
            updateControlPanelFromSelection();
        }

        document.getElementById('addTextBtn')?.addEventListener('click', () => {
            const t = document.createElement('div');
            t.textContent = 'Edit text';
            t.style.fontSize = '30px';
            t.style.fontWeight = '700';
            t.style.color = '#111';
            t.style.padding = '4px 6px';
            t.style.cursor = 'move';
            t.style.minWidth = '80px';
            t.setAttribute('contenteditable', 'false');
            t.setAttribute('data-editing', '0');
            t.setAttribute('data-layer-type', 'text');
            t.addEventListener('dblclick', () => {
                t.setAttribute('contenteditable', 'true');
                t.setAttribute('data-editing', '1');
                t.style.cursor = 'text';
                t.focus();
            });
            t.addEventListener('blur', () => {
                t.setAttribute('contenteditable', 'false');
                t.setAttribute('data-editing', '0');
                t.style.cursor = 'move';
                if (selectedLayer === t) updateControlPanelFromSelection();
            });
            t.addEventListener('input', () => {
                if (selectedLayer === t && layerTextInput) {
                    layerTextInput.value = t.textContent || '';
                    if (layerTextCharCount) layerTextCharCount.textContent = String((t.textContent || '').length);
                }
            });
            addLayer(t);
        });

        document.getElementById('customImageInput')?.addEventListener('change', (e) => {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;
            files.forEach((file) => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.width = Math.round(sizeW * 0.25) + 'px';
                img.style.height = 'auto';
                img.setAttribute('data-layer-type', 'image');
                addLayer(img);
            });
            e.target.value = '';
        });

        document.getElementById('removeLayerBtn')?.addEventListener('click', () => {
            if (!selectedLayer) return;
            selectedLayer.remove();
            selectedLayer = null;
            updateControlPanelFromSelection();
        });

        preview?.addEventListener('click', () => {
            selectedLayer = null;
            updateControlPanelFromSelection();
        });

        adBgColorInput?.addEventListener('input', function () {
            preview.style.backgroundColor = this.value || '#f7f7f7';
        });

        adBgImageInput?.addEventListener('change', function (event) {
            const file = event.target.files?.[0];
            if (!file) return;
            const imageUrl = URL.createObjectURL(file);
            preview.style.backgroundImage = `url(${imageUrl})`;
            preview.style.backgroundPosition = 'center';
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundRepeat = 'no-repeat';
        });

        clearAdBgBtn?.addEventListener('click', function () {
            preview.style.backgroundImage = 'none';
            if (adBgImageInput) adBgImageInput.value = '';
        });

        layerTextInput?.addEventListener('input', function () {
            if (!isTextLayer(selectedLayer)) return;
            selectedLayer.textContent = this.value.slice(0, 120);
            if (layerTextCharCount) layerTextCharCount.textContent = String(selectedLayer.textContent.length);
        });

        layerFontSizeInput?.addEventListener('input', function () {
            if (!isTextLayer(selectedLayer)) return;
            const size = Math.max(10, Math.min(180, Number(this.value) || 30));
            selectedLayer.style.fontSize = `${size}px`;
        });

        layerBoldInput?.addEventListener('change', function () {
            if (!isTextLayer(selectedLayer)) return;
            selectedLayer.style.fontWeight = this.checked ? '700' : '400';
        });

        layerTextColorInput?.addEventListener('input', function () {
            if (!isTextLayer(selectedLayer)) return;
            selectedLayer.style.color = this.value;
        });

        layerTextAlignInput?.addEventListener('change', function () {
            if (!isTextLayer(selectedLayer)) return;
            selectedLayer.style.textAlign = this.value;
        });

        layerFontFamilyInput?.addEventListener('change', function () {
            if (!isTextLayer(selectedLayer)) return;
            selectedLayer.style.fontFamily = this.value;
        });

        layerImageWidthInput?.addEventListener('input', function () {
            if (!isImageLayer(selectedLayer)) return;
            const width = Math.max(20, Number(this.value) || selectedLayer.offsetWidth);
            selectedLayer.style.width = `${width}px`;
            selectedLayer.style.height = 'auto';
            if (layerImageScaleInput && sizeW > 0) {
                layerImageScaleInput.value = String(Math.max(5, Math.min(100, Math.round((width / sizeW) * 100))));
            }
            if (layerImageHeightInput) layerImageHeightInput.value = String(selectedLayer.offsetHeight || 0);
        });

        layerImageHeightInput?.addEventListener('input', function () {
            if (!isImageLayer(selectedLayer)) return;
            const height = Math.max(20, Number(this.value) || selectedLayer.offsetHeight);
            selectedLayer.style.height = `${height}px`;
            selectedLayer.style.width = 'auto';
            if (layerImageWidthInput) layerImageWidthInput.value = String(selectedLayer.offsetWidth || 0);
            if (layerImageScaleInput && sizeW > 0) {
                layerImageScaleInput.value = String(Math.max(5, Math.min(100, Math.round(((selectedLayer.offsetWidth || 0) / sizeW) * 100))));
            }
        });

        layerImageScaleInput?.addEventListener('input', function () {
            if (!isImageLayer(selectedLayer)) return;
            const scalePercent = Math.max(5, Math.min(100, Number(this.value) || 25));
            const width = Math.round((sizeW * scalePercent) / 100);
            selectedLayer.style.width = `${width}px`;
            selectedLayer.style.height = 'auto';
            if (layerImageWidthInput) layerImageWidthInput.value = String(width);
            if (layerImageHeightInput) layerImageHeightInput.value = String(selectedLayer.offsetHeight || 0);
        });

        uploadInput?.addEventListener('change', (e) => {
            console.log('[AdUpload] File input changed.');
            const adImageError = document.getElementById('adImageError');
            const file = e.target.files?.[0];
            if (!file) {
                console.log('[AdUpload] No file was selected.');
                return;
            }
            const requiredWidth = Number(uploadInput.dataset.requiredWidth || 0);
            const requiredHeight = Number(uploadInput.dataset.requiredHeight || 0);
            const activeMode = document.querySelector('input[name="design_mode"]:checked')?.value || 'upload';
            console.log('[AdUpload] Required size:', requiredWidth + 'x' + requiredHeight, '| Mode:', activeMode);
            if (activeMode !== 'upload') {
                console.log('[AdUpload] Skipping validation because mode is not upload.');
                return;
            }

            if (adImageError) {
                adImageError.textContent = '';
                adImageError.style.display = 'none';
            }

            const objectUrl = URL.createObjectURL(file);
            const dimensionProbe = new Image();
            dimensionProbe.onload = () => {
                const isExactSize = dimensionProbe.naturalWidth === requiredWidth && dimensionProbe.naturalHeight === requiredHeight;
                console.log('[AdUpload] Selected image dimensions:', dimensionProbe.naturalWidth + 'x' + dimensionProbe.naturalHeight);
                if (!isExactSize) {
                    if (adImageError) {
                        adImageError.textContent = `Invalid image size. Please upload exact ${requiredWidth}×${requiredHeight}px image.`;
                        adImageError.style.display = 'block';
                    }
                    uploadInput.value = '';
                    uploadedImageFile = null;
                    URL.revokeObjectURL(objectUrl);
                    if (dropzonePreview && dropzonePreviewWrap && dropzonePlaceholder) {
                        dropzonePreview.src = '#';
                        dropzonePreviewWrap.classList.add('d-none');
                        dropzonePlaceholder.classList.remove('d-none');
                    }
                    preview.innerHTML = '';
                    preview.style.backgroundImage = 'none';
                    preview.style.backgroundColor = '#f7f7f7';
                    canvasWrap.classList.add('d-none');
                    uploadedImagePositionX = 50;
                    uploadedImagePositionY = 50;
                    if (uploadImagePosX) uploadImagePosX.value = '50';
                    if (uploadImagePosY) uploadImagePosY.value = '50';
                    uploadImagePositionControls?.classList.add('d-none');
                    console.warn('[AdUpload] Invalid image size, showing inline error and resetting selection.');
                    return;
                }

                console.log('[AdUpload] Valid image size. Rendering preview.');
                uploadedImageFile = file;
                if (dropzonePreview && dropzonePreviewWrap && dropzonePlaceholder) {
                    dropzonePreview.src = objectUrl;
                    dropzonePreviewWrap.classList.remove('d-none');
                    dropzonePlaceholder.classList.add('d-none');
                }

                preview.innerHTML = '';
                preview.style.backgroundImage = 'none';
                preview.style.backgroundColor = '#f7f7f7';
                const img = document.createElement('img');
                img.src = objectUrl;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.setAttribute('data-upload-image', '1');
                uploadedImagePositionX = 50;
                uploadedImagePositionY = 50;
                if (uploadImagePosX) uploadImagePosX.value = '50';
                if (uploadImagePosY) uploadImagePosY.value = '50';
                updateUploadedImagePosition();
                preview.appendChild(img);
                canvasWrap.classList.remove('d-none');
                uploadImagePositionControls?.classList.remove('d-none');
                console.log('[AdUpload] Preview render complete.');
            };
            dimensionProbe.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                uploadInput.value = '';
                uploadedImageFile = null;
                console.error('[AdUpload] Could not read selected image file.');
                if (adImageError) {
                    adImageError.textContent = 'Unable to read this image. Please upload a valid image.';
                    adImageError.style.display = 'block';
                }
            };
            dimensionProbe.src = objectUrl;
        });

        uploadImagePosX?.addEventListener('input', function () {
            uploadedImagePositionX = Math.max(0, Math.min(100, Number(this.value) || 50));
            updateUploadedImagePosition();
        });

        uploadImagePosY?.addEventListener('input', function () {
            uploadedImagePositionY = Math.max(0, Math.min(100, Number(this.value) || 50));
            updateUploadedImagePosition();
        });

        if (dropzone && uploadInput) {
            dropzone.addEventListener('click', () => {
                console.log('[AdUpload] Dropzone clicked, opening file browser.');
                uploadInput.click();
            });
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('is-dragover'); });
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('is-dragover'));
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
                console.log('[AdUpload] File dropped into dropzone.');
                const file = e.dataTransfer?.files?.[0];
                if (!file) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                uploadInput.files = dt.files;
                uploadInput.dispatchEvent(new Event('change'));
            });
        }

        async function exportPng() {
            if (!sizeW || !sizeH) return '';

            const canvas = document.createElement('canvas');
            const exportPixelRatio = 1;
            canvas.width = sizeW * exportPixelRatio;
            canvas.height = sizeH * exportPixelRatio;
            const ctx = canvas.getContext('2d');
            if (!ctx) return '';
            ctx.scale(exportPixelRatio, exportPixelRatio);

            const backgroundColor = adBgColorInput?.value || '#f7f7f7';
            ctx.fillStyle = backgroundColor;
            ctx.fillRect(0, 0, sizeW, sizeH);

            const drawImageCover = (img, x, y, w, h) => {
                const scale = Math.max(w / img.width, h / img.height);
                const drawWidth = img.width * scale;
                const drawHeight = img.height * scale;
                const dx = x + (w - drawWidth) / 2;
                const dy = y + (h - drawHeight) / 2;
                ctx.drawImage(img, dx, dy, drawWidth, drawHeight);
            };

            const loadImage = (src) => new Promise((resolve) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = () => resolve(null);
                img.src = src;
            });

            const bgMatch = (preview.style.backgroundImage || '').match(/url\(["']?(.*?)["']?\)/);
            if (bgMatch?.[1]) {
                const bgImg = await loadImage(bgMatch[1]);
                if (bgImg) drawImageCover(bgImg, 0, 0, sizeW, sizeH);
            }

            const layers = Array.from(preview.children).filter((node) => node.getAttribute('data-custom-stage') !== '1');
            for (const layer of layers) {
                const x = parseFloat(layer.style.left || '0');
                const y = parseFloat(layer.style.top || '0');
                const w = layer.offsetWidth || parseFloat(layer.style.width || '0');
                const h = layer.offsetHeight || parseFloat(layer.style.height || '0');

                if (isTextLayer(layer)) {
                    const fontSize = parseInt(layer.style.fontSize, 10) || 30;
                    const fontWeight = layer.style.fontWeight || '700';
                    const fontFamily = layer.style.fontFamily || 'Arial';
                    const lineHeight = Math.round(fontSize * 1.2);
                    ctx.font = `${fontWeight} ${fontSize}px ${fontFamily}`;
                    ctx.fillStyle = layer.style.color || '#111111';
                    ctx.textBaseline = 'top';
                    ctx.textAlign = (layer.style.textAlign || 'left');

                    const maxWidth = Math.max(20, w || Math.round(sizeW * 0.85));
                    const words = (layer.textContent || '').split(/\s+/).filter(Boolean);
                    const lines = [];
                    let line = '';
                    words.forEach((word) => {
                        const test = line ? `${line} ${word}` : word;
                        if (ctx.measureText(test).width <= maxWidth || !line) {
                            line = test;
                        } else {
                            lines.push(line);
                            line = word;
                        }
                    });
                    if (line) lines.push(line);
                    if (!lines.length) lines.push('');

                    let baseX = x;
                    if (ctx.textAlign === 'center') baseX = x + (maxWidth / 2);
                    if (ctx.textAlign === 'right') baseX = x + maxWidth;

                    lines.forEach((textLine, index) => {
                        ctx.fillText(textLine, baseX, y + (index * lineHeight));
                    });
                } else if (isImageLayer(layer)) {
                    const imgNode = layer.tagName === 'IMG' ? layer : layer.querySelector('img');
                    const src = imgNode?.getAttribute('src');
                    if (!src) continue;
                    const img = await loadImage(src);
                    if (img) {
                        const width = w || Math.round(sizeW * 0.25);
                        const height = h || Math.round((img.height / img.width) * width);
                        ctx.drawImage(img, x, y, width, height);
                    }
                } else if (layer.tagName === 'IMG') {
                    const src = layer.getAttribute('src');
                    if (!src) continue;
                    const img = await loadImage(src);
                    if (img) drawImageCover(img, 0, 0, sizeW, sizeH);
                }
            }

            return canvas.toDataURL('image/png');
        }

        async function exportUploadedFileAsPng(file) {
            if (!file || !sizeW || !sizeH) return '';

            return new Promise((resolve) => {
                const objectUrl = URL.createObjectURL(file);
                const image = new Image();
                image.onload = () => {
                    const canvas = document.createElement('canvas');
                    const exportPixelRatio = 1;
                    canvas.width = sizeW * exportPixelRatio;
                    canvas.height = sizeH * exportPixelRatio;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        URL.revokeObjectURL(objectUrl);
                        resolve('');
                        return;
                    }

                    ctx.fillStyle = '#f7f7f7';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    const targetW = canvas.width;
                    const targetH = canvas.height;
                    const scale = Math.max(targetW / image.width, targetH / image.height);
                    const drawWidth = image.width * scale;
                    const drawHeight = image.height * scale;
                    const maxOffsetX = Math.max(0, drawWidth - targetW);
                    const maxOffsetY = Math.max(0, drawHeight - targetH);
                    const cropOffsetX = (maxOffsetX * uploadedImagePositionX) / 100;
                    const cropOffsetY = (maxOffsetY * uploadedImagePositionY) / 100;
                    const dx = -cropOffsetX;
                    const dy = -cropOffsetY;

                    ctx.drawImage(image, dx, dy, drawWidth, drawHeight);
                    URL.revokeObjectURL(objectUrl);
                    resolve(canvas.toDataURL('image/png'));
                };
                image.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    resolve('');
                };
                image.src = objectUrl;
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }
            clearFieldErrors();
            setSubmitLoadingState(true);
            customHtmlInput.value = '<div class="ad-canvas" style="width:' + sizeW + 'px;height:' + sizeH + 'px;overflow:hidden;position:relative;">' + preview.innerHTML + '</div>';

            if (currentMode === 'upload' && uploadedImageFile) {
                generatedImageDataInput.value = await exportUploadedFileAsPng(uploadedImageFile);
            } else {
                generatedImageDataInput.value = await exportPng();
            }

            if (!generatedImageDataInput.value) {
                setSubmitLoadingState(false);
                return toast('danger', 'Could not generate ad image.');
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                let payload = {};
                try {
                    payload = await response.json();
                } catch (parseError) {
                    payload = {};
                }

                if (!response.ok) {
                    const errors = payload?.errors || {};
                    Object.keys(errors).forEach((key) => {
                        const messages = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
                        if (messages[0]) showFieldError(key, messages[0]);
                    });
                    setSubmitLoadingState(false);
                    return toast('danger', payload.message || 'Please fix the highlighted errors and try again.');
                }

                toast('success', payload.message || 'Saved successfully');

                const redirectUrl = payload?.redirect_url || form.dataset.redirectUrl || '';
                if (redirectUrl) {
                    setTimeout(() => {
                        window.location.assign(redirectUrl);
                    }, 1200);
                    return;
                }

                setSubmitLoadingState(false);
                return;
            } catch (networkError) {
                setSubmitLoadingState(false);
                toast('danger', 'Unable to save ad right now. Please try again.');
            }
        });

        async function loadSubcategories(categoryId) {
            const base = form.dataset.subcategoryUrlBase || '';
            if (!categoryId || !base || !subcategorySelect) {
                subcategorySelect.innerHTML = '<option value="">— Select a category first —</option>';
                subcategorySelect.disabled = true;
                return;
            }
            const response = await fetch(`${base}/${categoryId}/subcategories`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            const options = ['<option value="">— Select subcategory —</option>'];
            (Array.isArray(data) ? data : []).forEach((item) => {
                options.push(`<option value=\"${item.id}\">${item.name}</option>`);
            });
            subcategorySelect.innerHTML = options.join('');
            subcategorySelect.disabled = false;
            updateSubmitButtonState();
        }

        const categoryPriceNote = document.getElementById('adCategoryPriceNote');

        function updateCategoryPriceNote() {
            if (!categoryPriceNote || !categorySelect) return;
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const price = selectedOption ? selectedOption.dataset.adPrice : '';
            if (price === undefined || price === '') {
                categoryPriceNote.textContent = categorySelect.value ? 'No price is configured for this category and size.' : 'Select a category to see this size price.';
                return;
            }

            categoryPriceNote.textContent = `Price for this category and size: ₹${Number(price).toFixed(2)}`;
        }

        categorySelect?.addEventListener('change', function () {
            loadSubcategories(this.value);
            updateCategoryPriceNote();
            updateSubmitButtonState();
        });
        subcategorySelect?.addEventListener('change', updateSubmitButtonState);
        updateSubmitButtonState();

        window.initAdLocationAutocomplete = function () {
            const locationInput = document.getElementById('adLocation');
            const locationLatInput = document.getElementById('adLocationLat');
            const locationLngInput = document.getElementById('adLocationLng');
            if (!locationInput || !window.google || !google.maps || !google.maps.places) return;
            const autocomplete = new google.maps.places.Autocomplete(locationInput, { fields: ['formatted_address', 'geometry', 'name'] });
            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                const lat = place?.geometry?.location?.lat?.();
                const lng = place?.geometry?.location?.lng?.();
                locationInput.value = place?.formatted_address || place?.name || locationInput.value;
                if (locationLatInput) locationLatInput.value = typeof lat === 'number' ? String(lat) : '';
                if (locationLngInput) locationLngInput.value = typeof lng === 'number' ? String(lng) : '';
            });
        };

        setMode('upload');
        preview.style.backgroundColor = '#f7f7f7';
    })();
</script>
@endpush
