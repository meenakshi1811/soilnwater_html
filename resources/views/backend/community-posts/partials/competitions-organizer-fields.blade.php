@php
    $organizerLogo = data_get($post->meta, 'competitions_organizer_logo');
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Organizer details</h5>
            <p class="text-muted mb-0 small">Who is organizing this competition?</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="competitionsOrganizerName">Organizer name</label>
            <input type="text" name="competitions_organizer_name" id="competitionsOrganizerName" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_organizer_name', data_get($post->meta, 'competitions_organizer_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsOrganizerOrganization">Organization</label>
            <input type="text" name="competitions_organizer_organization" id="competitionsOrganizerOrganization" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_organizer_organization', data_get($post->meta, 'competitions_organizer_organization')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsOrganizerContact">Contact person</label>
            <input type="text" name="competitions_organizer_contact_person" id="competitionsOrganizerContact" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_organizer_contact_person', data_get($post->meta, 'competitions_organizer_contact_person')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsOrganizerEmail">Email</label>
            <input type="email" name="competitions_organizer_email" id="competitionsOrganizerEmail" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_organizer_email', data_get($post->meta, 'competitions_organizer_email')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsOrganizerPhone">Phone</label>
            <input type="text" name="competitions_organizer_phone" id="competitionsOrganizerPhone" class="form-control competitions-flow-field" maxlength="40" value="{{ old('competitions_organizer_phone', data_get($post->meta, 'competitions_organizer_phone')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsOrganizerWebsite">Website</label>
            <input type="url" name="competitions_organizer_website" id="competitionsOrganizerWebsite" class="form-control competitions-flow-field" maxlength="255" placeholder="https://" value="{{ old('competitions_organizer_website', data_get($post->meta, 'competitions_organizer_website')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="competitionsOrganizerLogo">Organizer logo</label>
            @if(is_array($organizerLogo) && data_get($organizerLogo, 'url'))
                <div class="d-flex align-items-center gap-3 mb-2">
                    <img src="{{ data_get($organizerLogo, 'url') }}" alt="Organizer logo" class="rounded border" style="max-height:64px;">
                    <label class="form-check mb-0">
                        <input type="checkbox" name="removed_competitions_organizer_logo" value="1" class="form-check-input competitions-flow-field">
                        <span class="form-check-label">Remove current logo</span>
                    </label>
                </div>
            @endif
            <input type="file" name="competitions_organizer_logo" id="competitionsOrganizerLogo" class="form-control competitions-flow-field" accept="image/*">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Competition theme</h5>
            <p class="text-muted mb-0 small">Select one or more themes that apply.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsThemes() as $theme)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="competitions_themes[]" value="{{ $theme }}" class="form-check-input competitions-flow-field" @checked(in_array($theme, (array) old('competitions_themes', data_get($post->meta, 'competitions_themes', [])), true))>
                <span class="form-check-label">{{ $theme }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Important dates</h5>
            <p class="text-muted mb-0 small">Key milestones for this competition.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateAnnouncement">Announcement date</label>
            <input type="date" name="competitions_date_announcement" id="competitionsDateAnnouncement" class="form-control competitions-flow-field" value="{{ old('competitions_date_announcement', data_get($post->meta, 'competitions_date_announcement')) }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateRegOpens">Registration opens</label>
            <input type="date" name="competitions_date_registration_opens" id="competitionsDateRegOpens" class="form-control competitions-flow-field" value="{{ old('competitions_date_registration_opens', data_get($post->meta, 'competitions_date_registration_opens')) }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateRegCloses">Registration closes</label>
            <input type="date" name="competitions_date_registration_closes" id="competitionsDateRegCloses" class="form-control competitions-flow-field" value="{{ old('competitions_date_registration_closes', data_get($post->meta, 'competitions_date_registration_closes')) }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateSubmission">Submission deadline</label>
            <input type="date" name="competitions_date_submission_deadline" id="competitionsDateSubmission" class="form-control competitions-flow-field" value="{{ old('competitions_date_submission_deadline', data_get($post->meta, 'competitions_date_submission_deadline')) }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateEvaluation">Evaluation period</label>
            <input type="text" name="competitions_date_evaluation_period" id="competitionsDateEvaluation" class="form-control competitions-flow-field" maxlength="120" placeholder="e.g. 1–15 April 2026" value="{{ old('competitions_date_evaluation_period', data_get($post->meta, 'competitions_date_evaluation_period')) }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateResult">Result date</label>
            <input type="date" name="competitions_date_result" id="competitionsDateResult" class="form-control competitions-flow-field" value="{{ old('competitions_date_result', data_get($post->meta, 'competitions_date_result')) }}">
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="competitionsDateAward">Award ceremony</label>
            <input type="date" name="competitions_date_award_ceremony" id="competitionsDateAward" class="form-control competitions-flow-field" value="{{ old('competitions_date_award_ceremony', data_get($post->meta, 'competitions_date_award_ceremony')) }}">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Registration details</h5>
            <p class="text-muted mb-0 small">How participants register for this competition.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-check border rounded p-3 bg-light mb-0" for="competitionsRegistrationRequired">
                <input type="checkbox" name="competitions_registration_required" value="1" class="form-check-input competitions-flow-field" id="competitionsRegistrationRequired" @checked(old('competitions_registration_required', data_get($post->meta, 'competitions_registration_required', false)))>
                <span class="form-check-label">Registration required</span>
            </label>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="competitionsRegistrationFee">Registration fee</label>
            <input type="text" name="competitions_registration_fee" id="competitionsRegistrationFee" class="form-control competitions-flow-field" maxlength="60" placeholder="Free or amount" value="{{ old('competitions_registration_fee', data_get($post->meta, 'competitions_registration_fee')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="competitionsMaxParticipants">Maximum participants</label>
            <input type="number" name="competitions_max_participants" id="competitionsMaxParticipants" class="form-control competitions-flow-field" min="1" value="{{ old('competitions_max_participants', data_get($post->meta, 'competitions_max_participants')) }}">
        </div>
        <div class="col-md-4 d-flex flex-column gap-2 justify-content-end">
            <label class="form-check border rounded p-2 bg-light mb-0" for="competitionsTeamAllowed">
                <input type="checkbox" name="competitions_team_allowed" value="1" class="form-check-input competitions-flow-field" id="competitionsTeamAllowed" @checked(old('competitions_team_allowed', data_get($post->meta, 'competitions_team_allowed', false)))>
                <span class="form-check-label">Team allowed</span>
            </label>
            <label class="form-check border rounded p-2 bg-light mb-0" for="competitionsIndividualOnly">
                <input type="checkbox" name="competitions_individual_only" value="1" class="form-check-input competitions-flow-field" id="competitionsIndividualOnly" @checked(old('competitions_individual_only', data_get($post->meta, 'competitions_individual_only', false)))>
                <span class="form-check-label">Individual only</span>
            </label>
        </div>
    </div>
</div>

<div id="competitionsTeamFields" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Team details</h5>
            <p class="text-muted mb-0 small">If this is a team competition.</p>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label" for="competitionsTeamMin">Minimum members</label>
            <input type="number" name="competitions_team_min_members" id="competitionsTeamMin" class="form-control competitions-flow-field" min="1" value="{{ old('competitions_team_min_members', data_get($post->meta, 'competitions_team_min_members')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsTeamMax">Maximum members</label>
            <input type="number" name="competitions_team_max_members" id="competitionsTeamMax" class="form-control competitions-flow-field" min="1" value="{{ old('competitions_team_max_members', data_get($post->meta, 'competitions_team_max_members')) }}">
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsTeamDetailOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="competitions_team_details[]" value="{{ $option }}" class="form-check-input competitions-flow-field" @checked(in_array($option, (array) old('competitions_team_details', data_get($post->meta, 'competitions_team_details', [])), true))>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
</div>
