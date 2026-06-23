@php
    $selectedSeniorCitizensForumCategory = old('senior_citizens_forum_category', data_get($post->meta, 'senior_citizens_forum_category', $post->category));
    $selectedLifeJourneyCategories = old('senior_citizens_forum_life_journey_categories', data_get($post->meta, 'senior_citizens_forum_life_journey_categories', []));
    $selectedThemes = old('senior_citizens_forum_themes', data_get($post->meta, 'senior_citizens_forum_themes', []));
    $selectedCommunityContributions = old('senior_citizens_forum_community_contributions', data_get($post->meta, 'senior_citizens_forum_community_contributions', []));
    $initialKeyLessons = old('senior_citizens_forum_key_lessons', data_get($post->meta, 'senior_citizens_forum_key_lessons', []));
    $flowPlacement = $placement ?? 'all';
    $showSeniorCitizensForumSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showSeniorCitizensForumRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showSeniorCitizensForumSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Main category</h5>
            <p class="text-muted mb-0 small">Choose the primary topic for this Senior Citizens Forum post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="seniorCitizensForumCategory">Main category <span class="text-danger">*</span></label>
    <select name="senior_citizens_forum_category" id="seniorCitizensForumCategory" class="form-select senior-citizens-forum-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedSeniorCitizensForumCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">
        Examples: Life Experiences, Advice to Youth, Retirement Life, Health &amp; Wellness, Family Values, Memoirs, Village Memories, Career Experiences, Social Issues, Spirituality, Culture &amp; Heritage, Community Service, Agriculture Experiences, Water &amp; Environment, Inspirational Stories
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
    <label class="form-label" for="seniorCitizensForumContentType">Content type <span class="text-danger">*</span></label>
    <select name="senior_citizens_forum_content_type" id="seniorCitizensForumContentType" class="form-select senior-citizens-forum-required" required>
        <option value="">Select content type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumContentTypeGroups() as $groupLabel => $groupOptions)
            <optgroup label="{{ $groupLabel }}">
                @foreach($groupOptions as $contentType)
                    <option value="{{ $contentType }}" @selected(old('senior_citizens_forum_content_type', data_get($post->meta, 'senior_citizens_forum_content_type')) === $contentType)>{{ $contentType }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>
@endif

@if($showSeniorCitizensForumRest)
<div class="news-flow-card story-flow-card story-flow-card--lessons border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Life lessons</h5>
            <p class="text-muted mb-0 small">Key lessons from your life — displayed separately as highlights on the public page.</p>
        </div>
        <span class="badge bg-danger text-white">Very valuable</span>
    </div>
    <label class="form-label">Key lessons</label>
    <div class="autobiography-list-example small text-muted mb-3">
        <strong>Examples:</strong>
        <ul class="mb-0 mt-2">
            @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumKeyLessonExamples() as $example)
                <li>{{ $example }}</li>
            @endforeach
        </ul>
    </div>
    <div id="seniorCitizensForumKeyLessonsEntries" class="d-flex flex-column gap-2 mb-2"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addSeniorCitizensForumKeyLessonBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add lesson
    </button>
    <template id="seniorCitizensForumKeyLessonTemplate">
        <div class="autobiography-list-entry d-flex gap-2 align-items-center">
            <input type="text" class="form-control js-senior-citizens-forum-key-lesson-input" maxlength="300" placeholder="Respect your parents." data-name="senior_citizens_forum_key_lessons[__INDEX__]">
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-list-entry">Remove</button>
        </div>
    </template>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Age group</h5>
            <p class="text-muted mb-0 small">Optional — helps readers relate to your life stage.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="seniorCitizensForumAgeGroup">Age group</label>
    <select name="senior_citizens_forum_age_group" id="seniorCitizensForumAgeGroup" class="form-select">
        <option value="">Select age group (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumAgeGroups() as $ageGroup)
            <option value="{{ $ageGroup }}" @selected(old('senior_citizens_forum_age_group', data_get($post->meta, 'senior_citizens_forum_age_group')) === $ageGroup)>{{ $ageGroup }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Life journey category</h5>
            <p class="text-muted mb-0 small">Select all career or life paths that describe your journey.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumLifeJourneyCategories() as $journeyCategory)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="senior_citizens_forum_life_journey_categories[]"
                        value="{{ $journeyCategory }}"
                        class="form-check-input"
                        @checked(in_array($journeyCategory, (array) $selectedLifeJourneyCategories, true))
                    >
                    <span class="form-check-label">{{ $journeyCategory }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Themes</h5>
            <p class="text-muted mb-0 small">Select all themes that relate to your story or message.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumThemes() as $theme)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="senior_citizens_forum_themes[]"
                        value="{{ $theme }}"
                        class="form-check-input"
                        @checked(in_array($theme, (array) $selectedThemes, true))
                    >
                    <span class="form-check-label">{{ $theme }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--lessons border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Advice to youth</h5>
            <p class="text-muted mb-0 small">A dedicated message for the younger generation — this can become one of the most read parts of your post.</p>
        </div>
        <span class="badge bg-danger text-white">Special section</span>
    </div>
    <label class="form-label" for="seniorCitizensForumAdviceToYouth">Your message to youth</label>
    <textarea
        name="senior_citizens_forum_advice_to_youth"
        id="seniorCitizensForumAdviceToYouth"
        class="form-control"
        rows="5"
        maxlength="5000"
        placeholder="What message would you like to give the younger generation?"
    >{{ old('senior_citizens_forum_advice_to_youth', data_get($post->meta, 'senior_citizens_forum_advice_to_youth')) }}</textarea>
    <small class="text-muted d-block mt-2">Example: What message would you like to give the younger generation?</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community contribution</h5>
            <p class="text-muted mb-0 small">Optional — how you have contributed to your community over the years.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumCommunityContributions() as $contribution)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="senior_citizens_forum_community_contributions[]"
                        value="{{ $contribution }}"
                        class="form-check-input"
                        @checked(in_array($contribution, (array) $selectedCommunityContributions, true))
                    >
                    <span class="form-check-label">{{ $contribution }}</span>
                </label>
            </div>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2">Examples: Teacher, Volunteer, Social Worker, Community Leader, Farmer, Environmental Activist</small>
</div>

@include('backend.community-posts.partials.senior-citizens-forum-achievements-fields', ['post' => $post])

@include('backend.community-posts.partials.senior-citizens-forum-audio-fields', ['post' => $post])

<div class="news-flow-card story-flow-card story-flow-card--video border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video memories</h5>
            <p class="text-muted mb-0 small">Optional video. You can also embed videos inside the rich text editor.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="seniorCitizensForumVideoType">Video type</label>
    <select name="senior_citizens_forum_video_type" id="seniorCitizensForumVideoType" class="form-select mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumVideoTypes() as $videoType)
            <option value="{{ $videoType }}" @selected(old('senior_citizens_forum_video_type', data_get($post->meta, 'senior_citizens_forum_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mb-1">Examples:</small>
    <ul class="small text-muted mb-3 ps-3">
        <li>Personal Interviews, Life Story Recordings</li>
        <li>Family Messages, Community History</li>
    </ul>
    <div id="communitySeniorCitizensForumVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Family heritage</h5>
            <p class="text-muted mb-0 small">Optional — helps preserve family and cultural heritage for future generations.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumFamilyHeritageFields() as $fieldName => $fieldLabel)
        <div class="mb-3">
            <label class="form-label" for="{{ Str::camel($fieldName) }}">{{ $fieldLabel }}</label>
            <textarea
                name="{{ $fieldName }}"
                id="{{ Str::camel($fieldName) }}"
                class="form-control"
                rows="3"
                maxlength="3000"
                placeholder="Share {{ strtolower($fieldLabel) }} (optional)"
            >{{ old($fieldName, data_get($post->meta, $fieldName)) }}</textarea>
        </div>
    @endforeach
</div>

<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Important for local heritage and community stories.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="alert alert-warning py-2 px-3 small mb-3">
        For safety reasons, exact addresses should never be displayed.
    </div>
    <div id="communitySeniorCitizensForumLocationSlot"></div>
</div>

@include('backend.community-posts.partials.senior-citizens-forum-engagement-fields', ['post' => $post])
@include('backend.community-posts.partials.senior-citizens-forum-privacy-fields', ['post' => $post])

<script>
    window.communitySeniorCitizensForumKeyLessons = @json(array_values($initialKeyLessons));
</script>
@endif
