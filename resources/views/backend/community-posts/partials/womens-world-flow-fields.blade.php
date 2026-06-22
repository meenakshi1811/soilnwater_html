@php
    $selectedWomensWorldCategory = old('womens_world_category', data_get($post->meta, 'womens_world_category', $post->category));
    $selectedWomensWorldAudiences = old('womens_world_target_audience', data_get($post->meta, 'womens_world_target_audience', []));
    $selectedFeaturedTopics = old('womens_world_featured_topics', data_get($post->meta, 'womens_world_featured_topics', []));
    $selectedWomensWorldThemes = old('womens_world_themes', data_get($post->meta, 'womens_world_themes', []));
    $selectedWomensWorldLifeStage = old('womens_world_life_stage', data_get($post->meta, 'womens_world_life_stage'));
    $flowPlacement = $placement ?? 'all';
    $showWomensWorldSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showWomensWorldRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showWomensWorldSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Main category</h5>
            <p class="text-muted mb-0 small">Choose the primary topic for this Women's World post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="womensWorldCategory">Main category <span class="text-danger">*</span></label>
    <select name="womens_world_category" id="womensWorldCategory" class="form-select womens-world-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldCategoryGroups() as $groupLabel => $categories)
            <optgroup label="{{ $groupLabel }}">
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected($selectedWomensWorldCategory === $category)>{{ $category }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">
        Examples: Personal Experiences, Career &amp; Professional Growth, Women Entrepreneurship, Health &amp; Wellness, Motherhood &amp; Parenting, Education, Financial Independence, Self Development, Relationships &amp; Family, Women Empowerment, Success Stories, Life Skills, Social Issues, Legal Awareness, Safety &amp; Security, Senior Women's Corner
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Content type</h5>
            <p class="text-muted mb-0 small">How this content should be classified for readers.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    <label class="form-label" for="womensWorldContentType">Content type <span class="text-danger">*</span></label>
    <select name="womens_world_content_type" id="womensWorldContentType" class="form-select womens-world-required" required>
        <option value="">Select content type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldContentTypeGroups() as $groupLabel => $groupOptions)
            <optgroup label="{{ $groupLabel }}">
                @foreach($groupOptions as $contentType)
                    <option value="{{ $contentType }}" @selected(old('womens_world_content_type', data_get($post->meta, 'womens_world_content_type')) === $contentType)>{{ $contentType }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldTargetAudiences() as $audience)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="womens_world_target_audience[]"
                        value="{{ $audience }}"
                        class="form-check-input womens-world-audience-required"
                        @checked(in_array($audience, (array) $selectedWomensWorldAudiences, true))
                    >
                    <span class="form-check-label">{{ $audience }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured topic</h5>
            <p class="text-muted mb-0 small">Optional spotlight topics for listings and discovery.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldFeaturedTopicGroups() as $groupLabel => $topics)
            @foreach($topics as $topic)
                <div class="col-md-4 col-sm-6">
                    <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                        <input
                            type="checkbox"
                            name="womens_world_featured_topics[]"
                            value="{{ $topic }}"
                            class="form-check-input"
                            @checked(in_array($topic, (array) $selectedFeaturedTopics, true))
                        >
                        <span class="form-check-label">{{ $topic }}</span>
                    </label>
                </div>
            @endforeach
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Life stage</h5>
            <p class="text-muted mb-0 small">Useful for content recommendations.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="womensWorldLifeStage">Life stage</label>
    <select name="womens_world_life_stage" id="womensWorldLifeStage" class="form-select">
        <option value="">Select life stage (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldLifeStageGroups() as $groupLabel => $stages)
            <optgroup label="{{ $groupLabel }}">
                @foreach($stages as $stage)
                    <option value="{{ $stage }}" @selected($selectedWomensWorldLifeStage === $stage)>{{ $stage }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card story-flow-card--theme border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Themes</h5>
            <p class="text-muted mb-0 small">Select all themes that apply to this content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldThemeGroups() as $groupLabel => $themes)
            @foreach($themes as $theme)
                <div class="col-md-4 col-sm-6">
                    <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                        <input
                            type="checkbox"
                            name="womens_world_themes[]"
                            value="{{ $theme }}"
                            class="form-check-input"
                            @checked(in_array($theme, (array) $selectedWomensWorldThemes, true))
                        >
                        <span class="form-check-label">{{ $theme }}</span>
                    </label>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
@endif

@if($showWomensWorldRest)
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
    <div id="communityWomensWorldFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--gallery border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Image gallery</h5>
            <p class="text-muted mb-0 small">Optional visual gallery alongside your content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <p class="small text-muted mb-3">Examples: Events, Business Activities, Training Programs, Achievements, Community Work</p>
    <input type="file" name="womens_world_gallery[]" id="womensWorldGallery" class="form-control" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
    @if(!empty(data_get($post->meta, 'womens_world_gallery')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'womens_world_gallery', []) as $galleryImage)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_womens_world_gallery[]" value="{{ data_get($galleryImage, 'path') }}" class="form-check-input">
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
    <label class="form-label" for="womensWorldVideoType">Video type</label>
    <select name="womens_world_video_type" id="womensWorldVideoType" class="form-select mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldVideoTypeGroups() as $groupLabel => $videoTypes)
            <optgroup label="{{ $groupLabel }}">
                @foreach($videoTypes as $videoType)
                    <option value="{{ $videoType }}" @selected(old('womens_world_video_type', data_get($post->meta, 'womens_world_video_type')) === $videoType)>{{ $videoType }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <small class="text-muted d-block mb-1">Examples:</small>
    <ul class="small text-muted mb-3 ps-3">
        <li>Motivational Talk, Business Introduction, Workshop Recording</li>
        <li>Awareness Video, Interview</li>
    </ul>
    <div id="communityWomensWorldVideoSlot"></div>
</div>

@include('backend.community-posts.partials.womens-world-audio-fields', ['post' => $post])

<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Optional — for local relevance. Country, state, district, and city only.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="alert alert-warning py-2 px-3 small mb-3">
        For safety reasons, exact addresses should never be displayed.
    </div>
    <div id="communityWomensWorldLocationSlot"></div>
</div>

@include('backend.community-posts.partials.womens-world-engagement-fields', ['post' => $post])
@include('backend.community-posts.partials.womens-world-privacy-fields', ['post' => $post])
@include('backend.community-posts.partials.womens-world-resource-fields', ['post' => $post])
@endif
