@php
    $studentCornerPollOptions = old(
        'student_corner_poll_options',
        data_get($post->meta, 'student_corner_poll_options', \App\Support\CommunityContentTaxonomy::studentCornerDefaultPollOptions())
    );
    if (is_string($studentCornerPollOptions)) {
        $studentCornerPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $studentCornerPollOptions))));
    }
    $selectedMentorshipRequests = old('student_corner_mentorship_requests', data_get($post->meta, 'student_corner_mentorship_requests', []));
    $selectedCompetitionCategories = old('student_corner_competition_categories', data_get($post->meta, 'student_corner_competition_categories', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="studentCornerPollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Highly engaging — ask fellow students a question with multiple choice answers.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly engaging</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-white mb-0" for="studentCornerAllowPoll">
            <input type="checkbox" name="allow_poll" value="1" class="form-check-input student-corner-flow-field" id="studentCornerAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
            <span class="form-check-label">Enable poll on this Student Corner post</span>
            <small class="text-muted d-block mt-1">Example: Which competitive exam are you preparing for?</small>
        </label>
    </div>
    <div id="studentCornerPollFields">
        <label class="form-label" for="studentCornerPollQuestion">Poll question</label>
        <input
            type="text"
            name="student_corner_poll_question"
            id="studentCornerPollQuestion"
            class="form-control student-corner-flow-field mb-3"
            maxlength="255"
            value="{{ old('student_corner_poll_question', data_get($post->meta, 'student_corner_poll_question')) }}"
            placeholder="Which competitive exam are you preparing for?"
        >
        <label class="form-label" for="studentCornerPollOptions">Poll options</label>
        <textarea
            name="student_corner_poll_options"
            id="studentCornerPollOptions"
            class="form-control student-corner-flow-field"
            rows="4"
            placeholder="One option per line"
        >{{ old('student_corner_poll_options', is_array($studentCornerPollOptions) ? implode("\n", $studentCornerPollOptions) : $studentCornerPollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example options: {{ implode(', ', \App\Support\CommunityContentTaxonomy::studentCornerDefaultPollOptions()) }}.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Invite fellow students to share experiences and advice in comments.</p>
        </div>
        <span class="badge bg-primary text-white">Very powerful</span>
    </div>
    <label class="form-label" for="studentCornerAskCommunity">Community question</label>
    <textarea
        name="student_corner_ask_community"
        id="studentCornerAskCommunity"
        class="form-control student-corner-flow-field"
        rows="3"
        maxlength="500"
        placeholder="What study strategy worked best for your board exams?"
    >{{ old('student_corner_ask_community', data_get($post->meta, 'student_corner_ask_community')) }}</textarea>
    <small class="text-muted d-block mt-2">Encourages discussion when comments are enabled.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Mentorship request</h5>
            <p class="text-muted mb-0 small">Let the community know what kind of guidance you are seeking.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerMentorshipRequests() as $mentorshipRequest)
            <div class="col-md-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_mentorship_requests[]"
                        value="{{ $mentorshipRequest }}"
                        class="form-check-input student-corner-flow-field"
                        @checked(in_array($mentorshipRequest, (array) $selectedMentorshipRequests, true))
                    >
                    <span class="form-check-label">{{ $mentorshipRequest }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="studentCornerCompetitionWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Competition entry</h5>
            <p class="text-muted mb-0 small">Submit this post to a SoilnWater student competition.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="studentCornerSubmitToCompetition">
        <input
            type="checkbox"
            name="student_corner_submit_to_competition"
            value="1"
            class="form-check-input student-corner-flow-field"
            id="studentCornerSubmitToCompetition"
            @checked(old('student_corner_submit_to_competition', data_get($post->meta, 'student_corner_submit_to_competition', false)))
        >
        <span class="form-check-label">Submit this post to a competition</span>
    </label>
    <p class="small text-muted mb-2">Competition categories:</p>
    <div class="row g-2 community-flow-checklist" id="studentCornerCompetitionCategories">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerCompetitionCategories() as $competitionCategory)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_competition_categories[]"
                        value="{{ $competitionCategory }}"
                        class="form-check-input student-corner-flow-field"
                        @checked(in_array($competitionCategory, (array) $selectedCompetitionCategories, true))
                    >
                    <span class="form-check-label">{{ $competitionCategory }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="communityStudentCornerTagsSlotWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Add up to 10 tags to help readers discover this content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Maximum 10 tags</span>
    </div>
    <div id="communityStudentCornerTagsSlot"></div>
    <small class="text-muted d-block mt-2">
        Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::studentCornerTagExamples()) }}.
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="studentCornerParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose how readers can engage with this post.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <p class="small text-muted mb-2">Allow:</p>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-light mb-0" for="studentCornerAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input student-corner-flow-field" id="studentCornerAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="studentCornerAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input student-corner-flow-field" id="studentCornerAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="studentCornerAllowFeedback">
            <input type="checkbox" name="allow_feedback" value="1" class="form-check-input student-corner-flow-field" id="studentCornerAllowFeedback" @checked(old('allow_feedback', $post->allow_feedback ?? false))>
            <span class="form-check-label">Peer Discussion</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="studentCornerAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input student-corner-flow-field" id="studentCornerAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Optional — useful for local scholarships, campus events, and regional opportunities.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <small class="text-muted d-block mb-3">
        Add state, district, and city to help readers find local scholarships, college fairs, and regional student opportunities.
    </small>
    <div id="communityStudentCornerLocationSlot"></div>
</div>
