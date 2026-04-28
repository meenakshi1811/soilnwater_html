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

            <label class="form-label fw-semibold">
                Ad Image <span class="text-danger">*</span>
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
                <input type="file" id="uploadImageInput" class="d-none" accept="image/png,image/jpeg,image/webp">
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
                <small class="text-secondary d-block mt-2">Image will be normalized to {{ $size['w'] }}×{{ $size['h'] }} using Intervention on save.</small>
            </div>

            <div id="customizeWrap" class="mb-3 d-none">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Background Color</label>
                        <input type="color" id="adBgColorInput" class="form-control form-control-color w-100" value="#f7f7f7">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Background Image (optional)</label>
                        <input type="file" id="adBgImageInput" class="form-control" accept="image/png,image/jpeg,image/webp">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Clear Background</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearAdBgBtn">Remove BG Image</button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Add Text Block</label>
                        <button type="button" class="btn btn-outline-primary w-100" id="addTextBtn">+ Add Text</button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Add Images</label>
                        <input type="file" id="customImageInput" class="form-control" accept="image/png,image/jpeg,image/webp" multiple>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Remove Selected</label>
                        <button type="button" class="btn btn-outline-danger w-100" id="removeLayerBtn">Remove Layer</button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Layer Text</label>
                        <textarea id="layerTextInput" class="form-control" rows="2" maxlength="120" placeholder="Edit selected text block"></textarea>
                        <small class="text-secondary"><span id="layerTextCharCount">0</span>/120</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Font Size</label>
                        <input type="number" id="layerFontSizeInput" class="form-control" min="10" max="180" value="30">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">Text Style</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="layerBoldInput">
                            <label class="form-check-label" for="layerBoldInput">Bold</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Text Color</label>
                        <input type="color" id="layerTextColorInput" class="form-control form-control-color w-100" value="#111111">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Alignment</label>
                        <select id="layerTextAlignInput" class="form-select">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Font Family</label>
                        <select id="layerFontFamilyInput" class="form-select">
                            <option value="Arial">Arial</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                            <option value="Trebuchet MS">Trebuchet MS</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Image Width</label>
                        <input type="number" id="layerImageWidthInput" class="form-control" min="20" max="2000" value="220" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Image Height</label>
                        <input type="number" id="layerImageHeightInput" class="form-control" min="20" max="2000" value="220" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quick Scale</label>
                        <input type="range" id="layerImageScaleInput" class="form-range" min="5" max="100" step="1" value="25" disabled>
                    </div>
                </div>
            </div>

            <div class="border rounded p-2 bg-light d-none" id="canvasWrap" style="width:fit-content;max-width:100%;">
                <div class="small text-secondary mb-2">Final Ads Preview (what users will see)</div>
                <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] }};width:min(100%, {{ $size['w'] }}px);">
                    <div class="ads-live-preview-inner" id="adPreviewFrame" data-source-width="{{ $size['w'] }}" data-source-height="{{ $size['h'] }}">
                        <div id="adPreview" class="ads-mini-preview-inner" style="position:relative;overflow:hidden;background:#f7f7f7;width:{{ $size['w'] }}px;height:{{ $size['h'] }}px;"></div>
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
<script>
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
        const categorySelect = document.getElementById('categorySelect');
        const subcategorySelect = document.getElementById('subcategorySelect');
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

        function toast(type, message) {
            if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                window.FormHelper.showToast(type, message);
            } else {
                alert(message);
            }
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

        function setMode(mode) {
            currentMode = mode === 'customize' ? 'customize' : 'upload';
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
                    stage.style.border = '2px dashed rgba(47,125,225,.35)';
                    stage.style.background = 'rgba(255,255,255,.6)';
                    stage.style.pointerEvents = 'none';
                    preview.appendChild(stage);
                }
            }
            else if (!preview.querySelector('img')) canvasWrap.classList.add('d-none');
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
                if (e.target.closest('[contenteditable="true"]')) return;
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
            t.setAttribute('contenteditable', 'true');
            t.setAttribute('data-layer-type', 'text');
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
            const file = e.target.files?.[0];
            if (!file) return;
            uploadedImageFile = file;
            const objectUrl = URL.createObjectURL(file);

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
            preview.appendChild(img);
            canvasWrap.classList.remove('d-none');
        });

        if (dropzone && uploadInput) {
            dropzone.addEventListener('click', () => uploadInput.click());
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('is-dragover'); });
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('is-dragover'));
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
                const file = e.dataTransfer?.files?.[0];
                if (!file) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                uploadInput.files = dt.files;
                uploadInput.dispatchEvent(new Event('change'));
            });
        }

        async function exportPng() {
            try {
                if (window.htmlToImage?.toPng) {
                    return await window.htmlToImage.toPng(preview, { pixelRatio: 1, canvasWidth: sizeW, canvasHeight: sizeH, cacheBust: true, skipFonts: true, fontEmbedCSS: '' });
                }
            } catch (error) {
                console.warn('html-to-image export failed, falling back to html2canvas.', error);
            }

            try {
                if (window.html2canvas) {
                    const canvas = await window.html2canvas(preview, { width: sizeW, height: sizeH, windowWidth: sizeW, windowHeight: sizeH, scale: 1, useCORS: true });
                    return canvas.toDataURL('image/png');
                }
            } catch (error) {
                console.warn('html2canvas export failed.', error);
            }

            return '';
        }

        async function exportUploadedFileAsPng(file) {
            if (!file || !sizeW || !sizeH) return '';

            return new Promise((resolve) => {
                const objectUrl = URL.createObjectURL(file);
                const image = new Image();
                image.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = sizeW;
                    canvas.height = sizeH;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        URL.revokeObjectURL(objectUrl);
                        resolve('');
                        return;
                    }

                    ctx.fillStyle = '#f7f7f7';
                    ctx.fillRect(0, 0, sizeW, sizeH);

                    const scale = Math.max(sizeW / image.width, sizeH / image.height);
                    const drawWidth = image.width * scale;
                    const drawHeight = image.height * scale;
                    const dx = (sizeW - drawWidth) / 2;
                    const dy = (sizeH - drawHeight) / 2;

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
            clearFieldErrors();
            customHtmlInput.value = '<div class="ad-canvas" style="width:' + sizeW + 'px;height:' + sizeH + 'px;overflow:hidden;position:relative;">' + preview.innerHTML + '</div>';

            if (currentMode === 'upload' && uploadedImageFile) {
                generatedImageDataInput.value = await exportUploadedFileAsPng(uploadedImageFile);
            } else {
                generatedImageDataInput.value = await exportPng();
            }

            if (!generatedImageDataInput.value) return toast('danger', 'Could not generate ad image.');

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
                    return toast('danger', payload.message || 'Please fix the highlighted errors and try again.');
                }

                toast('success', payload.message || 'Saved successfully');
                setTimeout(() => { window.location.href = payload.redirect_url || '{{ route('ads.index') }}'; }, 700);
            } catch (networkError) {
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
            (Array.isArray(data) ? data : []).forEach((item) => options.push(`<option value=\"${item.id}\">${item.name}</option>`));
            subcategorySelect.innerHTML = options.join('');
            subcategorySelect.disabled = false;
        }

        categorySelect?.addEventListener('change', function () { loadSubcategories(this.value); });

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
