@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@php
    $schema = is_array($template->schema_json) ? $template->schema_json : [];
    $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
    $sampleDefaults = \App\Support\AdTemplatePreview::sampleFieldsForSchema($fields, (string) $template->name);
    $textFieldKeys = [];

    $layoutHtml = (string) ($template->layout_html ?? '');
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
        <form method="POST" action="{{ route('ads.store', ['sizeType' => $sizeType, 'template' => $template->id]) }}" enctype="multipart/form-data" novalidate data-subcategory-url-base="{{ url('/dashboard/ads/categories') }}">
            @csrf
            <input type="hidden" name="custom_html" id="customHtmlInput" value="">
            <input type="hidden" name="generated_image_data" id="generatedImageDataInput" value="">
            @error('generated_image_data')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror
            @foreach($textFieldKeys as $hiddenTextKey)
                <input type="hidden" name="{{ $hiddenTextKey }}" value="{{ old($hiddenTextKey) }}" class="js-ad-hidden-text" data-key="{{ $hiddenTextKey }}">
            @endforeach

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ad Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror js-ad-title" maxlength="140" placeholder="e.g. Beauty Clinic — 50% OFF">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="categorySelect" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select
                            name="category_id"
                            id="categorySelect"
                            class="form-select @error('category_id') is-invalid @enderror"
                            data-selected-category="{{ old('category_id') }}"
                        >
                            <option value="">— Select category —</option>
                            @foreach($categories as $category)
                                @php($categoryPrice = (float) ($category->ads_price ?? 0))
                                <option value="{{ $category->id }}" data-ads-price="{{ number_format($categoryPrice, 2, '.', '') }}" @selected((string) old('category_id') === (string) $category->id)>
                                    {{ $category->name }} {{ $categoryPrice <= 0 ? '• Free' : '• ₹'.number_format($categoryPrice, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subcategorySelect" class="form-label fw-semibold">Sub Category <span class="text-danger">*</span></label>
                        <select
                            name="subcategory_id"
                            id="subcategorySelect"
                            class="form-select @error('subcategory_id') is-invalid @enderror"
                            data-selected-subcategory="{{ old('subcategory_id') }}"
                            disabled
                        >
                            <option value="">— Select a category first —</option>
                        </select>
                        @error('subcategory_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-success fw-semibold d-block mt-1" id="adsPricingStatus">Select category and sub category to check pricing.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="location"
                            id="adLocation"
                            class="form-control @error('location') is-invalid @enderror"
                            placeholder="Search location"
                            value="{{ old('location') }}"
                            autocomplete="off"
                        >
                        <input type="hidden" name="location_lat" id="adLocationLat" value="{{ old('location_lat') }}">
                        <input type="hidden" name="location_lng" id="adLocationLng" value="{{ old('location_lng') }}">
                        @error('location')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('location_lat')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('location_lng')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ads-fields">
                        <p class="small text-secondary mb-2">Upload template images here. Edit all text content directly in live preview.</p>
                        @foreach($fields as $field)
                            @php
                                $key = (string) ($field['key'] ?? '');
                                $label = (string) ($field['label'] ?? $key);
                                $type = (string) ($field['type'] ?? 'text');
                                $required = (bool) ($field['required'] ?? false);
                                $isUsedInTemplate = $key !== '' && in_array(strtolower($key), $usedKeys, true);
                            @endphp
                            @if($key !== '' && $type === 'image' && ($required || $isUsedInTemplate))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        {{ $label }} @if($required)<span class="text-danger">*</span>@endif
                                    </label>

                                    @if($type === 'image')
                                        <input
                                            type="file"
                                            name="{{ $key }}"
                                            class="form-control @error($key) is-invalid @enderror js-ad-image"
                                            accept="image/png,image/jpeg,image/webp"
                                            data-key="{{ $key }}"
                                            {{ $required ? 'required' : '' }}
                                        >
                                        @error($key)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-secondary">PNG/JPG/WebP · Max 2MB</small>
                                    @endif
                                </div>
                            @endif
                        @endforeach
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
                            class="form-check-input @error('accept_terms') is-invalid @enderror"
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
                        @error('accept_terms')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <h5 class="mb-0">Live Preview</h5>
                        <span class="text-secondary small">{{ $size['w'] }}×{{ $size['h'] }}</span>
                    </div>

                    <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] }};">
                        <div
                            class="ads-live-preview-inner"
                            id="adPreviewFrame"
                            data-source-width="{{ $size['w'] }}"
                            data-source-height="{{ $size['h'] }}"
                        >
                            <div class="ads-mini-preview-inner" id="adPreview">
                                {!! $template->layout_html !!}
                            </div>
                        </div>
                    </div>
                    <script type="application/json" id="adTemplateHtml">@json($template->layout_html)</script>
                    <script type="application/json" id="adTemplateFieldKeys">@json($fields)</script>
                    <script type="application/json" id="adTemplateSampleDefaults">@json($sampleDefaults)</script>

                    <small class="text-secondary d-block mt-2">Tip: Click any text to edit directly in the preview.</small>
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

        document.querySelectorAll('.js-ad-image').forEach((el) => {
            el.addEventListener('change', async () => {
                const key = el.getAttribute('data-key');
                const file = el.files && el.files[0];
                if (!key || !file) return;
                if (imageState[key]) {
                    try { URL.revokeObjectURL(imageState[key]); } catch (e) {}
                }
                imageState[key] = URL.createObjectURL(file);
                applyLiveImages();
            });
        });

        async function exportPreviewAsPng() {
            const exportWidth = sourceWidth || preview.scrollWidth || 0;
            const exportHeight = sourceHeight || preview.scrollHeight || 0;
            const pixelRatio = 1;
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
                form.submit();
            });
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
