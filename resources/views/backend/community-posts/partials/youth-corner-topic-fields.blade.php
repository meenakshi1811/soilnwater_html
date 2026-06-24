@php
    $selectedOpportunityTypes = old('youth_corner_opportunity_types', data_get($post->meta, 'youth_corner_opportunity_types', []));
    $selectedSkills = old('youth_corner_skills', data_get($post->meta, 'youth_corner_skills', []));
    $selectedThemes = old('youth_corner_themes', data_get($post->meta, 'youth_corner_themes', []));
    $selectedCommunityService = old('youth_corner_community_service', data_get($post->meta, 'youth_corner_community_service', []));
    $selectedNetworkingOptions = old('youth_corner_networking_options', data_get($post->meta, 'youth_corner_networking_options', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Opportunity type</h5>
            <p class="text-muted mb-0 small">Tag opportunities shared or discussed in this post.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerOpportunityTypes() as $opportunityType)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_opportunity_types[]"
                        value="{{ $opportunityType }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($opportunityType, (array) $selectedOpportunityTypes, true))
                    >
                    <span class="form-check-label">{{ $opportunityType }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Skills</h5>
            <p class="text-muted mb-0 small">Select skills demonstrated or discussed in this post.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerSkills() as $skill)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_skills[]"
                        value="{{ $skill }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($skill, (array) $selectedSkills, true))
                    >
                    <span class="form-check-label">{{ $skill }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Career section</h5>
            <p class="text-muted mb-0 small">Optional career context for this post.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="youthCornerCareerArea">Career area</label>
    <select name="youth_corner_career_area" id="youthCornerCareerArea" class="form-select youth-corner-flow-field">
        <option value="">Select career area (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerCareerAreas() as $careerArea)
            <option value="{{ $careerArea }}" @selected(old('youth_corner_career_area', data_get($post->meta, 'youth_corner_career_area')) === $careerArea)>{{ $careerArea }}</option>
        @endforeach
    </select>
</div>

@include('backend.community-posts.partials.youth-corner-startup-fields', ['post' => $post])

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Themes</h5>
            <p class="text-muted mb-0 small">Tag themes that best describe this content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerThemes() as $theme)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_themes[]"
                        value="{{ $theme }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($theme, (array) $selectedThemes, true))
                    >
                    <span class="form-check-label">{{ $theme }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community service</h5>
            <p class="text-muted mb-0 small">Tag community service activities related to this post.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <p class="small text-muted mb-2">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::youthCornerCommunityServiceExamples()) }}</p>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerCommunityServiceActivities() as $activity)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_community_service[]"
                        value="{{ $activity }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($activity, (array) $selectedCommunityService, true))
                    >
                    <span class="form-check-label">{{ $activity }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Networking options</h5>
            <p class="text-muted mb-0 small">Let readers know how they can connect with you.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerNetworkingOptions() as $networkingOption)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_networking_options[]"
                        value="{{ $networkingOption }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($networkingOption, (array) $selectedNetworkingOptions, true))
                    >
                    <span class="form-check-label">{{ $networkingOption }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
