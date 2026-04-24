@extends('backend.layouts.app')

@php
    $isEdit = !is_null($template);
    $schemaString = $isEdit ? json_encode($template->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : json_encode([
        'fields' => [
            ['key' => 'headline', 'label' => 'Headline', 'type' => 'text', 'required' => true, 'max' => 60],
            ['key' => 'subheadline', 'label' => 'Subheadline', 'type' => 'text', 'required' => false, 'max' => 90],
            ['key' => 'cta', 'label' => 'CTA Button Text', 'type' => 'text', 'required' => false, 'max' => 20],
            ['key' => 'image_main', 'label' => 'Main Image', 'type' => 'image', 'required' => false],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

@section('title', $isEdit ? 'Edit Ad Template' : 'New Ad Template')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">{{ $isEdit ? 'Edit Template' : 'Create Template' }}</h2>
            <p class="mb-0 text-secondary">Define fields (schema JSON) and a HTML layout using placeholders like <code>{{ '{' }}{{ '{' }}headline{{ '}' }}{{ '}' }}</code>.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">Please fix the highlighted fields and try again.</div>
    @endif

    <div class="chart-card">
        <div id="adminAdTemplateAlert" class="alert d-none" role="alert"></div>
        <form method="POST" action="{{ $isEdit ? route('admin.ads.templates.update', $template) : route('admin.ads.templates.store') }}" enctype="multipart/form-data">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Size Type <span class="text-danger">*</span></label>
                        <select name="size_type" class="form-select @error('size_type') is-invalid @enderror" {{ $isEdit ? 'disabled' : '' }}>
                            <option value="">— Select —</option>
                            @foreach($sizes as $key => $s)
                                <option value="{{ $key }}" {{ old('size_type', $template?->size_type) === $key ? 'selected' : '' }}>
                                    {{ $s['name'] }} ({{ $s['w'] }}×{{ $s['h'] }}){{ ($s['admin_only'] ?? false) ? ' • Admin placement' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($isEdit)
                            <input type="hidden" name="size_type" value="{{ $template->size_type }}">
                        @endif
                        @error('size_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $template?->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="120">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" value="{{ old('description', $template?->description) }}" class="form-control @error('description') is-invalid @enderror" maxlength="255">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preview Image</label>
                        <input type="file" name="preview_image" class="form-control @error('preview_image') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                        @error('preview_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($isEdit && $template?->preview_image)
                            <div class="mt-2">
                                <img src="{{ asset($template->preview_image) }}" alt="preview" style="max-width: 220px;border-radius:10px;border:1px solid rgba(0,0,0,0.08);">
                            </div>
                        @endif
                    </div>

                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Schema JSON <span class="text-danger">*</span></label>
                        <textarea name="schema_json" rows="10" class="form-control font-monospace @error('schema_json') is-invalid @enderror" placeholder='{"fields":[{"key":"headline","label":"Headline","type":"text","required":true,"max":60}]}'>{{ old('schema_json', $schemaString) }}</textarea>
                        @error('schema_json')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary d-block mt-1">Supported field types: <strong>text</strong>, <strong>image</strong>. Optional: <code>multiline</code>, <code>required</code>, <code>max</code>.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Layout HTML <span class="text-danger">*</span></label>
                        <textarea name="layout_html" rows="12" class="form-control font-monospace @error('layout_html') is-invalid @enderror" placeholder="<div>...{{ '{' }}{{ '{' }}headline{{ '}' }}{{ '}' }}...</div>">{{ old('layout_html', $template?->layout_html) }}</textarea>
                        @error('layout_html')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary d-block mt-1">For image fields, place an <code>&lt;img data-ad-key=&quot;image_main&quot; src=&quot;...&quot;&gt;</code> so the user preview updates on upload.</small>
                    </div>

                    <div class="border rounded-3 p-3 bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <h6 class="mb-0">Live Preview</h6>
                            <small class="text-secondary">Updates instantly as you edit schema/layout.</small>
                        </div>

                        <div id="adminTemplatePreviewPlaceholders" class="d-flex flex-wrap gap-2 mb-3"></div>

                        <div class="ads-live-preview ads-admin-template-live-preview" id="adminTemplateLivePreviewWrap" style="aspect-ratio: 1 / 1;">
                            <div class="ads-live-preview-inner" id="adminTemplateLivePreview"></div>
                        </div>
                        <small id="adminTemplateLivePreviewMessage" class="d-block mt-2 text-secondary">
                            Add HTML and placeholders (like <code>{{ '{' }}{{ '{' }}headline{{ '}' }}{{ '}' }}</code>) to see your final rendering.
                        </small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.ads.templates.index') }}" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-primary ems-btn-primary px-5">
                    <i class="fa-solid fa-floppy-disk me-2"></i>{{ $isEdit ? 'Update Template' : 'Create Template' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/ads.js') }}?v={{ now()->timestamp }}"></script>
@endpush
