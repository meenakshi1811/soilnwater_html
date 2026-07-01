@php
    $selectedCompetitionCategory = old('competitions_category', data_get($post->meta, 'competitions_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showCompSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showCompRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showCompSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Competition type</h5>
            <p class="text-muted mb-0 small">This controls the complete workflow.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="competitionsCompetitionType">Competition type <span class="text-danger">*</span></label>
    <select name="competitions_competition_type" id="competitionsCompetitionType" class="form-select competitions-required" required>
        <option value="">Select competition type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsCompetitionTypes() as $type)
            <option value="{{ $type }}" @selected(old('competitions_competition_type', data_get($post->meta, 'competitions_competition_type')) === $type)>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Competition category</h5>
            <p class="text-muted mb-0 small">Choose the main category for this competition.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="competitionsCategory">Main category <span class="text-danger">*</span></label>
    <select name="competitions_category" id="competitionsCategory" class="form-select competitions-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedCompetitionCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Eligibility</h5>
            <p class="text-muted mb-0 small">Who can participate in this competition?</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsEligibilityGroups() as $group)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="competitions_eligibility[]" value="{{ $group }}" class="form-check-input competitions-flow-field" @checked(in_array($group, (array) old('competitions_eligibility', data_get($post->meta, 'competitions_eligibility', [])), true))>
                <span class="form-check-label">{{ $group }}</span>
            </label>
        @endforeach
    </div>
</div>
@endif

@if($showCompRest)
@include('backend.community-posts.partials.competitions-organizer-fields', ['post' => $post])
@include('backend.community-posts.partials.competitions-submission-fields', ['post' => $post])
@include('backend.community-posts.partials.competitions-judging-fields', ['post' => $post])
@include('backend.community-posts.partials.competitions-unique-features-fields', ['post' => $post])
@include('backend.community-posts.partials.competitions-engagement-fields', ['post' => $post])
@endif
