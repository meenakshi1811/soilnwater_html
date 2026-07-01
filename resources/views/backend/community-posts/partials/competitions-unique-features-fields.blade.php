<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary text-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-2">
        <div>
            <h5 class="mb-1">Unique features for SoilnWater</h5>
            <p class="mb-0 small opacity-75">Enable flagship competition capabilities powered by one shared SoilnWater competition engine.</p>
        </div>
        <span class="badge bg-warning text-dark">SoilnWater unique</span>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-3" for="competitionsEnableMultiSection">
        <input type="checkbox" name="competitions_enable_multi_section" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableMultiSection" @checked(old('competitions_enable_multi_section', data_get($post->meta, 'competitions_enable_multi_section', false)))>
        <span class="form-check-label fw-semibold">1. Multi-Section Competitions</span>
        <span class="d-block small text-muted">Competitions can originate from different community sections while using one shared competition engine.</span>
    </label>
    <div id="competitionsMultiSectionFields" style="display:none;">
        <label class="form-label d-block">Originating sections</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsOriginSections() as $section)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_origin_sections[]" value="{{ $section }}" class="form-check-input competitions-flow-field" @checked(in_array($section, (array) old('competitions_origin_sections', data_get($post->meta, 'competitions_origin_sections', [])), true))>
                    <span class="form-check-label">{{ $section }}</span>
                </label>
            @endforeach
        </div>
        <label class="form-label" for="competitionsPrimaryOriginSection">Primary originating section</label>
        <select name="competitions_primary_origin_section" id="competitionsPrimaryOriginSection" class="form-select competitions-flow-field">
            <option value="">Select primary section (optional)</option>
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsOriginSections() as $section)
                <option value="{{ $section }}" @selected(old('competitions_primary_origin_section', data_get($post->meta, 'competitions_primary_origin_section')) === $section)>{{ $section }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-check border rounded p-3 bg-white mb-0" for="competitionsEnableAutoPortfolio">
        <input type="checkbox" name="competitions_enable_auto_portfolio" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableAutoPortfolio" @checked(old('competitions_enable_auto_portfolio', data_get($post->meta, 'competitions_enable_auto_portfolio', false)))>
        <span class="form-check-label fw-semibold">2. Auto Portfolio Generation</span>
        <span class="d-block small text-muted">Every competition entry can automatically become part of the participant&apos;s SoilnWater portfolio when they opt in.</span>
    </label>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-0" for="competitionsEnableEntryQrCodes">
        <input type="checkbox" name="competitions_enable_entry_qr_codes" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableEntryQrCodes" @checked(old('competitions_enable_entry_qr_codes', data_get($post->meta, 'competitions_enable_entry_qr_codes', false)))>
        <span class="form-check-label fw-semibold">3. QR Code for Every Entry</span>
        <span class="d-block small text-muted">Each submission receives a unique URL, QR code, and share link for schools, friends, and communities.</span>
    </label>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-check border rounded p-3 bg-white mb-3" for="competitionsEnableAchievementBadges">
        <input type="checkbox" name="competitions_enable_achievement_badges" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableAchievementBadges" @checked(old('competitions_enable_achievement_badges', data_get($post->meta, 'competitions_enable_achievement_badges', false)))>
        <span class="form-check-label fw-semibold">4. Achievement &amp; Badge System</span>
        <span class="d-block small text-muted">Automatically award badges that remain permanently on the user&apos;s profile.</span>
    </label>
    <div id="competitionsAchievementBadgesFields" style="display:none;">
        <label class="form-label d-block">Badges to award</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsAwardBadges() as $badge)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_award_badges[]" value="{{ $badge }}" class="form-check-input competitions-flow-field" @checked(in_array($badge, (array) old('competitions_award_badges', data_get($post->meta, 'competitions_award_badges', [])), true))>
                    <span class="form-check-label">{{ $badge }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-3" for="competitionsEnableLeaderboards">
        <input type="checkbox" name="competitions_enable_leaderboards" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableLeaderboards" @checked(old('competitions_enable_leaderboards', data_get($post->meta, 'competitions_enable_leaderboards', false)))>
        <span class="form-check-label fw-semibold">5. Leaderboard</span>
        <span class="d-block small text-muted">Separate leaderboards drive healthy competition and repeat visits.</span>
    </label>
    <div id="competitionsLeaderboardsFields" style="display:none;">
        <label class="form-label d-block">Leaderboard types</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsLeaderboardTypes() as $type)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_leaderboard_types[]" value="{{ $type }}" class="form-check-input competitions-flow-field" @checked(in_array($type, (array) old('competitions_leaderboard_types', data_get($post->meta, 'competitions_leaderboard_types', [])), true))>
                    <span class="form-check-label">{{ $type }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-check border rounded p-3 bg-white mb-3" for="competitionsEnableInstitutionDashboard">
        <input type="checkbox" name="competitions_enable_institution_dashboard" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableInstitutionDashboard" @checked(old('competitions_enable_institution_dashboard', data_get($post->meta, 'competitions_enable_institution_dashboard', false)))>
        <span class="form-check-label fw-semibold">6. School &amp; College Dashboard</span>
        <span class="d-block small text-muted">Educational institutions can register teams, track performance, download certificates, and view rankings.</span>
    </label>
    <div id="competitionsInstitutionDashboardFields" style="display:none;">
        <label class="form-label" for="competitionsInstitutionDashboardNotes">Institution dashboard notes</label>
        <textarea name="competitions_institution_dashboard_notes" id="competitionsInstitutionDashboardNotes" class="form-control competitions-flow-field" rows="2" maxlength="2000" placeholder="Instructions for schools/colleges registering teams">{{ old('competitions_institution_dashboard_notes', data_get($post->meta, 'competitions_institution_dashboard_notes')) }}</textarea>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-3" for="competitionsEnableSponsoredBranding">
        <input type="checkbox" name="competitions_enable_sponsored_branding" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableSponsoredBranding" @checked(old('competitions_enable_sponsored_branding', data_get($post->meta, 'competitions_enable_sponsored_branding', false)))>
        <span class="form-check-label fw-semibold">7. Sponsored Competitions</span>
        <span class="d-block small text-muted">Excellent revenue model — sponsor branding appears throughout the competition.</span>
    </label>
    <div id="competitionsSponsoredBrandingFields" style="display:none;">
        <p class="small text-muted mb-2">Examples:</p>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsSponsorExamples() as $example)
                <span class="badge bg-white text-dark border py-2 px-3">{{ $example }}</span>
            @endforeach
        </div>
        <label class="form-label" for="competitionsSponsoredBrandingNotes">Sponsor branding notes</label>
        <textarea name="competitions_sponsored_branding_notes" id="competitionsSponsoredBrandingNotes" class="form-control competitions-flow-field" rows="2" maxlength="2000" placeholder="How sponsor branding will appear across the competition">{{ old('competitions_sponsored_branding_notes', data_get($post->meta, 'competitions_sponsored_branding_notes')) }}</textarea>
        <small class="text-muted">Add sponsor details in the Sponsors section above.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-check border rounded p-3 bg-white mb-3" for="competitionsEnableEcommerce">
        <input type="checkbox" name="competitions_enable_ecommerce" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableEcommerce" @checked(old('competitions_enable_ecommerce', data_get($post->meta, 'competitions_enable_ecommerce', false)))>
        <span class="form-check-label fw-semibold">8. E-commerce Integration</span>
        <span class="d-block small text-muted">Winning creative works can create income opportunities for participants.</span>
    </label>
    <div id="competitionsEcommerceFields" style="display:none;">
        <label class="form-label d-block">Commerce options</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsEcommerceOptions() as $option)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_ecommerce_options[]" value="{{ $option }}" class="form-check-input competitions-flow-field" @checked(in_array($option, (array) old('competitions_ecommerce_options', data_get($post->meta, 'competitions_ecommerce_options', [])), true))>
                    <span class="form-check-label">{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-3" for="competitionsEnableVotingFraudProtection">
        <input type="checkbox" name="competitions_enable_voting_fraud_protection" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableVotingFraudProtection" @checked(old('competitions_enable_voting_fraud_protection', data_get($post->meta, 'competitions_enable_voting_fraud_protection', false)))>
        <span class="form-check-label fw-semibold">9. Community Voting with Fraud Protection</span>
        <span class="d-block small text-muted">Enable when public voting is used. Configure safeguards in addition to the voting system above.</span>
    </label>
    <div id="competitionsVotingFraudProtectionFields" style="display:none;">
        <label class="form-label d-block">Fraud protection measures</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsVotingFraudProtections() as $measure)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_voting_fraud_protections[]" value="{{ $measure }}" class="form-check-input competitions-flow-field" @checked(in_array($measure, (array) old('competitions_voting_fraud_protections', data_get($post->meta, 'competitions_voting_fraud_protections', [])), true))>
                    <span class="form-check-label">{{ $measure }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3">
    <label class="form-check border rounded p-3 bg-white mb-3" for="competitionsEnableDigitalCertificates">
        <input type="checkbox" name="competitions_enable_digital_certificates" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableDigitalCertificates" @checked(old('competitions_enable_digital_certificates', data_get($post->meta, 'competitions_enable_digital_certificates', false)))>
        <span class="form-check-label fw-semibold">10. Certificates &amp; Digital Awards</span>
        <span class="d-block small text-muted">Automatically generate verifiable certificates and digital awards for participants.</span>
    </label>
    <div id="competitionsDigitalCertificatesFields" style="display:none;">
        <label class="form-label d-block">Certificate &amp; award types</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::competitionsDigitalCertificateTypes() as $type)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="competitions_digital_certificate_types[]" value="{{ $type }}" class="form-check-input competitions-flow-field" @checked(in_array($type, (array) old('competitions_digital_certificate_types', data_get($post->meta, 'competitions_digital_certificate_types', [])), true))>
                    <span class="form-check-label">{{ $type }}</span>
                </label>
            @endforeach
        </div>
        <label class="form-check border rounded p-3 bg-white mb-0" for="competitionsEnableVerifiableCertificateIds">
            <input type="checkbox" name="competitions_enable_verifiable_certificate_ids" value="1" class="form-check-input competitions-flow-field" id="competitionsEnableVerifiableCertificateIds" @checked(old('competitions_enable_verifiable_certificate_ids', data_get($post->meta, 'competitions_enable_verifiable_certificate_ids', false)))>
            <span class="form-check-label">Issue verifiable certificate IDs with QR verification</span>
        </label>
    </div>
</div>
