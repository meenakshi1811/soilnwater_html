<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Cover image for homepage cards, category listings, and social sharing.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <p class="mb-2"><strong>Cover image</strong> <span class="text-muted fw-normal">(recommended)</span></p>
    <ul class="small text-muted mb-3">
        <li>Homepage cards</li>
        <li>Category listings</li>
        <li>Social sharing</li>
    </ul>
    <div id="communityStudentCornerFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--gallery border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Image gallery</h5>
            <p class="text-muted mb-0 small">Optional visual gallery alongside your content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <p class="small text-muted mb-3">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::studentCornerGalleryExamples()) }}</p>
    <input type="file" name="student_corner_gallery[]" id="studentCornerGallery" class="form-control student-corner-flow-field" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
    @if(!empty(data_get($post->meta, 'student_corner_gallery')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'student_corner_gallery', []) as $galleryImage)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_student_corner_gallery[]" value="{{ data_get($galleryImage, 'path') }}" class="form-check-input student-corner-flow-field">
                    <span class="form-check-label">Remove {{ data_get($galleryImage, 'name', 'gallery image') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card story-flow-card--video border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video section</h5>
            <p class="text-muted mb-0 small">Optional video. You can also embed videos inside the rich text editor.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="studentCornerVideoType">Video type</label>
    <select name="student_corner_video_type" id="studentCornerVideoType" class="form-select student-corner-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerVideoTypes() as $videoType)
            <option value="{{ $videoType }}" @selected(old('student_corner_video_type', data_get($post->meta, 'student_corner_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mb-1">Examples:</small>
    <ul class="small text-muted mb-3 ps-3">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerVideoTypes() as $videoType)
            <li>{{ $videoType }}</li>
        @endforeach
    </ul>
    <div id="communityStudentCornerVideoSlot"></div>
</div>
