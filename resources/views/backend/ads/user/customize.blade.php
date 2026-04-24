@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@php
    $schema = is_array($template->schema_json) ? $template->schema_json : [];
    $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
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
                                <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
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

                    <small class="text-secondary d-block mt-2">Tip: Click any text to edit directly in the preview.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('ads.create.template', ['sizeType' => $sizeType]) }}" class="btn btn-light px-4">Back</a>
                <button type="submit" class="btn btn-primary ems-btn-primary px-5">
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
        let originalHtml = '';
        let schemaFields = [];
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

        const placeholderSrc = '{{ asset('assets/images/ad-sample.png') }}';
        const imageState = {}; // key -> objectURL
        const textState = {};
        const staticState = {};
        const form = preview.closest('form');
        const customHtmlInput = document.getElementById('customHtmlInput');

        function scalePreview() {
            const sourceWidth = Number(previewFrame.getAttribute('data-source-width') || 0);
            const sourceHeight = Number(previewFrame.getAttribute('data-source-height') || 0);
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

            const map = {
                headline: 'Your Headline',
                subheadline: 'Add your message here',
                cta: 'Book Now',
                phone: '+1 000 000 0000',
                website: 'www.example.com',
                badge: '50% OFF',
                line1: 'Service 1',
                line2: 'Service 2',
                line3: 'Service 3',
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
            preview.querySelectorAll('img[data-ad-key]').forEach((img) => {
                const key = img.getAttribute('data-ad-key');
                if (!key) return;
                const desired = imageState[key] || placeholderSrc;
                img.setAttribute('src', desired);
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

        if (form) {
            form.addEventListener('submit', () => {
                preview.querySelectorAll('[data-ad-field]').forEach((node) => {
                    const key = node.getAttribute('data-ad-field');
                    if (!key) return;
                    const val = (node.textContent || '').trim();
                    const input = document.querySelector('.js-ad-hidden-text[data-key="' + key + '"]');
                    if (input) input.value = val;
                });
                if (customHtmlInput) {
                    customHtmlInput.value = preview.innerHTML;
                }
            });
        }

        window.addEventListener('resize', scalePreview);
        updatePreview();
    })();
</script>
<script>
    (function () {
        const form = document.querySelector('form[action*="/dashboard/ads/create/"]');
        if (!form) return;

        const categorySelect = document.getElementById('categorySelect');
        const subcategorySelect = document.getElementById('subcategorySelect');
        const subcategoryBaseUrl = form.dataset.subcategoryUrlBase || '';
        const selectedSubcategory = subcategorySelect ? (subcategorySelect.dataset.selectedSubcategory || '') : '';
        const locationInput = document.getElementById('adLocation');
        const locationLatInput = document.getElementById('adLocationLat');
        const locationLngInput = document.getElementById('adLocationLng');

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
                    options.push(`<option value="${item.id}" ${isSelected ? 'selected' : ''}>${item.name}</option>`);
                });
                subcategorySelect.innerHTML = options.join('');
                subcategorySelect.disabled = false;
            } catch (error) {
                subcategorySelect.innerHTML = '<option value="">— Unable to load subcategories —</option>';
                subcategorySelect.disabled = true;
            }
        }

        if (categorySelect && subcategorySelect) {
            categorySelect.addEventListener('change', function () {
                loadSubcategories(this.value, '');
            });

            if (categorySelect.value) {
                loadSubcategories(categorySelect.value, selectedSubcategory);
            }
        }

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
