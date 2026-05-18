<div class="vendor-section-block vendor-store-section {{ ($index !== '__INDEX__' && is_numeric($index) && ((int)$index % 2 === 1)) ? 'alt' : '' }} p-3 rounded mb-4 border" data-section-index="{{ $index }}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge bg-light text-dark border"><i class="fa-solid fa-layer-group me-1"></i> Section {{ is_numeric($index) ? ((int)$index + 1) : 'new' }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger js-remove-section" title="Remove section"><i class="fa-solid fa-trash"></i></button>
    </div>
    @if($section?->id)
        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">
    @endif
    <input type="hidden" name="sections[{{ $index }}][_delete]" value="0" class="section-delete-flag">
    <input type="text" name="sections[{{ $index }}][title]" class="d-none" value="{{ old('sections.'.$index.'.title', $section?->title) }}" data-sync-input="section-title-{{ $index }}">
    <textarea name="sections[{{ $index }}][content]" class="d-none" rows="6" data-sync-input="section-content-{{ $index }}">{{ old('sections.'.$index.'.content', $section?->content) }}</textarea>

    <div class="vendor-section-style-panel mb-3">
        <p class="small fw-semibold mb-2 text-primary"><i class="fa-solid fa-palette me-1"></i> Text styling</p>
        <p class="small text-muted mb-2">Choose what to style, pick colors and size, then click the title or content below to edit text.</p>
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-6">
                <label class="form-label small mb-1">Style this part</label>
                <select class="form-select form-select-sm" data-section-target>
                    <option value="title">Section title</option>
                    <option value="content" selected>Section content</option>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small mb-1"><i class="fa-solid fa-font me-1"></i> Text color</label>
                <input type="color" class="form-control form-control-color form-control-sm w-100" data-section-style="color" value="#1f2937" title="Text color">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small mb-1"><i class="fa-solid fa-fill-drip me-1"></i> Highlight</label>
                <input type="color" class="form-control form-control-color form-control-sm w-100" data-section-style="backgroundColor" value="#ffffff" title="Background highlight">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small mb-1">Font size</label>
                <select class="form-select form-select-sm" data-section-command="fontSize">
                    <option value="3">Default</option>
                    <option value="2">Small</option>
                    <option value="4">Large</option>
                    <option value="5">Extra large</option>
                    <option value="6">Huge</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-dark" data-section-command="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-dark" data-section-command="insertUnorderedList" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-5">
            <label class="form-label small fw-semibold">Section image</label>
            <div class="vendor-section-image-wrap">
                <img src="{{ $section?->image_path ? asset($section->image_path) : 'https://via.placeholder.com/900x500/e8ecef/6b7280?text=Click+to+add+image' }}" alt="" class="img-fluid rounded d-block section-live-image">
                <label class="vendor-section-image-upload btn btn-sm btn-light">
                    <i class="fa-solid fa-upload me-1"></i> Change image
                    <input type="file" name="sections[{{ $index }}][image]" class="d-none js-section-image-input" accept="image/*">
                </label>
            </div>
        </div>
        <div class="col-lg-7">
            <label class="form-label small text-muted mb-1">Section title (click to edit)</label>
            <h2 class="vendor-live-editable mb-3" contenteditable="true" data-sync-target="section-title-{{ $index }}">{!! old('sections.'.$index.'.title', $section?->title ?: 'Section title') !!}</h2>
            <label class="form-label small text-muted mb-1">Section content (click to edit)</label>
            <div class="vendor-live-editable vendor-section-content-editor border rounded p-3 bg-white" contenteditable="true" data-sync-target="section-content-{{ $index }}" data-sync-html="1">{!! old('sections.'.$index.'.content', $section?->content ?: '<p>Write your section content here...</p>') !!}</div>
        </div>
    </div>
</div>
