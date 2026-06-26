@php
    $isRecurring = old('community_issue_is_recurring', data_get($post->meta, 'community_issue_is_recurring'));
    $selectedFrequency = old('community_issue_frequency', data_get($post->meta, 'community_issue_frequency'));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Issue timeline</h5>
            <p class="text-muted mb-0 small">When did this issue start and is it recurring?</p>
        </div>
        <span class="badge bg-primary text-white">Timeline</span>
    </div>
    <label class="form-label" for="communityIssueFirstNoticedOn">When was issue first noticed?</label>
    <input
        type="date"
        name="community_issue_first_noticed_on"
        id="communityIssueFirstNoticedOn"
        class="form-control community-issues-flow-field mb-3"
        value="{{ old('community_issue_first_noticed_on', data_get($post->meta, 'community_issue_first_noticed_on')) }}"
    >
    <label class="form-label d-block">Is issue recurring?</label>
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach(['yes' => 'Yes', 'no' => 'No'] as $value => $label)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input
                    type="radio"
                    name="community_issue_is_recurring"
                    value="{{ $value }}"
                    class="form-check-input community-issues-flow-field"
                    id="communityIssueRecurring{{ ucfirst($value) }}"
                    @checked($isRecurring === $value)
                >
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <label class="form-label" for="communityIssueFrequency">Frequency</label>
    <select name="community_issue_frequency" id="communityIssueFrequency" class="form-select community-issues-flow-field">
        <option value="">Select frequency (if recurring)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueRecurringFrequencies() as $frequency)
            <option value="{{ $frequency }}" @selected($selectedFrequency === $frequency)>{{ $frequency }}</option>
        @endforeach
    </select>
</div>
