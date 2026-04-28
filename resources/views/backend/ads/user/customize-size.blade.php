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
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addTextBtn">Add Text</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addImageBtn">Add Image</button>
                    <input type="file" id="customImageInput" class="d-none" accept="image/png,image/jpeg,image/webp">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="removeLayerBtn">Remove Selected</button>
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

        const sizeW = Number(previewFrame?.dataset.sourceWidth || 0);
        const sizeH = Number(previewFrame?.dataset.sourceHeight || 0);
        let selectedLayer = null;

        function toast(type, message) {
            if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                window.FormHelper.showToast(type, message);
            } else {
                alert(message);
            }
        }

        function setMode(mode) {
            document.getElementById('uploadWrap').classList.toggle('d-none', mode !== 'upload');
            document.getElementById('customizeWrap').classList.toggle('d-none', mode !== 'customize');
            document.querySelectorAll('.banner-mode-card').forEach((card) => card.classList.remove('is-active'));
            const activeCard = document.querySelector('input[name="design_mode"]:checked')?.closest('.banner-mode-option')?.querySelector('.banner-mode-card');
            if (activeCard) activeCard.classList.add('is-active');

            if (mode === 'customize') {
                canvasWrap.classList.remove('d-none');
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
            else if (!preview.querySelector('img')) canvasWrap.classList.add('d-none');
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
        }

        document.getElementById('addTextBtn')?.addEventListener('click', () => {
            const t = document.createElement('div');
            t.textContent = 'Edit text';
            t.style.fontSize = '30px';
            t.style.fontWeight = '700';
            t.style.color = '#111';
            t.style.padding = '4px 6px';
            t.setAttribute('contenteditable', 'true');
            addLayer(t);
        });

        document.getElementById('addImageBtn')?.addEventListener('click', () => document.getElementById('customImageInput')?.click());
        document.getElementById('customImageInput')?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = Math.round(sizeW * 0.25) + 'px';
            addLayer(img);
            e.target.value = '';
        });

        document.getElementById('removeLayerBtn')?.addEventListener('click', () => {
            if (!selectedLayer) return;
            selectedLayer.remove();
            selectedLayer = null;
        });

        uploadInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const objectUrl = URL.createObjectURL(file);

            if (dropzonePreview && dropzonePreviewWrap && dropzonePlaceholder) {
                dropzonePreview.src = objectUrl;
                dropzonePreviewWrap.classList.remove('d-none');
                dropzonePlaceholder.classList.add('d-none');
            }

            preview.innerHTML = '';
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
            if (window.htmlToImage?.toPng) {
                return window.htmlToImage.toPng(preview, { pixelRatio: 1, canvasWidth: sizeW, canvasHeight: sizeH, cacheBust: true, skipFonts: true, fontEmbedCSS: '' });
            }
            if (window.html2canvas) {
                const canvas = await window.html2canvas(preview, { width: sizeW, height: sizeH, windowWidth: sizeW, windowHeight: sizeH, scale: 1, useCORS: true });
                return canvas.toDataURL('image/png');
            }
            return '';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            customHtmlInput.value = '<div class="ad-canvas" style="width:' + sizeW + 'px;height:' + sizeH + 'px;overflow:hidden;position:relative;">' + preview.innerHTML + '</div>';
            generatedImageDataInput.value = await exportPng();
            if (!generatedImageDataInput.value) return toast('danger', 'Could not generate ad image.');

            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const payload = await response.json();
            if (!response.ok) return toast('danger', payload.message || 'Unable to save ad.');
            toast('success', payload.message || 'Saved successfully');
            setTimeout(() => { window.location.href = payload.redirect_url || '{{ route('ads.index') }}'; }, 700);
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
    })();
</script>
@endpush
