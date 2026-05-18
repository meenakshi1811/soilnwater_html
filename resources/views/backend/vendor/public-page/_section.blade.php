<div class="vendor-section-block vendor-store-section {{ ($index !== '__INDEX__' && is_numeric($index) && ((int)$index % 2 === 1)) ? 'alt' : '' }} p-3 rounded mb-4 border" data-section-index="{{ $index }}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-secondary small fw-semibold">Custom Section Preview</span>
        <button type="button" class="btn btn-sm btn-outline-danger js-remove-section"><i class="fa-solid fa-trash"></i></button>
    </div>
    @if($section?->id)
        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">
    @endif
    <input type="hidden" name="sections[{{ $index }}][_delete]" value="0" class="section-delete-flag">
    <input type="text" name="sections[{{ $index }}][title]" class="d-none" value="{{ old('sections.'.$index.'.title', $section?->title) }}" data-sync-input="section-title-{{ $index }}">
    <textarea name="sections[{{ $index }}][content]" class="d-none" rows="6" data-sync-input="section-content-{{ $index }}">{{ old('sections.'.$index.'.content', $section?->content) }}</textarea>

    <div class="vendor-form-card p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-1">Apply To</label>
                <select class="form-select form-select-sm" data-section-target>
                    <option value="title">Section title</option>
                    <option value="content" selected>Section content</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">Text Color</label>
                <input type="color" class="form-control form-control-color form-control-sm" data-section-style="color" value="#1f2937">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">Background</label>
                <input type="color" class="form-control form-control-color form-control-sm" data-section-style="backgroundColor" value="#ffffff">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">Font Size</label>
                <select class="form-select form-select-sm" data-section-command="fontSize">
                    <option value="3">Default</option>
                    <option value="2">Small</option>
                    <option value="4">Large</option>
                    <option value="5">XL</option>
                    <option value="6">XXL</option>
                </select>
            </div>
            <div class="col-auto"><button type="button" class="btn btn-sm btn-outline-dark mt-4" data-section-command="bold"><i class="fa-solid fa-bold me-1"></i>Bold</button></div>
            <div class="col-auto"><button type="button" class="btn btn-sm btn-outline-dark mt-4" data-section-command="insertUnorderedList"><i class="fa-solid fa-list-ul me-1"></i>UL/LI</button></div>
        </div>
    </div>

    <div class="row g-4 align-items-center">
        <div class="col-lg-5">
            <label class="form-label">Section Image</label>
            <img src="{{ $section?->image_path ? asset($section->image_path) : 'https://via.placeholder.com/900x500?text=Section+Image+Preview' }}" alt="" class="img-fluid rounded mb-2 d-block section-live-image" style="max-height:220px;min-height:140px;width:100%;object-fit:cover;">
            <input type="file" name="sections[{{ $index }}][image]" class="form-control form-control-sm js-section-image-input" accept="image/*">
        </div>
        <div class="col-lg-7">
            <h2 class="vendor-live-editable mb-2" contenteditable="true" data-sync-target="section-title-{{ $index }}">{{ old('sections.'.$index.'.title', $section?->title ?: 'Section title') }}</h2>
            <div class="vendor-live-editable border rounded p-3 bg-white" contenteditable="true" data-sync-target="section-content-{{ $index }}" data-sync-html="1">{!! old('sections.'.$index.'.content', $section?->content ?: '<p>Section content...</p>') !!}</div>
        </div>
    </div>
</div>
