@php
    $selectedAstroCategory = old('astro_consultancy_category', data_get($post->meta, 'astro_consultancy_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showAstroSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showAstroRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showAstroSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Post type</h5>
            <p class="text-muted mb-0 small">This determines the workflow for your astro consultancy post.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="astroConsultancyPostType">Post type <span class="text-danger">*</span></label>
    <select name="astro_consultancy_post_type" id="astroConsultancyPostType" class="form-select astro-consultancy-required" required>
        <option value="">Select post type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyPostTypes() as $postType)
            <option value="{{ $postType }}" @selected(old('astro_consultancy_post_type', data_get($post->meta, 'astro_consultancy_post_type')) === $postType)>{{ $postType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Consultancy category</h5>
            <p class="text-muted mb-0 small">Choose the primary tradition or discipline for this post.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="astroConsultancyCategory">Main category <span class="text-danger">*</span></label>
    <select name="astro_consultancy_category" id="astroConsultancyCategory" class="form-select astro-consultancy-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedAstroCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyTargetAudiences() as $audience)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="astro_consultancy_target_audience[]" value="{{ $audience }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($audience, (array) old('astro_consultancy_target_audience', data_get($post->meta, 'astro_consultancy_target_audience', [])), true))>
                <span class="form-check-label">{{ $audience }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Consultation topic</h5>
            <p class="text-muted mb-0 small">What life areas does this guidance relate to?</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyConsultationTopics() as $topic)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="astro_consultancy_consultation_topics[]" value="{{ $topic }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($topic, (array) old('astro_consultancy_consultation_topics', data_get($post->meta, 'astro_consultancy_consultation_topics', [])), true))>
                <span class="form-check-label">{{ $topic }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white h-100">
            <label class="form-label" for="astroConsultancyContentLanguage">Language</label>
            <select name="astro_consultancy_content_language" id="astroConsultancyContentLanguage" class="form-select astro-consultancy-flow-field">
                <option value="">Select language (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyContentLanguages() as $language)
                    <option value="{{ $language }}" @selected(old('astro_consultancy_content_language', data_get($post->meta, 'astro_consultancy_content_language')) === $language)>{{ $language }}</option>
                @endforeach
            </select>
            <small class="text-muted">Primary language of the guidance shared.</small>
        </div>
    </div>
</div>
@endif

@if($showAstroRest)
@include('backend.community-posts.partials.astro-consultancy-conditional-sections', ['post' => $post])
@include('backend.community-posts.partials.astro-consultancy-media-fields', ['post' => $post])
@include('backend.community-posts.partials.astro-consultancy-engagement-fields', ['post' => $post])
@endif
