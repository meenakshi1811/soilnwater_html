<div class="vendor-section-block vendor-store-section {{ ($index !== '__INDEX__' && is_numeric($index) && ((int)$index % 2 === 1)) ? 'alt' : '' }} p-3 rounded mb-4 border" data-section-index="{{ $index }}" draggable="true">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <span class="badge bg-light text-dark border js-section-label"><i class="fa-solid fa-layer-group me-1"></i> Section {{ is_numeric($index) ? ((int)$index + 1) : 'new' }}</span>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-secondary vendor-section-reorder-btn js-drag-handle" title="Hold and drag to reorder this section" aria-label="Drag to reorder section">
                <i class="fa-solid fa-grip-vertical me-1"></i>
                <span>Reorder</span>
            </button>
            <div class="btn-group btn-group-sm vendor-section-move-group" role="group" aria-label="Move section without dragging">
                <button type="button" class="btn btn-outline-secondary js-move-section-up" title="Move section up" aria-label="Move section up"><i class="fa-solid fa-arrow-up"></i></button>
                <button type="button" class="btn btn-outline-secondary js-move-section-down" title="Move section down" aria-label="Move section down"><i class="fa-solid fa-arrow-down"></i></button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-section" title="Remove section"><i class="fa-solid fa-trash"></i></button>
        </div>
    </div>
    @if($section?->id)
        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">
    @endif
    <input type="hidden" name="sections[{{ $index }}][_delete]" value="0" class="section-delete-flag">
    <input type="hidden" name="sections[{{ $index }}][type]" value="{{ old('sections.'.$index.'.type', 'image_text') }}" data-section-type-input>
    <input type="text" name="sections[{{ $index }}][title]" class="d-none" value="{{ old('sections.'.$index.'.title', $section?->title) }}" data-sync-input="section-title-{{ $index }}">
    <textarea name="sections[{{ $index }}][content]" class="d-none" rows="6" data-sync-input="section-content-{{ $index }}">{{ old('sections.'.$index.'.content', $section?->content) }}</textarea>
    <div class="row g-3 mb-3 js-video-fields d-none">
        <div class="col-md-6">
            <label class="form-label small">Upload video (MP4/WEBM/OGG, max 50MB)</label>
            <input type="file" name="sections[{{ $index }}][video_file]" class="form-control form-control-sm" accept="video/mp4,video/webm,video/ogg">
        </div>
        <div class="col-md-6">
            <label class="form-label small">Or YouTube embed link</label>
            <input type="url" name="sections[{{ $index }}][youtube_url]" class="form-control form-control-sm" placeholder="https://www.youtube.com/embed/...">
        </div>
    </div>

    <div class="row g-4 align-items-start mb-3">
        <div class="col-12">
            <label class="form-label small text-muted mb-1">Section title — click here to edit &amp; style</label>
            <div class="vendor-live-editable vendor-section-title-editor h2 mb-3" contenteditable="true" data-sync-target="section-title-{{ $index }}" data-section-field="title" role="textbox">{!! old('sections.'.$index.'.title', $section?->title ?: 'Section title') !!}</div>
            <label class="form-label small text-muted mb-1">Section content — click here to edit &amp; style</label>
            <div class="vendor-live-editable vendor-section-content-editor border rounded p-3 bg-white" contenteditable="true" data-sync-target="section-content-{{ $index }}" data-sync-html="1" data-section-field="content" role="textbox">{!! old('sections.'.$index.'.content', $section?->content ?: '<p>Write your section content here...</p>') !!}</div>
        </div>
    </div>

    <div class="vendor-section-style-panel">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <p class="small fw-semibold mb-0 text-primary"><i class="fa-solid fa-palette me-1"></i> Styling tools</p>
            <span class="badge bg-primary-subtle text-primary border vendor-section-active-badge" data-section-active-label>Click title or content above</span>
        </div>
        <p class="small text-muted mb-2">Click the <strong>title</strong> or <strong>content</strong> box above — colors and size apply to whichever you clicked last.</p>
        <div class="row g-2 align-items-end">
            <div class="col-md-2 col-6">
                <label class="form-label small mb-1"><i class="fa-solid fa-font me-1"></i> Text color</label>
                <input type="color" class="form-control form-control-color form-control-sm w-100" data-section-style="color" value="#1f2937" title="Text color">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small mb-1">Font size</label>
                <select class="form-select form-select-sm" data-section-command="fontSize">
                    <option value="">Default</option>
                    <option value="16px">Small</option>
                    <option value="18px">Medium</option>
                    <option value="22px">Large</option>
                    <option value="28px">Extra large</option>
                    <option value="36px">Huge</option>
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
</div>
