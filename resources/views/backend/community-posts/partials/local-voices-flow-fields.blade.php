@php
    $selectedLocalVoiceCategory = old('local_voice_category', data_get($post->meta, 'local_voice_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showLocalVoicesSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showLocalVoicesRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showLocalVoicesSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Voice type</h5>
            <p class="text-muted mb-0 small">What would you like to share with your community?</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="localVoiceType">What would you like to share? <span class="text-danger">*</span></label>
    <select name="local_voice_type" id="localVoiceType" class="form-select local-voices-required" required>
        <option value="">Select voice type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceTypes() as $voiceType)
            <option value="{{ $voiceType }}" @selected(old('local_voice_type', data_get($post->meta, 'local_voice_type')) === $voiceType)>{{ $voiceType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Category</h5>
            <p class="text-muted mb-0 small">Choose the main topic for this local voice post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="localVoiceCategory">Main category <span class="text-danger">*</span></label>
    <select name="local_voice_category" id="localVoiceCategory" class="form-select local-voices-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedLocalVoiceCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

@include('backend.community-posts.partials.local-voices-metadata-fields', ['post' => $post])
@endif

@if($showLocalVoicesRest)
<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location details</h5>
            <p class="text-muted mb-0 small">Local Voices must be location-centric. Add country, state, district, city/town/village, and locality.</p>
        </div>
        <span class="badge bg-danger text-white">Most important</span>
    </div>
    <p class="small text-muted mb-3">
        Example: India, Uttarakhand, Dehradun, Prem Nagar.
        GPS location and map pin are optional.
    </p>
    <div id="communityLocalVoicesLocationSlot"></div>
</div>

@include('backend.community-posts.partials.local-voices-media-fields', ['post' => $post])
@include('backend.community-posts.partials.local-voices-solution-fields', ['post' => $post])
@include('backend.community-posts.partials.local-voices-conditional-fields', ['post' => $post])
@include('backend.community-posts.partials.local-voices-engagement-fields', ['post' => $post])
@include('backend.community-posts.partials.local-voices-privacy-fields', ['post' => $post])
@endif
