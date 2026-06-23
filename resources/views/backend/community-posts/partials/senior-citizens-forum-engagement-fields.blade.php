<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Encourage engagement — invite readers to share their experiences in comments.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <label class="form-label" for="seniorCitizensForumAskCommunity">Community question</label>
    <textarea
        name="senior_citizens_forum_ask_community"
        id="seniorCitizensForumAskCommunity"
        class="form-control"
        rows="3"
        maxlength="500"
        placeholder="How do you stay active after retirement?"
    >{{ old('senior_citizens_forum_ask_community', data_get($post->meta, 'senior_citizens_forum_ask_community')) }}</textarea>
    <small class="text-muted d-block mt-2">
        Examples: How do you stay active after retirement? What life lesson changed your life?
    </small>
</div>
