@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@php
    $schema = is_array($template->schema_json) ? $template->schema_json : [];
    $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
    $sampleDefaults = \App\Support\AdTemplatePreview::sampleFieldsForSchema($fields, (string) $template->name);
    $textFields = [];

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
        $textFields[] = [
            'key' => $key,
            'label' => (string) ($field['label'] ?? $key),
            'required' => (bool) ($field['required'] ?? false),
            'max' => (int) ($field['max'] ?? 0),
        ];
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
            <input type="hidden" name="custom_css" id="customCssInput" value="">
            <input type="hidden" name="generated_image_data" id="generatedImageDataInput" value="">
            @error('generated_image_data')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror
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
                        <p class="small text-secondary mb-2">Fill text and upload template images. Live preview below keeps the original HTML template size.</p>
                        @foreach($textFields as $textField)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    {{ $textField['label'] }} @if($textField['required'])<span class="text-danger">*</span>@endif
                                </label>
                                <input
                                    type="text"
                                    name="{{ $textField['key'] }}"
                                    class="form-control @error($textField['key']) is-invalid @enderror js-ad-text"
                                    data-key="{{ $textField['key'] }}"
                                    value="{{ old($textField['key'], $sampleDefaults[$textField['key']] ?? '') }}"
                                    maxlength="{{ $textField['max'] > 0 ? $textField['max'] : 255 }}"
                                    {{ $textField['required'] ? 'required' : '' }}
                                >
                                @error($textField['key'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
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
                        <h5 class="mb-0">Live Template Preview</h5>
                        <span class="text-secondary small">{{ $size['w'] }}×{{ $size['h'] }}</span>
                    </div>
                    <div class="template-customizer-wrap mb-3">
                        <div class="ads-live-preview" style="aspect-ratio: {{ $size['w'] }} / {{ $size['h'] }};">
                            <div class="ads-live-preview-inner p-0" id="adTemplateLivePreview"></div>
                        </div>
                    </div>
                    <script type="application/json" id="adTemplateHtml">@json((string) ($template->layout_html ?? ''))</script>
                    <script type="application/json" id="adTemplateSampleDefaults">@json($sampleDefaults)</script>
                    <script type="application/json" id="adTemplateFields">@json($fields)</script>

                    <small class="text-secondary d-block mt-2">This preview renders your original HTML template at the selected ad size.</small>
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
        const preview = document.getElementById('adTemplateLivePreview');
        const form = document.querySelector('form[action*="/dashboard/ads/create/"]');
        if (!preview || !form) return;

        const templateHtmlScript = document.getElementById('adTemplateHtml');
        const sampleDefaultsScript = document.getElementById('adTemplateSampleDefaults');
        const fieldsScript = document.getElementById('adTemplateFields');

        const customHtmlInput = document.getElementById('customHtmlInput');
        const generatedImageDataInput = document.getElementById('generatedImageDataInput');
        const templateHtml = templateHtmlScript ? JSON.parse(templateHtmlScript.textContent || '""') : '';
        const sampleDefaults = sampleDefaultsScript ? JSON.parse(sampleDefaultsScript.textContent || '{}') : {};
        const fields = fieldsScript ? JSON.parse(fieldsScript.textContent || '[]') : [];

        function getDefaultValue(key) {
            if (Object.prototype.hasOwnProperty.call(sampleDefaults, key) && String(sampleDefaults[key]).trim() !== '') {
                return String(sampleDefaults[key]);
            }
            return key;
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildPreviewHtml() {
            let html = String(templateHtml || '');

            document.querySelectorAll('.js-ad-text').forEach((input) => {
                const key = input.getAttribute('data-key');
                if (!key) return;
                const value = escapeHtml((input.value || '').trim() || getDefaultValue(key));
                const matcher = new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g');
                html = html.replace(matcher, value);
            });
            return html.replace(/\{\{[a-zA-Z][a-zA-Z0-9_]*\}\}/g, '');
        }

        function renderPreview() {
            preview.innerHTML = buildPreviewHtml();
            preview.querySelectorAll('img[data-ad-key]').forEach((img) => {
                img.style.objectFit = 'contain';
                img.style.objectPosition = 'center';
            });
        }

        document.querySelectorAll('.js-ad-text').forEach((input) => {
            input.addEventListener('input', renderPreview);
        });

        fields.forEach((field) => {
            const key = String(field.key || '');
            const type = String(field.type || 'text');
            if (!key || type !== 'image') return;
            const input = document.querySelector(`.js-ad-image[data-key="${key}"]`);
            if (!input) return;
            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.querySelectorAll(`img[data-ad-key="${key}"]`).forEach((img) => {
                        img.setAttribute('src', String(event.target?.result || ''));
                    });
                };
                reader.readAsDataURL(file);
            });
        });

        form.addEventListener('submit', (event) => {
            if (form.dataset.isSubmitting === '1') return;
            event.preventDefault();

            renderPreview();
            if (customHtmlInput) customHtmlInput.value = preview.innerHTML;
            if (generatedImageDataInput) generatedImageDataInput.value = '';
            form.dataset.isSubmitting = '1';
            form.submit();
        });

        renderPreview();
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
