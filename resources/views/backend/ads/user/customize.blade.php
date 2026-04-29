@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@php
    $schema = is_array($template->schema_json) ? $template->schema_json : [];
    $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
    $sampleDefaults = \App\Support\AdTemplatePreview::sampleFieldsForSchema($fields, (string) $template->name);
    $textFieldKeys = [];

    $layoutHtml = (string) ($template->layout_html ?? '');
    $previewHtml = \App\Support\AdTemplatePreview::render($layoutHtml, $sampleDefaults);
    $usedKeys = [];

    if ($layoutHtml !== '') {
        preg_match_all('/\{\{\s*([a-zA-Z][a-zA-Z0-9_]*)\s*\}\}/', $layoutHtml, $matches);
        $placeholderKeys = $matches[1] ?? [];

        preg_match_all('/data-ad-key=[\"\']([a-zA-Z][a-zA-Z0-9_]*)[\"\']/', $layoutHtml, $imgMatches);
        $imageKeys = $imgMatches[1] ?? [];

        $usedKeys = array_values(array_unique(array_map('strtolower', array_merge($placeholderKeys, $imageKeys))));
    }

    foreach ($fields as $field) {
        $key = (string) ($field['key'] ?? '');
        $type = (string) ($field['type'] ?? 'text');
        if ($key === '' || $type === 'image') {
            continue;
        }
        $textFieldKeys[] = $key;
    }
@endphp

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Customize Your Ad</h2>
            <p class="mb-0 text-secondary">
                Template: <strong>{{ $template->name }}</strong> · Size: <strong>{{ $size['name'] }}</strong>
            </p>
            @if(($size['admin_only'] ?? false) === true)
                <p class="mb-0 mt-1"><span class="badge text-bg-warning">Admin Placement</span> <span class="text-secondary">Submit this ad directly to admin for homepage placement approval.</span></p>
            @endif
        </div>
    </div>

    <div class="chart-card">
        <div id="adCustomizeAlert" class="alert d-none" role="alert"></div>
        <form method="POST" action="{{ route('ads.store', ['sizeType' => $sizeType]) }}" enctype="multipart/form-data" novalidate data-subcategory-url-base="{{ url('/dashboard/ads/categories') }}">
            @csrf
            <input type="hidden" name="custom_html" id="customHtmlInput" value="">
            <input type="hidden" name="generated_image_data" id="generatedImageDataInput" value="">
            @if($errors->has('generated_image_data'))
                <div class="alert alert-danger py-2">{{ $errors->first('generated_image_data') }}</div>
            @endif
            @foreach($textFieldKeys as $hiddenTextKey)
                <input type="hidden" name="{{ $hiddenTextKey }}" value="{{ old($hiddenTextKey) }}" class="js-ad-hidden-text" data-key="{{ $hiddenTextKey }}">
            @endforeach

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ad Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }} js-ad-title" maxlength="140" placeholder="e.g. Beauty Clinic — 50% OFF">
                        @if($errors->has('title'))
                            <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="categorySelect" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select
                            name="category_id"
                            id="categorySelect"
                            class="form-select {{ $errors->has('category_id') ? 'is-invalid' : '' }}"
                            data-selected-category="{{ old('category_id') }}"
                        >
                            <option value="">— Select category —</option>
                            @foreach($categories as $category)
                                @php $categoryPrice = (float) ($category->ads_price ?? 0) @endphp
                                <option value="{{ $category->id }}" data-ads-price="{{ number_format($categoryPrice, 2, '.', '') }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} {{ $categoryPrice <= 0 ? '• Free' : '• ₹'.number_format($categoryPrice, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('category_id'))
                            <div class="invalid-feedback">{{ $errors->first('category_id') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="subcategorySelect" class="form-label fw-semibold">Sub Category <span class="text-danger">*</span></label>
                        <select
                            name="subcategory_id"
                            id="subcategorySelect"
                            class="form-select {{ $errors->has('subcategory_id') ? 'is-invalid' : '' }}"
                            data-selected-subcategory="{{ old('subcategory_id') }}"
                            disabled
                        >
                            <option value="">— Select a category first —</option>
                        </select>
                        @if($errors->has('subcategory_id'))
                            <div class="invalid-feedback">{{ $errors->first('subcategory_id') }}</div>
                        @endif
                        <small class="text-success fw-semibold d-block mt-1" id="adsPricingStatus">Select category and sub category to check pricing.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="location"
                            id="adLocation"
                            class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}"
                            placeholder="Search location"
                            value="{{ old('location') }}"
                            autocomplete="off"
                        >
                        <input type="hidden" name="location_lat" id="adLocationLat" value="{{ old('location_lat') }}">
                        <input type="hidden" name="location_lng" id="adLocationLng" value="{{ old('location_lng') }}">
                        @if($errors->has('location'))
                            <div class="invalid-feedback d-block">{{ $errors->first('location') }}</div>
                        @endif
                        @if($errors->has('location_lat'))
                            <div class="invalid-feedback d-block">{{ $errors->first('location_lat') }}</div>
                        @endif
                        @if($errors->has('location_lng'))
                            <div class="invalid-feedback d-block">{{ $errors->first('location_lng') }}</div>
                        @endif
                    </div>

                    <div class="ads-fields">
                        <p class="small text-secondary mb-2">Fully customize the ad directly in live preview: add text, upload images, drag/drop, and style your content.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addTextLayerBtn">
                                <i class="fa-solid fa-font me-1"></i>Add Text
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addImageLayerBtn">
                                <i class="fa-solid fa-image me-1"></i>Add Image
                            </button>
                            <input type="file" id="addImageLayerInput" class="d-none" accept="image/png,image/jpeg,image/webp">
                            <button type="button" class="btn btn-outline-danger btn-sm d-none" id="removeLayerBtn">
                                <i class="fa-solid fa-trash me-1"></i>Remove Selected
                            </button>
                        </div>
                    </div>

                    @if(($size['admin_only'] ?? false) === true)
                        <div class="alert alert-warning mb-0">
                            This is an admin-placement size. After submission, your ad will be posted to admin for homepage review and approval.
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            After submission, your ad will be reviewed by admin before it goes live.
                        </div>
                    @endif

                    <div class="form-check mt-3">
                        <input
                            class="form-check-input {{ $errors->has('accept_terms') ? 'is-invalid' : '' }}"
                            type="checkbox"
                            value="1"
                            id="acceptTerms"
                            name="accept_terms"
                            {{ old('accept_terms') ? 'checked' : '' }}
                            required
                        >
                        <label class="form-check-label" for="acceptTerms">
                            I agree to the
                            <a href="{{ route('frontend.terms.show', ['moduleKey' => 'ads']) }}" target="_blank" rel="noopener noreferrer">Terms and Conditions</a>
                        </label>
                        @if($errors->has('accept_terms'))
                            <div class="invalid-feedback d-block">{{ $errors->first('accept_terms') }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <h5 class="mb-0">Live Preview</h5>
                        <span class="text-secondary small">Exact size: {{ $size['w'] }}px × {{ $size['h'] }}px</span>
                    </div>

                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1 small">Font Size</label>
                                <input type="number" id="layerFontSize" class="form-control form-control-sm" min="8" max="180" value="28">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1 small">Text Color</label>
                                <input type="color" id="layerColor" class="form-control form-control-sm form-control-color p-1" value="#111111">
                            </div>
                            <div class="col-6 col-md-3">
                                <button type="button" class="btn btn-outline-dark btn-sm w-100" id="layerBoldBtn"><i class="fa-solid fa-bold me-1"></i>Bold</button>
                            </div>
                            <div class="col-6 col-md-3">
                                <button type="button" class="btn btn-outline-dark btn-sm w-100" id="layerBringFrontBtn"><i class="fa-solid fa-layer-group me-1"></i>Bring Front</button>
                            </div>
                        </div>
                    </div>

                    <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] }};">
                        <div
                            class="ads-live-preview-inner"
                            id="adPreviewFrame"
                            data-source-width="{{ $size['w'] }}"
                            data-source-height="{{ $size['h'] }}"
                        >
                            <div class="ads-mini-preview-inner" id="adPreview">
                                {!! $previewHtml !!}
                            </div>
                        </div>
                    </div>
                    <script type="application/json" id="adTemplateHtml">@json($template->layout_html)</script>
                    <script type="application/json" id="adTemplateFieldKeys">@json($fields)</script>
                    <script type="application/json" id="adTemplateSampleDefaults">@json($sampleDefaults)</script>

                    <small class="text-secondary d-block mt-2">Tip: Click to select, drag to move, and double-click text to edit.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('ads.create.template', ['sizeType' => $sizeType]) }}" class="btn btn-light px-4">Back</a>
                <button type="button" id="adsPayButton" class="btn btn-warning px-5 d-none">
                    <i class="fa-solid fa-credit-card me-2"></i>Proceed to Payment
                </button>
                <button type="submit" id="adsSubmitButton" class="btn btn-primary ems-btn-primary px-5">
                    <i class="fa-solid fa-paper-plane me-2"></i>Submit for Approval
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const previewFrame = document.getElementById('adPreviewFrame');
        const preview = document.getElementById('adPreview');
        if (!previewFrame || !preview) return;

        const templateScript = document.getElementById('adTemplateHtml');
        const fieldKeysScript = document.getElementById('adTemplateFieldKeys');
        const sampleDefaultsScript = document.getElementById('adTemplateSampleDefaults');
        let originalHtml = '';
        let schemaFields = [];
        let sampleDefaults = {};
        try {
            originalHtml = templateScript ? JSON.parse(templateScript.textContent || '""') : '';
        } catch (e) {
            originalHtml = '';
        }
        try {
            schemaFields = fieldKeysScript ? JSON.parse(fieldKeysScript.textContent || '[]') : [];
        } catch (e) {
            schemaFields = [];
        }
        try {
            sampleDefaults = sampleDefaultsScript ? JSON.parse(sampleDefaultsScript.textContent || '{}') : {};
        } catch (e) {
            sampleDefaults = {};
        }

        const placeholderSrc = '{{ asset('assets/images/ad-sample.png') }}';
        const imageState = {}; // key -> objectURL
        const textState = {};
        const staticState = {};
        const form = preview.closest('form');
        const customHtmlInput = document.getElementById('customHtmlInput');
        const generatedImageDataInput = document.getElementById('generatedImageDataInput');
        const alertBox = document.getElementById('adCustomizeAlert');
        const sourceWidth = Number(previewFrame.getAttribute('data-source-width') || 0);
        const sourceHeight = Number(previewFrame.getAttribute('data-source-height') || 0);
        let selectedLayer = null;

        function scalePreview() {
            const targetWidth = previewFrame.clientWidth || 0;
            const targetHeight = previewFrame.clientHeight || 0;

            if (!sourceWidth || !sourceHeight || !targetWidth || !targetHeight) return;

            const scale = Math.min(targetWidth / sourceWidth, targetHeight / sourceHeight);
            preview.style.width = sourceWidth + 'px';
            preview.style.height = sourceHeight + 'px';
            preview.style.transform = 'scale(' + scale + ')';
            preview.style.transformOrigin = 'top left';
        }

        function getFieldByKey(key) {
            return schemaFields.find((field) => (field && field.key) === key) || null;
        }

        function getDefaultValue(key) {
            const field = getFieldByKey(key);
            if (field && typeof field.default !== 'undefined' && field.default !== null && String(field.default).trim() !== '') {
                return String(field.default);
            }

            if (Object.prototype.hasOwnProperty.call(sampleDefaults, key) && String(sampleDefaults[key]).trim() !== '') {
                return String(sampleDefaults[key]);
            }

            const map = {
                headline: 'Grand Opening Sale',
                subheadline: 'Modern design for real-world promotions',
                cta: 'Claim Offer',
                phone: '+1 234 567 8900',
                website: 'www.yourbrand.com',
                badge: '50% OFF',
                line1: 'Up to 50% discount',
                line2: 'Limited-time launch deal',
                line3: 'Offer valid this week',
                offer_text: 'Flat 30% OFF',
                date_text: 'Offer ends Sunday',
                location_text: 'Main branch, Downtown',
            };

            if (Object.prototype.hasOwnProperty.call(map, key)) return map[key];
            if (field && field.label) return String(field.label);
            return '';
        }

        function computeTextReplacements() {
            const map = {};

            const titleInput = document.querySelector('.js-ad-title');
            const titleVal = titleInput ? (titleInput.value || '').toString().trim() : '';
            map.title = titleVal;

            document.querySelectorAll('.js-ad-hidden-text').forEach((el) => {
                const key = el.getAttribute('data-key');
                if (!key) return;
                const val = (el.value || '').toString().trim();
                map[key] = val === '' ? getDefaultValue(key) : val;
            });

            
            if ((!map.headline || String(map.headline).trim() === '') && titleVal) {
                map.headline = titleVal;
            }

            return map;
        }

        function escapeRegExp(str) {
            return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function renderPreviewHtml() {
            let html = originalHtml;
            const replacements = computeTextReplacements();
            const OPEN = '{' + '{';
            const CLOSE = '}' + '}';

            Object.keys(replacements).forEach((key) => {
                const pattern = escapeRegExp(OPEN) + '\\s*' + escapeRegExp(key) + '\\s*' + escapeRegExp(CLOSE);
                const re = new RegExp(pattern, 'gi');
                const value = replacements[key] || getDefaultValue(key);
                textState[key] = value;
                html = html.replace(re, '<span data-ad-field="' + key + '" contenteditable="true" spellcheck="false">' + escapeHtml(value) + '</span>');
            });

            preview.innerHTML = html;
            applyStaticEditable();
            bindInlineEditors();
        }

        function applyLiveImages() {
            preview.querySelectorAll('img').forEach((img) => {
                img.style.objectFit = 'cover';
                img.style.objectPosition = 'center';
            });

            preview.querySelectorAll('img[data-ad-key]').forEach((img) => {
                const key = img.getAttribute('data-ad-key');
                if (!key) return;
                const existing = (img.getAttribute('src') || '').trim();
                const desired = imageState[key] || existing || placeholderSrc;
                img.setAttribute('src', desired);
                img.style.objectFit = 'cover';
                img.style.objectPosition = 'center';
            });
        }

        function setSelectedLayer(node) {
            preview.querySelectorAll('.ad-layer-selected').forEach((el) => el.classList.remove('ad-layer-selected'));
            selectedLayer = node;
            const removeBtn = document.getElementById('removeLayerBtn');
            if (node) {
                node.classList.add('ad-layer-selected');
                if (removeBtn) removeBtn.classList.remove('d-none');
                if (node.dataset.layerType === 'text') {
                    const fontSizeInput = document.getElementById('layerFontSize');
                    const colorInput = document.getElementById('layerColor');
                    if (fontSizeInput) fontSizeInput.value = parseInt(node.style.fontSize || '28', 10) || 28;
                    if (colorInput) colorInput.value = rgbToHex(node.style.color || '#111111');
                }
            } else if (removeBtn) {
                removeBtn.classList.add('d-none');
            }
        }

        function rgbToHex(value) {
            if (!value) return '#111111';
            if (value.startsWith('#')) return value;
            const matches = value.match(/\d+/g);
            if (!matches || matches.length < 3) return '#111111';
            return '#' + matches.slice(0, 3).map((x) => Number(x).toString(16).padStart(2, '0')).join('');
        }

        function makeDraggable(node) {
            let startX = 0;
            let startY = 0;
            let originX = 0;
            let originY = 0;
            let dragging = false;

            node.addEventListener('mousedown', (event) => {
                if (event.target && event.target.closest('[contenteditable="true"]')) return;
                event.preventDefault();
                setSelectedLayer(node);
                dragging = true;
                startX = event.clientX;
                startY = event.clientY;
                originX = parseFloat(node.style.left || '20');
                originY = parseFloat(node.style.top || '20');
            });

            window.addEventListener('mousemove', (event) => {
                if (!dragging) return;
                const dx = event.clientX - startX;
                const dy = event.clientY - startY;
                const maxX = Math.max(0, sourceWidth - node.offsetWidth);
                const maxY = Math.max(0, sourceHeight - node.offsetHeight);
                const nextLeft = Math.min(maxX, Math.max(0, originX + dx));
                const nextTop = Math.min(maxY, Math.max(0, originY + dy));
                node.style.left = nextLeft + 'px';
                node.style.top = nextTop + 'px';
            });

            window.addEventListener('mouseup', () => {
                dragging = false;
            });
        }

        function attachLayer(node) {
            node.classList.add('ad-custom-layer');
            node.style.position = 'absolute';
            node.style.left = node.style.left || '20px';
            node.style.top = node.style.top || '20px';
            node.style.cursor = 'move';
            node.style.zIndex = String(Date.now() % 100000);
            node.addEventListener('click', (event) => {
                event.stopPropagation();
                setSelectedLayer(node);
            });
            makeDraggable(node);
            preview.appendChild(node);
            setSelectedLayer(node);
        }

        function escapeHtml(str) {
            return str
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function updatePreview() {
            renderPreviewHtml();
            applyLiveImages();
            preview.style.position = 'relative';
            preview.style.overflow = 'hidden';
            scalePreview();
        }

        function applyStaticEditable() {
            const editableNodes = preview.querySelectorAll('div, span, p, li, h1, h2, h3, h4, h5, h6, a, button');
            let idx = 0;
            editableNodes.forEach((node) => {
                if (node.closest('[data-ad-field]')) return;
                if (node.querySelector('img')) return;
                if (node.children.length > 0) return;
                const raw = (node.textContent || '').trim();
                if (!raw) return;
                const id = 's_' + idx++;
                node.setAttribute('data-ad-static-id', id);
                node.setAttribute('contenteditable', 'true');
                node.setAttribute('spellcheck', 'false');
                if (Object.prototype.hasOwnProperty.call(staticState, id)) {
                    node.textContent = staticState[id];
                }
            });
        }

        function bindInlineEditors() {
            preview.querySelectorAll('[data-ad-field]').forEach((node) => {
                node.addEventListener('input', () => {
                    const key = node.getAttribute('data-ad-field');
                    if (!key) return;
                    const val = (node.textContent || '').trim();
                    textState[key] = val;
                    const input = document.querySelector('.js-ad-hidden-text[data-key="' + key + '"]');
                    if (input) input.value = val;
                });
            });

            preview.querySelectorAll('[data-ad-static-id]').forEach((node) => {
                node.addEventListener('input', () => {
                    const id = node.getAttribute('data-ad-static-id');
                    if (!id) return;
                    staticState[id] = node.textContent || '';
                });
            });

        }

        document.querySelectorAll('.js-ad-hidden-text').forEach((el) => {
            el.addEventListener('input', updatePreview);
        });

        const titleEl = document.querySelector('.js-ad-title');
        if (titleEl) {
            titleEl.addEventListener('input', updatePreview);
        }

        document.getElementById('addTextLayerBtn')?.addEventListener('click', () => {
            const node = document.createElement('div');
            node.dataset.layerType = 'text';
            node.style.fontSize = '28px';
            node.style.color = '#111111';
            node.style.fontWeight = '400';
            node.style.minWidth = '80px';
            node.style.maxWidth = Math.max(120, sourceWidth - 40) + 'px';
            node.style.wordBreak = 'break-word';
            node.style.padding = '4px 6px';
            node.style.background = 'rgba(255,255,255,0.15)';
            node.style.border = '1px dashed transparent';
            node.textContent = 'Edit text';
            node.setAttribute('contenteditable', 'true');
            node.setAttribute('spellcheck', 'false');
            attachLayer(node);
        });

        document.getElementById('addImageLayerBtn')?.addEventListener('click', () => {
            document.getElementById('addImageLayerInput')?.click();
        });

        document.getElementById('addImageLayerInput')?.addEventListener('change', (event) => {
            const file = event.target.files && event.target.files[0];
            if (!file) return;
            const node = document.createElement('img');
            node.dataset.layerType = 'image';
            node.src = URL.createObjectURL(file);
            node.style.width = Math.max(140, Math.round(sourceWidth * 0.25)) + 'px';
            node.style.height = 'auto';
            node.style.objectFit = 'cover';
            node.style.border = '1px dashed transparent';
            attachLayer(node);
            event.target.value = '';
        });

        document.getElementById('layerFontSize')?.addEventListener('input', (event) => {
            if (!selectedLayer || selectedLayer.dataset.layerType !== 'text') return;
            const size = Math.min(180, Math.max(8, Number(event.target.value || 28)));
            selectedLayer.style.fontSize = size + 'px';
        });

        document.getElementById('layerColor')?.addEventListener('input', (event) => {
            if (!selectedLayer || selectedLayer.dataset.layerType !== 'text') return;
            selectedLayer.style.color = event.target.value || '#111111';
        });

        document.getElementById('layerBoldBtn')?.addEventListener('click', () => {
            if (!selectedLayer || selectedLayer.dataset.layerType !== 'text') return;
            selectedLayer.style.fontWeight = selectedLayer.style.fontWeight === '700' ? '400' : '700';
        });

        document.getElementById('layerBringFrontBtn')?.addEventListener('click', () => {
            if (!selectedLayer) return;
            selectedLayer.style.zIndex = String(Date.now() % 100000);
        });

        document.getElementById('removeLayerBtn')?.addEventListener('click', () => {
            if (!selectedLayer) return;
            selectedLayer.remove();
            setSelectedLayer(null);
        });

        preview.addEventListener('click', () => setSelectedLayer(null));

        async function exportPreviewAsPng() {
            const exportWidth = sourceWidth || preview.scrollWidth || 0;
            const exportHeight = sourceHeight || preview.scrollHeight || 0;
            const pixelRatio = 4;
            const clone = preview.cloneNode(true);
            const sandbox = document.createElement('div');
            sandbox.style.position = 'fixed';
            sandbox.style.left = '-10000px';
            sandbox.style.top = '0';
            sandbox.style.width = exportWidth + 'px';
            sandbox.style.height = exportHeight + 'px';
            sandbox.style.overflow = 'hidden';
            sandbox.style.zIndex = '-1';

            clone.style.position = 'static';
            clone.style.inset = 'auto';
            clone.style.left = 'auto';
            clone.style.right = 'auto';
            clone.style.top = 'auto';
            clone.style.bottom = 'auto';
            clone.style.transform = 'none';
            clone.style.transformOrigin = 'top left';
            clone.style.width = exportWidth + 'px';
            clone.style.height = exportHeight + 'px';
            clone.style.maxWidth = 'none';
            clone.style.maxHeight = 'none';
            clone.style.overflow = 'hidden';

            sandbox.appendChild(clone);
            document.body.appendChild(sandbox);

            const waitForImages = async (root, timeoutMs = 6000) => {
                const imgs = Array.from(root.querySelectorAll('img'));
                if (!imgs.length) return;

                await Promise.race([
                    Promise.all(imgs.map((img) => {
                        if (img.complete) return Promise.resolve();
                        return new Promise((resolve) => {
                            const done = () => resolve();
                            img.addEventListener('load', done, { once: true });
                            img.addEventListener('error', done, { once: true });
                        });
                    })),
                    new Promise((resolve) => setTimeout(resolve, timeoutMs))
                ]);
            };

            try {
                await waitForImages(clone);

                if (window.htmlToImage && typeof window.htmlToImage.toPng === 'function') {
                    try {
                        return await window.htmlToImage.toPng(clone, {
                            cacheBust: true,
                            pixelRatio,
                            canvasWidth: exportWidth,
                            canvasHeight: exportHeight,
                            backgroundColor: null,
                            // Avoid reading cssRules from cross-origin stylesheets (Google Fonts, etc.).
                            // Some html-to-image versions support one or both of these flags.
                            // Unsupported options are safely ignored.
                            skipFonts: true,
                            fontEmbedCSS: '',
                        });
                    } catch (error) {
                        // Some stylesheets (e.g. Google Fonts) block cssRules access in html-to-image.
                        // Fall back to html2canvas instead of failing export.
                    }
                }

                if (window.html2canvas) {
                    const canvas = await window.html2canvas(clone, {
                        width: exportWidth || clone.scrollWidth,
                        height: exportHeight || clone.scrollHeight,
                        windowWidth: exportWidth || clone.scrollWidth,
                        windowHeight: exportHeight || clone.scrollHeight,
                        backgroundColor: null,
                        useCORS: true,
                        allowTaint: false,
                        logging: false,
                        imageTimeout: 10000,
                        scale: pixelRatio,
                    });
                    const context = canvas.getContext('2d');
                    if (context) {
                        context.imageSmoothingEnabled = true;
                        context.imageSmoothingQuality = 'high';
                    }
                    return canvas.toDataURL('image/png');
                }
            } finally {
                document.body.removeChild(sandbox);
            }

            return '';
        }

        if (form) {
            form.addEventListener('submit', async (event) => {
                if (form.dataset.isSubmitting === '1') {
                    return;
                }
                event.preventDefault();

                preview.querySelectorAll('[data-ad-field]').forEach((node) => {
                    const key = node.getAttribute('data-ad-field');
                    if (!key) return;
                    const val = (node.textContent || '').trim();
                    const input = document.querySelector('.js-ad-hidden-text[data-key="' + key + '"]');
                    if (input) input.value = val;
                });
                if (customHtmlInput) {
                    const exportWidth = sourceWidth || preview.scrollWidth || 0;
                    const exportHeight = sourceHeight || preview.scrollHeight || 0;
                    customHtmlInput.value = '<div class="ad-canvas" style="width:' + exportWidth + 'px;height:' + exportHeight + 'px;overflow:hidden;position:relative;">'
                        + preview.innerHTML
                        + '</div>';
                }

                if (generatedImageDataInput) {
                    generatedImageDataInput.value = await exportPreviewAsPng();
                }

                if (!generatedImageDataInput || !generatedImageDataInput.value) {
                    if (alertBox) {
                        alertBox.className = 'alert alert-danger';
                        alertBox.textContent = 'Could not generate ad image. Please re-upload images and try again.';
                        alertBox.classList.remove('d-none');
                    }
                    form.dataset.isSubmitting = '0';
                    return;
                }

                form.dataset.isSubmitting = '1';

                const formData = new FormData(form);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        throw payload;
                    }

                    showToast(payload.message || 'Ad saved successfully.', 'success');
                    setTimeout(() => {
                        window.location.href = payload.redirect_url || '{{ route('ads.index') }}';
                    }, 900);
                } catch (error) {
                    const message = error?.message
                        || error?.errors?.generated_image_data?.[0]
                        || error?.errors?.title?.[0]
                        || 'Unable to save ad. Please check the form and try again.';
                    showToast(message, 'danger');
                    form.dataset.isSubmitting = '0';
                }
            });
        }

        function showToast(message, type = 'success') {
            let container = document.getElementById('adToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'adToastContainer';
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '1080';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-' + (type === 'danger' ? 'danger' : 'success') + ' border-0';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + escapeHtml(message) + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            container.appendChild(toast);

            if (window.bootstrap && window.bootstrap.Toast) {
                const instance = new window.bootstrap.Toast(toast, { delay: 2500 });
                instance.show();
                toast.addEventListener('hidden.bs.toast', () => toast.remove());
            } else {
                toast.classList.add('show');
                setTimeout(() => toast.remove(), 2200);
            }
        }

        window.addEventListener('resize', scalePreview);
        updatePreview();
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.13/dist/html-to-image.min.js"></script>
<script>
    (function () {
        const form = document.querySelector('form[action*="/dashboard/ads/create/"]');
        if (!form) return;

        const categorySelect = document.getElementById('categorySelect');
        const subcategorySelect = document.getElementById('subcategorySelect');
        const subcategoryBaseUrl = form.dataset.subcategoryUrlBase || '';
        const selectedSubcategory = subcategorySelect ? (subcategorySelect.dataset.selectedSubcategory || '') : '';
        const pricingStatus = document.getElementById('adsPricingStatus');
        const submitButton = document.getElementById('adsSubmitButton');
        const payButton = document.getElementById('adsPayButton');
        const locationInput = document.getElementById('adLocation');
        const locationLatInput = document.getElementById('adLocationLat');
        const locationLngInput = document.getElementById('adLocationLng');
        function currentPriceFromOption(selectElement) {
            if (!selectElement) return 0;
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption) return 0;
            return Number(selectedOption.getAttribute('data-ads-price') || 0);
        }

        function syncPricingUi() {
            const categoryPrice = currentPriceFromOption(categorySelect);
            const subcategoryPrice = currentPriceFromOption(subcategorySelect);
            const finalPrice = subcategoryPrice > 0 ? subcategoryPrice : categoryPrice;
            const isPaid = finalPrice > 0;

            if (pricingStatus) {
                if (!categorySelect.value || !subcategorySelect.value) {
                    pricingStatus.textContent = 'Select category and sub category to check pricing.';
                    pricingStatus.className = 'text-success fw-semibold d-block mt-1';
                } else if (finalPrice <= 0) {
                    pricingStatus.textContent = 'This selection is Free. You can submit your ad now.';
                    pricingStatus.className = 'text-success fw-semibold d-block mt-1';
                } else {
                    pricingStatus.textContent = `This sub category is Paid (₹${finalPrice.toFixed(2)}). Please continue to payment.`;
                    pricingStatus.className = 'text-warning fw-semibold d-block mt-1';
                }
            }

            if (submitButton) {
                submitButton.classList.toggle('d-none', isPaid);
            }
            if (payButton) {
                payButton.classList.toggle('d-none', !isPaid);
            }
        }

        async function loadSubcategories(categoryId, selectedId = '') {
            if (!subcategorySelect) return;
            if (!categoryId || !subcategoryBaseUrl) {
                subcategorySelect.innerHTML = '<option value="">— Select a category first —</option>';
                subcategorySelect.disabled = true;
                return;
            }

            try {
                const response = await fetch(`${subcategoryBaseUrl}/${categoryId}/subcategories`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                const options = ['<option value="">— Select subcategory —</option>'];
                (Array.isArray(data) ? data : []).forEach((item) => {
                    const isSelected = String(item.id) === String(selectedId);
                    const price = Number(item.ads_price || 0);
                    const label = price <= 0 ? `${item.name} • Free` : `${item.name} • ₹${price.toFixed(2)}`;
                    options.push(`<option value="${item.id}" data-ads-price="${price.toFixed(2)}" ${isSelected ? 'selected' : ''}>${label}</option>`);
                });
                subcategorySelect.innerHTML = options.join('');
                subcategorySelect.disabled = false;
                syncPricingUi();
            } catch (error) {
                subcategorySelect.innerHTML = '<option value="">— Unable to load subcategories —</option>';
                subcategorySelect.disabled = true;
                syncPricingUi();
            }
        }

        if (categorySelect && subcategorySelect) {
            categorySelect.addEventListener('change', function () {
                loadSubcategories(this.value, '');
                syncPricingUi();
            });
            subcategorySelect.addEventListener('change', function () {
                syncPricingUi();
            });

            if (categorySelect.value) {
                loadSubcategories(categorySelect.value, selectedSubcategory);
            }
        }

        if (payButton) {
            payButton.addEventListener('click', function () {
                alert('Payment integration is not configured yet. Please contact admin to complete payment for this paid sub category.');
            });
        }

        syncPricingUi();

        if (locationInput) {
            locationInput.addEventListener('input', function () {
                if (locationLatInput) locationLatInput.value = '';
                if (locationLngInput) locationLngInput.value = '';
            });
        }

        window.initAdLocationAutocomplete = function () {
            if (!locationInput || !window.google || !google.maps || !google.maps.places) {
                return;
            }

            const autocomplete = new google.maps.places.Autocomplete(locationInput, {
                fields: ['formatted_address', 'geometry', 'name'],
            });

            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                const lat = place?.geometry?.location?.lat?.();
                const lng = place?.geometry?.location?.lng?.();
                locationInput.value = place?.formatted_address || place?.name || locationInput.value;
                if (locationLatInput) locationLatInput.value = typeof lat === 'number' ? String(lat) : '';
                if (locationLngInput) locationLngInput.value = typeof lng === 'number' ? String(lng) : '';
            });
        };
    })();
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAdLocationAutocomplete"></script>
@endpush
