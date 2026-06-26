@php
    $selectedAuthorities = old('local_voice_authorities', data_get($post->meta, 'local_voice_authorities', []));
    $selectedCallForAction = old('local_voice_call_for_action', data_get($post->meta, 'local_voice_call_for_action', []));
    $selectedStatus = old('local_voice_status_tracker', data_get($post->meta, 'local_voice_status_tracker'));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community solution section</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — share a practical solution for your community.</p>
        </div>
        <span class="badge bg-primary text-white">Recommended</span>
    </div>
    <label class="form-label" for="localVoiceSuggestedSolution">Suggested solution</label>
    <textarea
        name="local_voice_suggested_solution"
        id="localVoiceSuggestedSolution"
        class="form-control local-voices-flow-field"
        rows="3"
        maxlength="3000"
        placeholder="Example: Install rainwater harvesting systems in government schools."
    >{{ old('local_voice_suggested_solution', data_get($post->meta, 'local_voice_suggested_solution')) }}</textarea>
    <label class="form-label mt-3" for="localVoiceEstimatedBenefit">Estimated benefit</label>
    <input
        type="text"
        name="local_voice_estimated_benefit"
        id="localVoiceEstimatedBenefit"
        class="form-control local-voices-flow-field"
        maxlength="500"
        value="{{ old('local_voice_estimated_benefit', data_get($post->meta, 'local_voice_estimated_benefit')) }}"
        placeholder="Optional — expected community benefit"
    >
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Authority concerned</h5>
            <p class="text-muted mb-0 small">Optional — who should address this issue.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceAuthorities() as $authority)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="local_voice_authorities[]"
                        value="{{ $authority }}"
                        class="form-check-input local-voices-flow-field"
                        @checked(in_array($authority, (array) $selectedAuthorities, true))
                    >
                    <span class="form-check-label">{{ $authority }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Call for action</h5>
            <p class="text-muted mb-0 small">Optional actions you want readers to take.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceCallForActionExamples() as $action)
            <div class="col-md-6 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="local_voice_call_for_action[]"
                        value="{{ $action }}"
                        class="form-check-input local-voices-flow-field"
                        @checked(in_array($action, (array) $selectedCallForAction, true))
                    >
                    <span class="form-check-label">{{ $action }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Status tracker</h5>
            <p class="text-muted mb-0 small">Very powerful — show where this issue stands in the civic process.</p>
        </div>
        <span class="badge bg-success text-white">Powerful</span>
    </div>
    <label class="form-label" for="localVoiceStatusTracker">Current status</label>
    <select name="local_voice_status_tracker" id="localVoiceStatusTracker" class="form-select local-voices-flow-field">
        <option value="">Select status (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceStatusTrackerSteps() as $status)
            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">Example flow: Reported → Under Discussion → Forwarded to Authority → Action Taken → Resolved.</small>
</div>
