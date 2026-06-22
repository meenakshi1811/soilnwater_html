@php
    $selectedWomensWorldCategory = old('womens_world_category', data_get($post->meta, 'womens_world_category', $post->category));
    $selectedWomensWorldAudiences = old('womens_world_target_audience', data_get($post->meta, 'womens_world_target_audience', []));
    $flowPlacement = $placement ?? 'all';
    $showWomensWorldSetup = in_array($flowPlacement, ['all', 'setup'], true);
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
@endif
