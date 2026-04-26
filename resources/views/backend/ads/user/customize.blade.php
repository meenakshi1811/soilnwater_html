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
            <input type="hidden" name="custom_css" id="customCssInput" value="">
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
                        <h5 class="mb-0">Image Template Designer</h5>
                        <span class="text-secondary small">{{ $size['w'] }}×{{ $size['h'] }}</span>
                    </div>
                    <div class="template-customizer-wrap mb-3">
                        <div class="row g-2 mb-2">
                        <div class="col-6 col-md-3">
                            <label class="form-label mb-1 small text-secondary">Font Family</label>
                            <select class="form-select form-select-sm" id="adFontFamilyControl">
                                <option value="Arial">Arial</option>
                                <option value="Verdana">Verdana</option>
                                <option value="Tahoma">Tahoma</option>
                                <option value="Trebuchet MS">Trebuchet MS</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Courier New">Courier New</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1 small text-secondary">Font Size</label>
                            <input type="number" class="form-control form-control-sm" id="adFontSizeControl" min="8" max="160" value="24">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1 small text-secondary">Text Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="adTextColorControl" value="#111111">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1 small text-secondary d-block">Bold</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="adTextBoldControl">
                                <label class="form-check-label small" for="adTextBoldControl">Enable</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label mb-1 small text-secondary">Text Align</label>
                            <select class="form-select form-select-sm" id="adTextAlignControl">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1 small text-secondary">Image Width</label>
                            <input type="number" class="form-control form-control-sm" id="adImageWidthControl" min="40" max="{{ $size['w'] }}" value="220" disabled>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1 small text-secondary">Image Height</label>
                            <input type="number" class="form-control form-control-sm" id="adImageHeightControl" min="40" max="{{ $size['h'] }}" value="220" disabled>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small text-secondary">Template Background (optional)</label>
                            <input type="file" class="form-control form-control-sm" id="adTemplateBgImageControl" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label mb-1 small text-secondary d-block">Layers</label>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="addAdTextLayerBtn">+ Add Text</button>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label mb-1 small text-secondary d-block">Remove</label>
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" id="removeAdLayerBtn">Remove Selected</button>
                        </div>
                    </div>

                        <div
                            id="adDesignerStage"
                            class="banner-designer-stage"
                            style="aspect-ratio: {{ $size['w'] }} / {{ $size['h'] }}; background:#ffffff;"
                            data-source-width="{{ $size['w'] }}"
                            data-source-height="{{ $size['h'] }}"
                        >
                        </div>
                    </div>
                    <script type="application/json" id="adTemplateFieldKeys">@json($fields)</script>
                    <script type="application/json" id="adTemplateSampleDefaults">@json($sampleDefaults)</script>
                    <script type="application/json" id="adTemplateBackgroundUrl">@json(!empty($template->preview_image) ? asset($template->preview_image) : asset('assets/images/ad-sample.png'))</script>

                    <small class="text-secondary d-block mt-2">Template is now edited as an image canvas. Drag layers, edit text/font, and submit the generated image (post-offer style flow).</small>
                    <div class="small text-secondary mt-1">
                        <span class="d-inline-block me-2">• Text and image layers are draggable.</span>
                        <span class="d-inline-block">• Use mouse wheel on selected image to resize quickly.</span>
                    </div>
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
        const stage = document.getElementById('adDesignerStage');
        const form = document.querySelector('form[action*="/dashboard/ads/create/"]');
        if (!stage || !form) return;

        const fieldKeysScript = document.getElementById('adTemplateFieldKeys');
        const sampleDefaultsScript = document.getElementById('adTemplateSampleDefaults');
        const templateBgScript = document.getElementById('adTemplateBackgroundUrl');

        const sourceWidth = Number(stage.getAttribute('data-source-width') || 0);
        const sourceHeight = Number(stage.getAttribute('data-source-height') || 0);

        const customHtmlInput = document.getElementById('customHtmlInput');
        const generatedImageDataInput = document.getElementById('generatedImageDataInput');
        const schemaFields = fieldKeysScript ? JSON.parse(fieldKeysScript.textContent || '[]') : [];
        const sampleDefaults = sampleDefaultsScript ? JSON.parse(sampleDefaultsScript.textContent || '{}') : {};
        const templateBackgroundUrl = templateBgScript ? JSON.parse(templateBgScript.textContent || '""') : '';

        const fontFamily = document.getElementById('adFontFamilyControl');
        const fontSize = document.getElementById('adFontSizeControl');
        const textColor = document.getElementById('adTextColorControl');
        const textBold = document.getElementById('adTextBoldControl');
        const textAlign = document.getElementById('adTextAlignControl');
        const imageWidth = document.getElementById('adImageWidthControl');
        const imageHeight = document.getElementById('adImageHeightControl');
        const addTextLayerBtn = document.getElementById('addAdTextLayerBtn');
        const removeLayerBtn = document.getElementById('removeAdLayerBtn');
        const templateBgInput = document.getElementById('adTemplateBgImageControl');

        const designer = {
            width: sourceWidth,
            height: sourceHeight,
            layers: [],
            activeId: null,
            drag: null,
            counter: 0,
        };

        function uid(prefix) {
            designer.counter += 1;
            return prefix + '_' + designer.counter;
        }

        function clamp(val, min, max) {
            return Math.max(min, Math.min(max, val));
        }

        function getDefaultValue(key) {
            if (Object.prototype.hasOwnProperty.call(sampleDefaults, key) && String(sampleDefaults[key]).trim() !== '') {
                return String(sampleDefaults[key]);
            }
            return key;
        }

        function addLayer(layer) {
            designer.layers.push(layer);
        }

        function addTextLayer(text, options = {}) {
            addLayer({
                id: uid('txt'),
                type: 'text',
                text: String(text || 'Text'),
                x: options.x ?? 40,
                y: options.y ?? 40,
                fontSize: options.fontSize ?? 42,
                color: options.color ?? '#111111',
                fontFamily: options.fontFamily ?? 'Arial',
                fontWeight: options.fontWeight ?? '700',
                align: options.align ?? 'left',
                sourceTag: options.sourceTag || null,
                locked: !!options.locked,
            });
        }

        function addImageLayer(src, options = {}) {
            const img = new Image();
            img.onload = function () {
                const ratio = img.width / img.height || 1;
                const width = options.width ?? Math.round(designer.width * 0.3);
                const height = options.height ?? Math.round(width / ratio);
                addLayer({
                    id: uid('img'),
                    type: 'image',
                    src,
                    x: options.x ?? Math.round((designer.width - width) / 2),
                    y: options.y ?? Math.round((designer.height - height) / 2),
                    width: clamp(width, 40, designer.width),
                    height: clamp(height, 40, designer.height),
                    aspectRatio: ratio,
                    sourceTag: options.sourceTag || null,
                    locked: !!options.locked,
                    toBack: !!options.toBack,
                });
                if (options.toBack) {
                    const layer = designer.layers.pop();
                    designer.layers.unshift(layer);
                }
                render();
            };
            img.src = src;
        }

        function getActiveLayer() {
            return designer.layers.find((layer) => layer.id === designer.activeId) || null;
        }

        function ensureLayerBounds(layer) {
            if (layer.type === 'image') {
                layer.width = clamp(layer.width, 40, designer.width);
                layer.height = clamp(layer.height, 40, designer.height);
            }
            const maxX = designer.width - (layer.type === 'image' ? layer.width : 20);
            const maxY = designer.height - (layer.type === 'image' ? layer.height : 20);
            layer.x = clamp(layer.x, 0, Math.max(0, maxX));
            layer.y = clamp(layer.y, 0, Math.max(0, maxY));
        }

        function syncControlsFromActive() {
            const layer = getActiveLayer();
            const isText = !!layer && layer.type === 'text';
            const isImage = !!layer && layer.type === 'image';

            fontFamily.disabled = !isText;
            fontSize.disabled = !isText;
            textColor.disabled = !isText;
            textBold.disabled = !isText;
            textAlign.disabled = !isText;

            imageWidth.disabled = !isImage;
            imageHeight.disabled = !isImage;

            if (isText) {
                fontFamily.value = layer.fontFamily || 'Arial';
                fontSize.value = layer.fontSize || 42;
                textColor.value = layer.color || '#111111';
                textBold.checked = String(layer.fontWeight) === '700';
                textAlign.value = layer.align || 'left';
            }

            if (isImage) {
                imageWidth.value = layer.width || 220;
                imageHeight.value = layer.height || 220;
            }
        }

        function syncHiddenTextInputs() {
            document.querySelectorAll('.js-ad-hidden-text').forEach((input) => {
                const key = input.getAttribute('data-key');
                if (!key) return;
                const layer = designer.layers.find((item) => item.type === 'text' && item.sourceTag === key);
                input.value = layer ? layer.text : getDefaultValue(key);
            });
        }

        function render() {
            stage.innerHTML = '';
            designer.layers.forEach((layer) => {
                const node = document.createElement('div');
                node.className = 'banner-designer-layer ' + (layer.type === 'text' ? 'text-layer' : 'image-layer') + (layer.id === designer.activeId ? ' active' : '');
                node.dataset.layerId = layer.id;
                node.style.left = layer.x + 'px';
                node.style.top = layer.y + 'px';
                node.style.zIndex = String(layer.type === 'image' && layer.toBack ? 1 : 2);

                if (layer.locked) {
                    node.style.cursor = 'default';
                }

                if (layer.type === 'text') {
                    node.textContent = layer.text;
                    node.style.fontSize = layer.fontSize + 'px';
                    node.style.color = layer.color;
                    node.style.fontWeight = layer.fontWeight;
                    node.style.fontFamily = layer.fontFamily;
                    node.style.textAlign = layer.align;
                    node.style.whiteSpace = 'pre-line';
                    node.contentEditable = layer.locked ? 'false' : 'true';
                } else {
                    node.style.width = layer.width + 'px';
                    node.style.height = layer.height + 'px';
                    const img = document.createElement('img');
                    img.src = layer.src;
                    img.alt = 'Layer';
                    node.appendChild(img);
                }

                stage.appendChild(node);
            });
            syncControlsFromActive();
            syncHiddenTextInputs();
        }

        function setActiveLayer(layerId) {
            designer.activeId = layerId;
            render();
        }

        function bindControls() {
            stage.addEventListener('click', (event) => {
                const node = event.target.closest('.banner-designer-layer');
                if (!node) return;
                setActiveLayer(node.dataset.layerId);
            });

            stage.addEventListener('input', (event) => {
                const node = event.target.closest('.banner-designer-layer.text-layer');
                if (!node) return;
                const layer = designer.layers.find((item) => item.id === node.dataset.layerId);
                if (!layer || layer.locked) return;
                layer.text = (node.textContent || '').slice(0, 200);
                syncHiddenTextInputs();
            });

            stage.addEventListener('mousedown', (event) => {
                const node = event.target.closest('.banner-designer-layer');
                if (!node) return;
                const layer = designer.layers.find((item) => item.id === node.dataset.layerId);
                if (!layer || layer.locked) return;
                setActiveLayer(layer.id);
                const rect = stage.getBoundingClientRect();
                designer.drag = {
                    id: layer.id,
                    startX: event.clientX,
                    startY: event.clientY,
                    origX: layer.x,
                    origY: layer.y,
                    scaleX: designer.width / rect.width,
                    scaleY: designer.height / rect.height,
                };
                event.preventDefault();
            });

            document.addEventListener('mousemove', (event) => {
                if (!designer.drag) return;
                const layer = designer.layers.find((item) => item.id === designer.drag.id);
                if (!layer) return;
                const dx = (event.clientX - designer.drag.startX) * designer.drag.scaleX;
                const dy = (event.clientY - designer.drag.startY) * designer.drag.scaleY;
                layer.x = designer.drag.origX + dx;
                layer.y = designer.drag.origY + dy;
                ensureLayerBounds(layer);
                render();
            });

            document.addEventListener('mouseup', () => {
                designer.drag = null;
            });

            stage.addEventListener('wheel', (event) => {
                const node = event.target.closest('.banner-designer-layer.image-layer');
                if (!node) return;
                const layer = designer.layers.find((item) => item.id === node.dataset.layerId);
                if (!layer || layer.locked) return;
                const delta = event.deltaY < 0 ? 20 : -20;
                layer.width = clamp((layer.width || 220) + delta, 40, designer.width);
                if (layer.aspectRatio > 0) layer.height = Math.round(layer.width / layer.aspectRatio);
                ensureLayerBounds(layer);
                render();
                event.preventDefault();
            }, { passive: false });

            [fontFamily, fontSize, textColor, textBold, textAlign].forEach((control) => {
                control.addEventListener('input', () => {
                    const layer = getActiveLayer();
                    if (!layer || layer.type !== 'text' || layer.locked) return;
                    layer.fontFamily = fontFamily.value || 'Arial';
                    layer.fontSize = clamp(parseInt(fontSize.value || '42', 10), 8, 200);
                    layer.color = textColor.value || '#111111';
                    layer.fontWeight = textBold.checked ? '700' : '400';
                    layer.align = textAlign.value || 'left';
                    render();
                });
            });

            imageWidth.addEventListener('input', () => {
                const layer = getActiveLayer();
                if (!layer || layer.type !== 'image' || layer.locked) return;
                layer.width = clamp(parseInt(imageWidth.value || '220', 10), 40, designer.width);
                if (layer.aspectRatio > 0) layer.height = Math.round(layer.width / layer.aspectRatio);
                ensureLayerBounds(layer);
                render();
            });

            imageHeight.addEventListener('input', () => {
                const layer = getActiveLayer();
                if (!layer || layer.type !== 'image' || layer.locked) return;
                layer.height = clamp(parseInt(imageHeight.value || '220', 10), 40, designer.height);
                if (layer.aspectRatio > 0) layer.width = Math.round(layer.height * layer.aspectRatio);
                ensureLayerBounds(layer);
                render();
            });

            addTextLayerBtn.addEventListener('click', () => {
                addTextLayer('New Text', { x: 60, y: 60, fontSize: 40, color: '#111111', fontWeight: '700' });
                render();
            });

            removeLayerBtn.addEventListener('click', () => {
                const layer = getActiveLayer();
                if (!layer || layer.locked) return;
                designer.layers = designer.layers.filter((item) => item.id !== layer.id);
                designer.activeId = null;
                render();
            });

            templateBgInput.addEventListener('change', () => {
                const file = templateBgInput.files && templateBgInput.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const bgLayer = designer.layers.find((item) => item.type === 'image' && item.sourceTag === '__template_bg');
                    if (bgLayer) {
                        bgLayer.src = e.target.result;
                        render();
                    }
                };
                reader.readAsDataURL(file);
            });

            document.querySelectorAll('.js-ad-image').forEach((input) => {
                input.addEventListener('change', () => {
                    const key = input.getAttribute('data-key');
                    const file = input.files && input.files[0];
                    if (!key || !file || !file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        let layer = designer.layers.find((item) => item.type === 'image' && item.sourceTag === key);
                        if (!layer) {
                            addImageLayer(e.target.result, { sourceTag: key, x: 80, y: 80 });
                            return;
                        }
                        layer.src = e.target.result;
                        render();
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        function initDefaultLayers() {
            addImageLayer(templateBackgroundUrl, {
                x: 0,
                y: 0,
                width: designer.width,
                height: designer.height,
                sourceTag: '__template_bg',
                locked: true,
                toBack: true,
            });

            let offsetY = 60;
            document.querySelectorAll('.js-ad-hidden-text').forEach((input) => {
                const key = input.getAttribute('data-key');
                if (!key) return;
                const seed = (input.value || '').trim() || getDefaultValue(key);
                addTextLayer(seed, { x: 60, y: offsetY, fontSize: 42, color: '#111111', sourceTag: key });
                offsetY += 72;
            });
            render();
        }

        async function exportDesignerImage() {
            const canvas = document.createElement('canvas');
            canvas.width = designer.width;
            canvas.height = designer.height;
            const ctx = canvas.getContext('2d');
            if (!ctx) return '';

            const drawLayer = (index) => new Promise((resolve) => {
                if (index >= designer.layers.length) {
                    resolve();
                    return;
                }

                const layer = designer.layers[index];
                if (layer.type === 'image') {
                    const img = new Image();
                    img.onload = function () {
                        ctx.drawImage(img, layer.x, layer.y, layer.width, layer.height);
                        resolve(drawLayer(index + 1));
                    };
                    img.onerror = function () {
                        resolve(drawLayer(index + 1));
                    };
                    img.src = layer.src;
                    return;
                }

                ctx.fillStyle = layer.color || '#111111';
                ctx.font = `${layer.fontWeight || '700'} ${layer.fontSize || 42}px ${layer.fontFamily || 'Arial'}`;
                ctx.textAlign = layer.align || 'left';
                ctx.textBaseline = 'top';
                const lines = String(layer.text || '').split('\n');
                const lineHeight = Math.round((layer.fontSize || 42) * 1.2);
                lines.forEach((line, idx) => {
                    let x = layer.x;
                    if (ctx.textAlign === 'center') x += 150;
                    if (ctx.textAlign === 'right') x += 300;
                    ctx.fillText(line, x, layer.y + (idx * lineHeight), 320);
                });
                resolve(drawLayer(index + 1));
            });

            await drawLayer(0);
            return canvas.toDataURL('image/png');
        }

        form.addEventListener('submit', async (event) => {
            if (form.dataset.isSubmitting === '1') return;
            event.preventDefault();

            syncHiddenTextInputs();
            if (customHtmlInput) customHtmlInput.value = '';
            if (generatedImageDataInput) {
                generatedImageDataInput.value = await exportDesignerImage();
            }
            form.dataset.isSubmitting = '1';
            form.submit();
        });

        bindControls();
        initDefaultLayers();
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
