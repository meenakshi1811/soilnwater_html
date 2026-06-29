@php
    $astroPollOptions = old(
        'astro_consultancy_poll_options',
        data_get($post->meta, 'astro_consultancy_poll_options', \App\Support\CommunityContentTaxonomy::astroConsultancyDefaultPollOptions())
    );
    if (is_string($astroPollOptions)) {
        $astroPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $astroPollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Verified consultant directory</h5>
            <p class="text-muted mb-0 small">Connect community engagement with verified SoilnWater consultants — profiles, appointments, packages, and reviews.</p>
        </div>
        <span class="badge bg-primary text-white">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="astroEnableConsultantLinking">
        <input type="checkbox" name="astro_consultancy_enable_consultant_linking" value="1" class="form-check-input astro-consultancy-flow-field" id="astroEnableConsultantLinking" @checked(old('astro_consultancy_enable_consultant_linking', data_get($post->meta, 'astro_consultancy_enable_consultant_linking', false)))>
        <span class="form-check-label">Allow linking to a verified SoilnWater consultant profile</span>
    </label>
    <label class="form-label" for="astroConsultantProfileUrl">Consultant profile URL</label>
    <input type="url" name="astro_consultancy_consultant_profile_url" id="astroConsultantProfileUrl" class="form-control astro-consultancy-flow-field mb-3" maxlength="255" value="{{ old('astro_consultancy_consultant_profile_url', data_get($post->meta, 'astro_consultancy_consultant_profile_url')) }}" placeholder="https://soilnwater.com/consultant/...">
    <label class="form-label d-block">Related service actions</label>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyRelatedServiceActions() as $action)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="astro_consultancy_related_service_actions[]" value="{{ $action }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($action, (array) old('astro_consultancy_related_service_actions', data_get($post->meta, 'astro_consultancy_related_service_actions', [])), true))>
                <span class="form-check-label">{{ $action }}</span>
            </label>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2">Helps convert community engagement into consultancy leads.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Astrology knowledge library</h5>
            <p class="text-muted mb-0 small">Organize educational content for learners — not purely predictive posts.</p>
        </div>
        <span class="badge bg-info text-dark">Educational</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyKnowledgeLibraryTopics() as $topic)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="astro_consultancy_knowledge_library_topics[]" value="{{ $topic }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($topic, (array) old('astro_consultancy_knowledge_library_topics', data_get($post->meta, 'astro_consultancy_knowledge_library_topics', [])), true))>
                <span class="form-check-label">{{ $topic }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Live Q&amp;A sessions</h5>
            <p class="text-muted mb-0 small">Verified consultants can host scheduled sessions. Users can request private consultation instead of posting personal details publicly.</p>
        </div>
        <span class="badge bg-success text-white">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="astroEnableLiveQa">
        <input type="checkbox" name="astro_consultancy_enable_live_qa" value="1" class="form-check-input astro-consultancy-flow-field" id="astroEnableLiveQa" @checked(old('astro_consultancy_enable_live_qa', data_get($post->meta, 'astro_consultancy_enable_live_qa', false)))>
        <span class="form-check-label">Mark this post for live Q&amp;A or archived session discovery</span>
    </label>
    <label class="form-label d-block">Private query routing</label>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyPrivateQueryOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="astro_consultancy_private_query_options[]" value="{{ $option }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($option, (array) old('astro_consultancy_private_query_options', data_get($post->meta, 'astro_consultancy_private_query_options', [])), true))>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-label" for="astroAskCommunity">Ask the community</label>
    <textarea name="astro_consultancy_ask_community" id="astroAskCommunity" class="form-control astro-consultancy-flow-field" rows="2" maxlength="500" placeholder="e.g. How do different traditions interpret Saturn's influence?">{{ old('astro_consultancy_ask_community', data_get($post->meta, 'astro_consultancy_ask_community')) }}</textarea>
    <small class="text-muted">Examples: Saturn interpretations, Vastu planning experiences, choosing auspicious dates.</small>
</div>

<div id="astroPollWrap" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Optional — e.g. &ldquo;Do you regularly read your horoscope?&rdquo;</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="astroAllowPoll">
        <input type="checkbox" name="astro_consultancy_allow_poll" value="1" class="form-check-input astro-consultancy-flow-field" id="astroAllowPoll" @checked(old('astro_consultancy_allow_poll', data_get($post->meta, 'astro_consultancy_allow_poll', false)))>
        <span class="form-check-label">Include a poll on this post</span>
    </label>
    <div id="astroPollFields" style="display:none;">
        <label class="form-label" for="astroPollQuestion">Poll question</label>
        <input type="text" name="astro_consultancy_poll_question" id="astroPollQuestion" class="form-control astro-consultancy-flow-field mb-3" maxlength="255" value="{{ old('astro_consultancy_poll_question', data_get($post->meta, 'astro_consultancy_poll_question')) }}" placeholder="e.g. Do you regularly read your horoscope?">
        <label class="form-label" for="astroPollOptions">Poll options (one per line)</label>
        <textarea name="astro_consultancy_poll_options" id="astroPollOptions" class="form-control astro-consultancy-flow-field" rows="3" maxlength="2000">{{ is_array($astroPollOptions) ? implode("\n", $astroPollOptions) : $astroPollOptions }}</textarea>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose what types of comments are welcome.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyCommentSettings() as $setting)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="astro_consultancy_comment_settings[]" value="{{ $setting }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($setting, (array) old('astro_consultancy_comment_settings', data_get($post->meta, 'astro_consultancy_comment_settings', [])), true))>
                <span class="form-check-label">{{ $setting }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Readers can react without implying guaranteed accuracy.</p>
        </div>
        <span class="badge bg-primary text-white">Reactions</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border py-2 px-3"><i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}</span>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2">These reactions are enabled automatically for Astro Consultancy posts.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Declaration</h5>
            <p class="text-muted mb-0 small">Mandatory — confirm responsible sharing of traditional and educational content.</p>
        </div>
        <span class="badge bg-danger text-white">Mandatory</span>
    </div>
    <div class="d-flex flex-column gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyDeclarationStatements() as $field => $label)
            <label class="form-check border rounded p-3 bg-white mb-0">
                <input type="checkbox" name="{{ $field }}" id="{{ $field }}" class="form-check-input astro-consultancy-declaration-required" value="1" @checked(old($field, data_get($post->meta, $field, false))) required>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Disclaimer</h5>
            <p class="text-muted mb-0 small">This disclaimer is displayed on every Astro Consultancy post.</p>
        </div>
        <span class="badge bg-secondary text-white">Always shown</span>
    </div>
    <div class="alert alert-light border mb-0 small">
        {{ \App\Support\CommunityContentTaxonomy::astroConsultancyDisclaimerText() }}
    </div>
</div>
