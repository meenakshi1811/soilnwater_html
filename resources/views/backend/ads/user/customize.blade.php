@extends('backend.layouts.app')

@section('title', 'Customize Ad')

@php
    $schema = is_array($template->schema_json) ? $template->schema_json : [];
    $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];

    $layoutHtml = (string) ($template->layout_html ?? '');
    $usedKeys = [];

    if ($layoutHtml !== '') {
        preg_match_all('/\{\{\s*([a-zA-Z][a-zA-Z0-9_]*)\s*\}\}/', $layoutHtml, $matches);
        $placeholderKeys = $matches[1] ?? [];

        preg_match_all('/data-ad-key=[\"\']([a-zA-Z][a-zA-Z0-9_]*)[\"\']/', $layoutHtml, $imgMatches);
        $imageKeys = $imgMatches[1] ?? [];

        $usedKeys = array_values(array_unique(array_map('strtolower', array_merge($placeholderKeys, $imageKeys))));
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
        </div>
    </div>

    <div class="chart-card">
        <div id="adCustomizeAlert" class="alert d-none" role="alert"></div>
        <form method="POST" action="{{ route('ads.store', ['sizeType' => $sizeType, 'template' => $template->id]) }}" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ad Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror js-ad-title" maxlength="140" placeholder="e.g. Beauty Clinic — 50% OFF">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ads-fields">
                        @foreach($fields as $field)
                            @php
                                $key = (string) ($field['key'] ?? '');
                                $label = (string) ($field['label'] ?? $key);
                                $type = (string) ($field['type'] ?? 'text');
                                $required = (bool) ($field['required'] ?? false);
                                $max = (int) ($field['max'] ?? 0);
                                $isUsedInTemplate = $key !== '' && in_array(strtolower($key), $usedKeys, true);
                            @endphp
                            @if($key !== '' && ($required || $isUsedInTemplate))
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
                                    @elseif(($field['multiline'] ?? false) === true)
                                        <textarea
                                            name="{{ $key }}"
                                            class="form-control @error($key) is-invalid @enderror js-ad-text"
                                            rows="3"
                                            data-key="{{ $key }}"
                                            maxlength="{{ $max > 0 ? $max : 500 }}"
                                            placeholder="Enter {{ strtolower($label) }}"
                                        >{{ old($key) }}</textarea>
                                        @error($key)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @else
                                        <input
                                            type="text"
                                            name="{{ $key }}"
                                            value="{{ old($key) }}"
                                            class="form-control @error($key) is-invalid @enderror js-ad-text"
                                            data-key="{{ $key }}"
                                            maxlength="{{ $max > 0 ? $max : 255 }}"
                                            placeholder="Enter {{ strtolower($label) }}"
                                        >
                                        @error($key)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="alert alert-info mb-0">
                        After submission, your ad will be reviewed by admin before it goes live.
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <h5 class="mb-0">Live Preview</h5>
                        <span class="text-secondary small">{{ $size['w'] }}×{{ $size['h'] }}</span>
                    </div>

                    <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] }};">
                        <div class="ads-live-preview-inner" id="adPreview">
                            {!! $template->layout_html !!}
                        </div>
                    </div>
                    <script type="application/json" id="adTemplateHtml">@json($template->layout_html)</script>
                    <script type="application/json" id="adTemplateFieldKeys">@json($fields)</script>

                    <small class="text-secondary d-block mt-2">Tip: Upload images and type text to see the preview update.</small>
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
        const preview = document.getElementById('adPreview');
        if (!preview) return;

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

        function computeTextReplacements() {
            const map = {};

            const titleInput = document.querySelector('.js-ad-title');
            const titleVal = titleInput ? (titleInput.value || '').toString().trim() : '';
            map.title = titleVal;

            document.querySelectorAll('.js-ad-text').forEach((el) => {
                const key = el.getAttribute('data-key');
                if (!key) return;
                const val = (el.value || '').toString();
                map[key] = val;
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
                html = html.replace(re, escapeHtml(replacements[key] || ''));
            });

            preview.innerHTML = html;
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
        }

        document.querySelectorAll('.js-ad-text').forEach((el) => {
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

        updatePreview();
    })();
</script>
@endpush

