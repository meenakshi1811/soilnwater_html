@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    #adsSizeCustomizerPage .select2-container { width: 100% !important; }
    #adsSizeCustomizerPage .select2-container--default .select2-selection--multiple {
        min-height: calc(2.25rem + 2px);
        border: 1px solid #ced4da;
        border-radius: .375rem;
        padding: .25rem .25rem;
    }
    #adsSizeCustomizerPage .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: .2rem;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page" id="adsSizeCustomizerPage">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">{{ !empty($isEdit) ? 'Edit Your Ad' : 'Create Your Ad' }}</h2>
            <p class="mb-0 text-secondary">Selected size: <strong>{{ $size['name'] }}</strong> ({{ $size['w'] }}×{{ $size['h'] }} px)</p>
        </div>
    </div>

    <div class="chart-card">
        <form method="POST" action="{{ !empty($isEdit) ? route('ads.update', $ad) : route('ads.store', ['sizeType' => $sizeType, 'template' => $template->id??null]) }}" novalidate data-subcategory-url-base="{{ url('/dashboard/ads/categories') }}" data-category-filter-url="{{ route('ads.categories.by-modules') }}">
            @csrf
            @if(!empty($isEdit)) @method('PUT') @endif
            <input type="hidden" name="custom_html" id="customHtmlInput" value="">
            <input type="hidden" name="generated_image_data" id="generatedImageDataInput" value="">
            <input type="hidden" name="ad_image_input_type" id="adImageInputType" value="1">
            <input type="hidden" id="existingFinalImage" value="{{ !empty($isEdit) && !empty($ad?->final_image) ? asset($ad->final_image) : "" }}">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold required-label">Ad Title <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <input type="text" name="title" value="{{ old('title', $ad->title ?? '') }}" class="form-control" maxlength="140" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Short Description</label>
                    <textarea name="short_description" class="form-control" rows="2" maxlength="300" placeholder="Write a short summary for this ad (max 300 characters)...">{{ old('short_description', $ad->short_description ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="moduleSelect" class="form-label fw-semibold">Select Module(s)</label>
                    <select name="selected_modules[]" id="moduleSelect" class="form-select" multiple data-module-prices='@json($size["module_prices"] ?? [])'>
                        @foreach(($moduleOptions ?? []) as $moduleKey => $moduleName)
                            @php $modulePrice = $size['module_prices'][$moduleKey] ?? null; @endphp
                            <option value="{{ $moduleKey }}" data-module-price="{{ $modulePrice !== null ? (float) $modulePrice : '' }}" {{ in_array($moduleKey, old('selected_modules', $ad->selected_modules ?? []), true) ? 'selected' : '' }}>
                                {{ $moduleName }}{{ $modulePrice !== null && $modulePrice > 0 ? ' (₹' . number_format((float) $modulePrice, 2) . '/day)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div id="adModulePriceNote" class="form-text text-muted">Select one or more modules to include additional paid module charges.</div>
                </div>
                <div class="col-md-6">
                    <label for="categorySelect" class="form-label fw-semibold required-label">Category <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <select name="category_id" id="categorySelect" class="form-select" {{ empty(old('selected_modules', $ad->selected_modules ?? [])) ? 'disabled' : '' }} required>
                        <option value="">— Select category —</option>
                        @foreach($categories as $category)
                            @php $categoryPrice = $size['category_prices'][$category->id] ?? null; @endphp
                            <option value="{{ $category->id }}" data-ad-price="{{ $categoryPrice !== null ? (float) $categoryPrice : '' }}" data-modules="{{ e(json_encode(array_values(array_filter($category->modules ?? [], fn($module) => $module !== 'ads')))) }}" {{ (string) old('category_id', $ad->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @if((bool) ($size['is_paid'] ?? false))
                        <div id="adCategoryPriceNote" class="form-text text-muted">Select a category to see this size price.</div>
                        <div id="adCategoryPremiumChip" class="d-none mt-2">
                            <span class="badge rounded-pill px-3 py-2 fw-semibold text-warning-emphasis bg-warning-subtle border border-warning-subtle">
                                <i class="fa-solid fa-crown me-1" aria-hidden="true"></i>
                                Premium • ₹0.00
                            </span>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label for="subcategorySelect" class="form-label fw-semibold required-label">Sub Category <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <select name="subcategory_id" id="subcategorySelect" class="form-select" {{ empty($subcategories ?? []) ? 'disabled' : '' }} required>
                        <option value="">— Select a category first —</option>
                        @foreach(($subcategories ?? []) as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ (string) old('subcategory_id', $ad->subcategory_id ?? '') === (string) $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold required-label">Location <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <input type="text" name="location" id="adLocation" class="form-control" value="{{ old('location', $ad->location ?? '') }}" required>
                    <input type="hidden" name="location_lat" id="adLocationLat" value="{{ old('location_lat', $ad->location_lat ?? '') }}">
                    <input type="hidden" name="location_lng" id="adLocationLng" value="{{ old('location_lng', $ad->location_lng ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold required-label">Valid Upto <i class="fa-solid fa-asterisk required-icon" aria-hidden="true"></i></label>
                    <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until', optional($ad->valid_until ?? null)->format('Y-m-d')) }}" min="{{ now()->toDateString() }}" {{ $sizeType === "square" ? "max=\"" . now()->addDays(30)->toDateString() . "\"" : "" }} required>
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
                    <div id="adDropzonePreviewWrap" class="{{ !empty($isEdit) && !empty($ad?->final_image) ? "" : "d-none" }} position-relative">
                        <img id="adDropzonePreview" src="{{ !empty($isEdit) && !empty($ad?->final_image) ? asset($ad->final_image) : "#" }}" alt="Ad image preview" class="banner-preview-img">
                    </div>
                    <div id="adDropzonePlaceholder" class="banner-placeholder-content {{ !empty($isEdit) && !empty($ad?->final_image) ? "d-none" : "" }}">
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
                <label class="form-check-label" for="acceptTerms">I agree to <a href="{{ route('frontend.terms.show', ['moduleKey' => 'ads']) }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-decoration-underline" style="color:#0d6efd;">Terms and Conditions</a></label>
            </div>
            <p class="text-secondary small mt-2 mb-0">
                Note: Your ad will be sent to admin for verification. It will be published after approval.
            </p>
            <p class="text-secondary small mt-2 mb-0">
                Need help? <a href="#" data-bs-toggle="modal" data-bs-target="#contactSupportModal" class="fw-semibold">Contact support</a>.
            </p>

            <div class="modal fade" id="contactSupportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Contact Support</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="contactSupportAlert" class="alert d-none" role="alert"></div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" id="contactSupportSubject" class="form-control" maxlength="150" placeholder="Briefly describe your issue">
                            </div>
                            <div>
                                <label class="form-label">Message</label>
                                <textarea id="contactSupportMessage" class="form-control" rows="4" maxlength="2000" placeholder="Write your query"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="contactSupportSendBtn" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </div>
            </div>

            @if((bool) ($size['is_paid'] ?? false))
                <div id="pricingDetailsCard" class="mt-4 rounded-4 border p-4 d-none" style="background:#f5f2ec;border-color:#f1bb86 !important;">
                    <h4 class="mb-3">Pricing Details</h4>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Category price / day</span>
                        <strong id="pricingCategoryPrice">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Module price / day</span>
                        <strong id="pricingModulePrice">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Base price / day (Category + Module)</span>
                        <strong id="pricingBasePrice">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total days</span>
                        <strong id="pricingTotalDays">1</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Subtotal (Base × Days)</span>
                        <strong id="pricingSubtotal">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>GST (5%)</span>
                        <strong id="pricingGst">₹0.00</strong>
                    </div>
                    <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                        <strong class="fs-4">Grand Total</strong>
                        <strong class="fs-3" id="pricingGrandTotal">₹0.00</strong>
                    </div>
                    <p id="pricingHint" class="text-secondary mb-0 mt-3">Valid until is not selected, so GST is calculated on the standard 1-day base price.</p>
                </div>
            @endif

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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAdLocationAutocomplete"></script>
<script>
let uploadedImage = null;

    const contactSendBtn = document.getElementById('contactSupportSendBtn');
    const contactAlert = document.getElementById('contactSupportAlert');
    contactSendBtn?.addEventListener('click', async function () {
        const subject = document.getElementById('contactSupportSubject')?.value?.trim() || '';
        const message = document.getElementById('contactSupportMessage')?.value?.trim() || '';

        contactAlert.className = 'alert d-none';
        if (!subject || !message) {
            contactAlert.className = 'alert alert-danger';
            contactAlert.textContent = 'Please fill subject and message.';
            return;
        }

        this.disabled = true;
        try {
            const response = await fetch("{{ route('ads.contact-support') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ subject, message })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Failed to send request.');

            document.getElementById('contactSupportSubject').value = '';
            document.getElementById('contactSupportMessage').value = '';
            if (contactAlert) {
                contactAlert.className = 'alert d-none';
                contactAlert.textContent = '';
            }
            toast('success', data.message || 'Support request sent successfully.');
            const modalEl = document.getElementById('contactSupportModal');
            if (window.bootstrap?.Modal && modalEl) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
        } catch (err) {
            contactAlert.className = 'alert alert-danger';
            contactAlert.textContent = err.message || 'Failed to send support request.';
        } finally {
            this.disabled = false;
        }
    });

const addAdImageInput = document.getElementById('uploadImageInput');
    const addAdImageError = document.getElementById('adImageError');
    const dropzonePreview = document.getElementById('adDropzonePreview');
    const dropzonePreviewWrap = document.getElementById('adDropzonePreviewWrap');
    const dropzonePlaceholder = document.getElementById('adDropzonePlaceholder');

if (addAdImageInput && addAdImageError) {
    const requiredWidth = Number(addAdImageInput.dataset.requiredWidth || 0);
    const requiredHeight = Number(addAdImageInput.dataset.requiredHeight || 0);

   

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

            
        };

        img.onerror = function () {
            URL.revokeObjectURL(objectUrl);
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
        const form = page.querySelector('form');
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

                const existingFinalImage = document.getElementById('existingFinalImage')?.value || '';
        if (existingFinalImage && dropzonePreview && dropzonePreviewWrap && dropzonePlaceholder) {
            dropzonePreview.src = existingFinalImage;
            dropzonePreviewWrap.classList.remove('d-none');
            dropzonePlaceholder.classList.add('d-none');
            canvasWrap.classList.remove('d-none');
            preview.innerHTML = `<img data-upload-image="1" src="${existingFinalImage}" alt="Ad Preview" style="width:100%;height:100%;object-fit:cover;object-position:${uploadedImagePositionX}% ${uploadedImagePositionY}%;pointer-events:none;">`;
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
                img.style.objectFit = 'contain';
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
            const exportPixelRatio = 3;
            canvas.width = sizeW * exportPixelRatio;
            canvas.height = sizeH * exportPixelRatio;
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.scale(exportPixelRatio, exportPixelRatio);
            // if (!ctx) return '';
            // ctx.scale(exportPixelRatio, exportPixelRatio);

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
            if (!file) return '';

            return new Promise((resolve) => {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const src = e?.target?.result;
                    if (!src) {
                        resolve('');
                        return;
                    }

                    const image = new Image();
                    image.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = image.naturalWidth || image.width || sizeW || 1;
                        canvas.height = image.naturalHeight || image.height || sizeH || 1;

                        const context = canvas.getContext('2d');
                        if (!context) {
                            resolve('');
                            return;
                        }

                        context.drawImage(image, 0, 0, canvas.width, canvas.height);
                        resolve(canvas.toDataURL('image/png'));
                    };
                    image.onerror = () => resolve('');
                    image.src = src;
                };

                reader.onerror = function () {
                    resolve('');
                };

                reader.readAsDataURL(file);
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

        function initModuleSelect2() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2 || !moduleSelect) return;
            const $moduleSelect = window.jQuery(moduleSelect);
            if ($moduleSelect.data('select2')) {
                $moduleSelect.select2('destroy');
            }
            $moduleSelect.select2({
                width: '100%',
                placeholder: 'Select module(s)',
                closeOnSelect: false
            });
        }

        const categoryPriceNote = document.getElementById('adCategoryPriceNote');
        const adCategoryPremiumChip = document.getElementById('adCategoryPremiumChip');
        const moduleSelect = document.getElementById('moduleSelect');
        const adModulePriceNote = document.getElementById('adModulePriceNote');
        const pricingCategoryPrice = document.getElementById('pricingCategoryPrice');
        const pricingModulePrice = document.getElementById('pricingModulePrice');
        const validUntilInput = document.querySelector('input[name="valid_until"]');
        const pricingBasePrice = document.getElementById('pricingBasePrice');
        const pricingTotalDays = document.getElementById('pricingTotalDays');
        const pricingSubtotal = document.getElementById('pricingSubtotal');
        const pricingGst = document.getElementById('pricingGst');
        const pricingGrandTotal = document.getElementById('pricingGrandTotal');
        const pricingHint = document.getElementById('pricingHint');
        const pricingDetailsCard = document.getElementById('pricingDetailsCard');
        const isSquareSizeType = @json($sizeType === 'square');

        const allCategoryOptions = categorySelect
            ? Array.from(categorySelect.querySelectorAll('option')).map((option) => ({
                value: option.value,
                label: option.textContent,
                price: option.dataset.adPrice || '',
                modules: option.dataset.modules || '[]',
                selected: option.selected,
            }))
            : [];

        function normalizeModuleKey(value) {
            return String(value || '')
                .trim()
                .toLowerCase()
                .replace(/&/g, 'and')
                .replace(/[^a-z0-9]+/g, '');
        }

        async function filterCategoriesByModules() {
            if (!categorySelect || !moduleSelect) return;
            const selectedModules = Array.from(moduleSelect.selectedOptions || []).map((option) => normalizeModuleKey(option.value)).filter(Boolean);
            console.log('[AdsCustomize] filterCategoriesByModules selectedModules:', selectedModules);
            const currentCategory = categorySelect.value;

            if (selectedModules.length === 0) {
                categorySelect.innerHTML = '<option value="">— Select module(s) first —</option>';
                categorySelect.disabled = true;
                categorySelect.value = '';
                if (subcategorySelect) {
                    subcategorySelect.innerHTML = '<option value="">— Select a category first —</option>';
                    subcategorySelect.disabled = true;
                }
                updateCategoryPriceNote();
                updateSubmitButtonState();
                return;
            }

            const filterUrl = form.dataset.categoryFilterUrl || '';
            console.log('[AdsCustomize] category filter URL:', filterUrl);
            try {
                const requestUrl = `${filterUrl}?${new URLSearchParams(selectedModules.map((module) => ['modules[]', module]))}`;
                console.log('[AdsCustomize] fetching categories:', requestUrl);
                const response = await fetch(requestUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                console.log('[AdsCustomize] categories response status:', response.status, 'payload:', data);
                const allowedIds = new Set((Array.isArray(data) ? data : []).map((item) => String(item.id)));
                const options = ['<option value="">— Select category —</option>'];

                allCategoryOptions.forEach((item) => {
                    if (!item.value || !allowedIds.has(String(item.value))) return;
                    const isSelected = String(item.value) === String(currentCategory);
                    options.push(`<option value="${item.value}" data-ad-price="${item.price}" data-modules="${item.modules}" ${isSelected ? 'selected' : ''}>${item.label}</option>`);
                });

                categorySelect.innerHTML = options.join('');
                categorySelect.disabled = false;

                if (currentCategory && !Array.from(categorySelect.options).some((option) => option.value === currentCategory)) {
                    categorySelect.value = '';
                    if (subcategorySelect) {
                        subcategorySelect.innerHTML = '<option value="">— Select a category first —</option>';
                        subcategorySelect.disabled = true;
                    }
                }
            } catch (error) {
                console.error('[AdsCustomize] categories fetch failed:', error);
                categorySelect.innerHTML = '<option value="">— Unable to load categories —</option>';
                categorySelect.disabled = true;
            }

            updateCategoryPriceNote();
            updateSubmitButtonState();
        }

        function applyValidUntilLimit() {
            if (!validUntilInput) return;
            if (!isSquareSizeType) {
                validUntilInput.removeAttribute('max');
                return;
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const maxDate = new Date(today);
            maxDate.setDate(maxDate.getDate() + 30);
            const maxDateIso = maxDate.toISOString().slice(0, 10);
            validUntilInput.setAttribute('max', maxDateIso);

            if (validUntilInput.value && validUntilInput.value > maxDateIso) {
                validUntilInput.value = maxDateIso;
            }
        }


        function calculateValidDays() {
            if (!validUntilInput || !validUntilInput.value) return { days: 1, usedFallback: true };
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selectedDate = new Date(`${validUntilInput.value}T00:00:00`);
            if (Number.isNaN(selectedDate.getTime()) || selectedDate < today) {
                return { days: 1, usedFallback: true };
            }
            const diffMs = selectedDate.getTime() - today.getTime();
            const diffDays = Math.floor(diffMs / 86400000) + 1;
            return { days: Math.max(1, diffDays), usedFallback: false };
        }

        function selectedModulePricePerDay() {
            if (!moduleSelect) return 0;
            return Array.from(moduleSelect.selectedOptions || []).reduce((total, option) => {
                const amount = Number(option.dataset.modulePrice || 0);
                return total + (Number.isFinite(amount) ? amount : 0);
            }, 0);
        }

        function updatePricingDetails(categoryPrice, modulePrice = selectedModulePricePerDay()) {
            if (!pricingBasePrice || !pricingTotalDays || !pricingSubtotal || !pricingGst || !pricingGrandTotal) return;
            const normalizedCategoryPrice = Number.isFinite(Number(categoryPrice)) && Number(categoryPrice) > 0 ? Number(categoryPrice) : 0;
            const normalizedModulePrice = Number.isFinite(Number(modulePrice)) && Number(modulePrice) > 0 ? Number(modulePrice) : 0;
            const normalizedBasePrice = normalizedCategoryPrice + normalizedModulePrice;

            if (pricingDetailsCard) {
                pricingDetailsCard.classList.toggle('d-none', normalizedBasePrice <= 0);
            }
            const { days, usedFallback } = calculateValidDays();
            const subtotal = normalizedBasePrice * days;
            const gst = subtotal * 0.05;
            const grandTotal = subtotal + gst;

            if (pricingCategoryPrice) pricingCategoryPrice.textContent = `₹${normalizedCategoryPrice.toFixed(2)}`;
            if (pricingModulePrice) pricingModulePrice.textContent = `₹${normalizedModulePrice.toFixed(2)}`;
            pricingBasePrice.textContent = `₹${normalizedBasePrice.toFixed(2)}`;
            pricingTotalDays.textContent = String(days);
            pricingSubtotal.textContent = `₹${subtotal.toFixed(2)}`;
            pricingGst.textContent = `₹${gst.toFixed(2)}`;
            pricingGrandTotal.textContent = `₹${grandTotal.toFixed(2)}`;

            if (pricingHint) {
                pricingHint.textContent = usedFallback
                    ? 'Valid until is not selected, so GST is calculated on the standard 1-day base price.'
                    : `Valid upto is ${days} day${days > 1 ? 's' : ''}.`;
            }
        }

        function updateCategoryPriceNote() {
            if (!categoryPriceNote || !categorySelect) return;
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const price = selectedOption ? selectedOption.dataset.adPrice : '';
            if (price === undefined || price === '') {
                categoryPriceNote.textContent = categorySelect.value ? 'No price is configured for this category and size.' : 'Select a category to see this size price.';
                adCategoryPremiumChip?.classList.add('d-none');
                updatePricingDetails(0);
                return;
            }

            const formattedPrice = Number(price).toFixed(2);
            categoryPriceNote.textContent = `Price for this category and size: ₹${formattedPrice}`;
            if (adCategoryPremiumChip) {
                adCategoryPremiumChip.classList.remove('d-none');
                adCategoryPremiumChip.innerHTML = `<span class="badge rounded-pill px-3 py-2 fw-semibold text-warning-emphasis bg-warning-subtle border border-warning-subtle"><i class="fa-solid fa-crown me-1" aria-hidden="true"></i> Premium • ₹${formattedPrice}</span>`;
            }
            updatePricingDetails(price);
        }

        function updateModulePriceNote() {
            if (!adModulePriceNote) return;
            const modulePrice = selectedModulePricePerDay();
            adModulePriceNote.textContent = modulePrice > 0
                ? `Selected module charges: ₹${modulePrice.toFixed(2)}/day`
                : 'Selected modules are free for this size.';
            const selectedOption = categorySelect?.options[categorySelect.selectedIndex];
            updatePricingDetails(selectedOption ? selectedOption.dataset.adPrice : 0, modulePrice);
        }

        initModuleSelect2();

        categorySelect?.addEventListener('change', function () {
            loadSubcategories(this.value);
            updateCategoryPriceNote();
            updateSubmitButtonState();
        });
        function handleModuleSelectionChange(eventSource = 'native') {
            console.log('[AdsCustomize] module selection changed via:', eventSource);
            filterCategoriesByModules();
            updateModulePriceNote();
        }

        moduleSelect?.addEventListener('change', function () {
            handleModuleSelectionChange('native-change');
        });

        if (window.jQuery && moduleSelect) {
            const $moduleSelect = window.jQuery(moduleSelect);
            $moduleSelect.on('change', function () {
                handleModuleSelectionChange('jquery-change');
            });
            $moduleSelect.on('select2:select select2:unselect select2:clear', function (event) {
                handleModuleSelectionChange(`select2-${event.type}`);
            });
        }
        subcategorySelect?.addEventListener('change', updateSubmitButtonState);
        validUntilInput?.addEventListener('change', () => {
            updateCategoryPriceNote();
            updateModulePriceNote();
        });
        applyValidUntilLimit();
        filterCategoriesByModules();
        updateCategoryPriceNote();
        updateModulePriceNote();
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
