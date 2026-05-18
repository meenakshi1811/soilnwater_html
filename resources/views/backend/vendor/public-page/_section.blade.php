<div class="vendor-section-block" data-section-index="{{ $index }}">
    <div class="d-flex justify-content-between mb-2">
        <span class="text-secondary small">Section</span>
        <button type="button" class="btn btn-sm btn-outline-danger js-remove-section"><i class="fa-solid fa-trash"></i></button>
    </div>
    @if($section?->id)
        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section->id }}">
    @endif
    <input type="hidden" name="sections[{{ $index }}][_delete]" value="0" class="section-delete-flag">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Section Image</label>
            @if($section?->image_path)
                <img src="{{ asset($section->image_path) }}" alt="" class="img-fluid rounded mb-2 d-block" style="max-height:180px;object-fit:cover;">
            @endif
            <input type="file" name="sections[{{ $index }}][image]" class="form-control form-control-sm" accept="image/*">
        </div>
        <div class="col-md-8">
            <input type="text" name="sections[{{ $index }}][title]" class="d-none" value="{{ old('sections.'.$index.'.title', $section?->title) }}" data-sync-input="section-title-{{ $index }}">
            <textarea name="sections[{{ $index }}][content]" class="d-none" rows="6" data-sync-input="section-content-{{ $index }}">{{ old('sections.'.$index.'.content', $section?->content) }}</textarea>

            <h5 class="vendor-live-editable mb-2" contenteditable="true" data-sync-target="section-title-{{ $index }}">{{ old('sections.'.$index.'.title', $section?->title ?: 'Section title') }}</h5>
            <p class="vendor-live-editable mb-0" contenteditable="true" data-sync-target="section-content-{{ $index }}">{{ old('sections.'.$index.'.content', $section?->content ?: 'Section content...') }}</p>
        </div>
    </div>
</div>
