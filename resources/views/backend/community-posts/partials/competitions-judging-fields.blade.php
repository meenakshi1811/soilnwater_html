@php
    $juryMembers = old('competitions_jury', data_get($post->meta, 'competitions_jury', []));
    if (! is_array($juryMembers) || $juryMembers === []) {
        $juryMembers = [['name' => '', 'designation' => '', 'organization' => '', 'profile' => '']];
    }
    $sponsors = old('competitions_sponsors', data_get($post->meta, 'competitions_sponsors', []));
    if (! is_array($sponsors) || $sponsors === []) {
        $sponsors = [['name' => '', 'website' => '', 'contribution' => '']];
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Judging criteria</h5>
            <p class="text-muted mb-0 small">Admin defines evaluation criteria and weightage.</p>
        </div>
    </div>
    <label class="form-label d-block">Criteria</label>
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsJudgingCriteriaOptions() as $criterion)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="competitions_judging_criteria[]" value="{{ $criterion }}" class="form-check-input competitions-flow-field" @checked(in_array($criterion, (array) old('competitions_judging_criteria', data_get($post->meta, 'competitions_judging_criteria', [])), true))>
                <span class="form-check-label">{{ $criterion }}</span>
            </label>
        @endforeach
    </div>
    <label class="form-label" for="competitionsJudgingWeightage">Weightage</label>
    <textarea name="competitions_judging_weightage" id="competitionsJudgingWeightage" class="form-control competitions-flow-field" rows="3" maxlength="2000" placeholder="Innovation: 30%&#10;Presentation: 20%&#10;Impact: 30%&#10;Originality: 20%">{{ old('competitions_judging_weightage', data_get($post->meta, 'competitions_judging_weightage')) }}</textarea>
    <small class="text-muted">Example: Innovation : 30%, Presentation : 20%, Impact : 30%, Originality : 20%</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Jury details</h5>
            <p class="text-muted mb-0 small">Optional — add judges for this competition.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div id="competitionsJuryList" class="d-flex flex-column gap-3">
        @foreach($juryMembers as $index => $member)
            <div class="competitions-jury-row border rounded-3 p-3 bg-white" data-index="{{ $index }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Judge name</label>
                        <input type="text" name="competitions_jury[{{ $index }}][name]" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_jury.'.$index.'.name', data_get($member, 'name')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Designation</label>
                        <input type="text" name="competitions_jury[{{ $index }}][designation]" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_jury.'.$index.'.designation', data_get($member, 'designation')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Organization</label>
                        <input type="text" name="competitions_jury[{{ $index }}][organization]" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_jury.'.$index.'.organization', data_get($member, 'organization')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo</label>
                        @if(is_array(data_get($member, 'photo')) && data_get($member, 'photo.url'))
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ data_get($member, 'photo.url') }}" alt="Judge photo" class="rounded border" style="max-height:48px;">
                                <label class="form-check mb-0 small">
                                    <input type="checkbox" name="competitions_jury_remove_photo[]" value="{{ $index }}" class="form-check-input competitions-flow-field">
                                    <span class="form-check-label">Remove photo</span>
                                </label>
                            </div>
                        @endif
                        <input type="file" name="competitions_jury_photos[{{ $index }}]" class="form-control competitions-flow-field" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Profile</label>
                        <textarea name="competitions_jury[{{ $index }}][profile]" class="form-control competitions-flow-field" rows="2" maxlength="2000">{{ old('competitions_jury.'.$index.'.profile', data_get($member, 'profile')) }}</textarea>
                    </div>
                </div>
                @if($index > 0)
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 competitions-remove-jury-row">Remove judge</button>
                @endif
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="competitionsAddJuryRow">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add judge
    </button>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Prizes</h5>
            <p class="text-muted mb-0 small">Awards and recognition for winners.</p>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label" for="competitionsPrizeFirst">First prize</label>
            <input type="text" name="competitions_prize_first" id="competitionsPrizeFirst" class="form-control competitions-flow-field" maxlength="255" value="{{ old('competitions_prize_first', data_get($post->meta, 'competitions_prize_first')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsPrizeSecond">Second prize</label>
            <input type="text" name="competitions_prize_second" id="competitionsPrizeSecond" class="form-control competitions-flow-field" maxlength="255" value="{{ old('competitions_prize_second', data_get($post->meta, 'competitions_prize_second')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsPrizeThird">Third prize</label>
            <input type="text" name="competitions_prize_third" id="competitionsPrizeThird" class="form-control competitions-flow-field" maxlength="255" value="{{ old('competitions_prize_third', data_get($post->meta, 'competitions_prize_third')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="competitionsPrizeConsolation">Consolation prize</label>
            <input type="text" name="competitions_prize_consolation" id="competitionsPrizeConsolation" class="form-control competitions-flow-field" maxlength="255" value="{{ old('competitions_prize_consolation', data_get($post->meta, 'competitions_prize_consolation')) }}">
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach([
            'competitions_prize_certificates' => 'Certificates',
            'competitions_prize_trophies' => 'Trophies',
            'competitions_prize_cash' => 'Cash',
            'competitions_prize_gift_voucher' => 'Gift voucher',
            'competitions_prize_internship' => 'Internship',
            'competitions_prize_scholarship' => 'Scholarship',
            'competitions_prize_featured_homepage' => 'Featured on homepage',
        ] as $field => $label)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="{{ $field }}" value="1" class="form-check-input competitions-flow-field" @checked(old($field, data_get($post->meta, $field, false)))>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Certificate</h5>
            <p class="text-muted mb-0 small">Certificates offered to participants and winners.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach([
            'competitions_certificate_participation' => 'Participation certificate',
            'competitions_certificate_winner' => 'Winner certificate',
            'competitions_certificate_merit' => 'Merit certificate',
            'competitions_certificate_digital' => 'Digital certificate (automatically generated)',
        ] as $field => $label)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="{{ $field }}" value="1" class="form-check-input competitions-flow-field" @checked(old($field, data_get($post->meta, $field, false)))>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Sponsors</h5>
            <p class="text-muted mb-0 small">Excellent monetization opportunity.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div id="competitionsSponsorList" class="d-flex flex-column gap-3">
        @foreach($sponsors as $index => $sponsor)
            <div class="competitions-sponsor-row border rounded-3 p-3 bg-light" data-index="{{ $index }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sponsor name</label>
                        <input type="text" name="competitions_sponsors[{{ $index }}][name]" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_sponsors.'.$index.'.name', data_get($sponsor, 'name')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <input type="url" name="competitions_sponsors[{{ $index }}][website]" class="form-control competitions-flow-field" maxlength="255" placeholder="https://" value="{{ old('competitions_sponsors.'.$index.'.website', data_get($sponsor, 'website')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Logo</label>
                        @if(is_array(data_get($sponsor, 'logo')) && data_get($sponsor, 'logo.url'))
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ data_get($sponsor, 'logo.url') }}" alt="Sponsor logo" class="rounded border" style="max-height:48px;">
                                <label class="form-check mb-0 small">
                                    <input type="checkbox" name="competitions_sponsor_remove_logo[]" value="{{ $index }}" class="form-check-input competitions-flow-field">
                                    <span class="form-check-label">Remove logo</span>
                                </label>
                            </div>
                        @endif
                        <input type="file" name="competitions_sponsor_logos[{{ $index }}]" class="form-control competitions-flow-field" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contribution</label>
                        <input type="text" name="competitions_sponsors[{{ $index }}][contribution]" class="form-control competitions-flow-field" maxlength="255" value="{{ old('competitions_sponsors.'.$index.'.contribution', data_get($sponsor, 'contribution')) }}">
                    </div>
                </div>
                @if($index > 0)
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 competitions-remove-sponsor-row">Remove sponsor</button>
                @endif
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="competitionsAddSponsorRow">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add sponsor
    </button>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Voting system</h5>
            <p class="text-muted mb-0 small">How entries will be scored.</p>
        </div>
    </div>
    <label class="form-label" for="competitionsVotingSystem">Admin choice</label>
    <select name="competitions_voting_system" id="competitionsVotingSystem" class="form-select competitions-flow-field mb-3">
        <option value="">Select voting system (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsVotingSystems() as $system)
            <option value="{{ $system }}" @selected(old('competitions_voting_system', data_get($post->meta, 'competitions_voting_system')) === $system)>{{ $system }}</option>
        @endforeach
    </select>
    <div id="competitionsPublicVotingFields" style="display:none;">
        <label class="form-label d-block">If public voting</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsPublicVotingMethods() as $method)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_public_voting_methods[]" value="{{ $method }}" class="form-check-input competitions-flow-field" @checked(in_array($method, (array) old('competitions_public_voting_methods', data_get($post->meta, 'competitions_public_voting_methods', [])), true))>
                    <span class="form-check-label">{{ $method }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
