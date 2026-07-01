<div id="rsScriptureSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Scripture reference</h5>
            <p class="text-muted mb-0 small">Optional — e.g. Bhagavad Gita, Chapter 2, Verse 47.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="rsScriptureName">Scripture name</label>
            <input type="text" name="religion_spirituality_scripture_name" id="rsScriptureName" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_scripture_name', data_get($post->meta, 'religion_spirituality_scripture_name')) }}" placeholder="e.g. Bhagavad Gita">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="rsScriptureChapter">Chapter</label>
            <input type="text" name="religion_spirituality_scripture_chapter" id="rsScriptureChapter" class="form-control religion-spirituality-flow-field" maxlength="40" value="{{ old('religion_spirituality_scripture_chapter', data_get($post->meta, 'religion_spirituality_scripture_chapter')) }}" placeholder="Chapter 2">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="rsScriptureVerse">Verse</label>
            <input type="text" name="religion_spirituality_scripture_verse" id="rsScriptureVerse" class="form-control religion-spirituality-flow-field" maxlength="40" value="{{ old('religion_spirituality_scripture_verse', data_get($post->meta, 'religion_spirituality_scripture_verse')) }}" placeholder="Verse 47">
        </div>
        <div class="col-12">
            <label class="form-label" for="rsScriptureReference">Reference</label>
            <input type="text" name="religion_spirituality_scripture_reference" id="rsScriptureReference" class="form-control religion-spirituality-flow-field" maxlength="255" value="{{ old('religion_spirituality_scripture_reference', data_get($post->meta, 'religion_spirituality_scripture_reference')) }}">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Moral message</h5>
            <p class="text-muted mb-0 small">These values can appear separately on the article page.</p>
        </div>
        <span class="badge bg-success text-white">Special section</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityMoralValues() as $value)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="religion_spirituality_moral_messages[]" value="{{ $value }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($value, (array) old('religion_spirituality_moral_messages', data_get($post->meta, 'religion_spirituality_moral_messages', [])), true))>
                <span class="form-check-label">{{ $value }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="rsFestivalSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Festival information</h5>
            <p class="text-muted mb-0 small">If applicable — share festival details respectfully.</p>
        </div>
        <span class="badge bg-warning text-dark">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="rsFestivalName">Festival name</label>
            <input type="text" name="religion_spirituality_festival_name" id="rsFestivalName" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_festival_name', data_get($post->meta, 'religion_spirituality_festival_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsFestivalDate">Date</label>
            <input type="text" name="religion_spirituality_festival_date" id="rsFestivalDate" class="form-control religion-spirituality-flow-field" maxlength="120" value="{{ old('religion_spirituality_festival_date', data_get($post->meta, 'religion_spirituality_festival_date')) }}" placeholder="e.g. March 2026 or seasonal">
        </div>
        <div class="col-12">
            <label class="form-label" for="rsFestivalHistoricalSignificance">Historical significance</label>
            <textarea name="religion_spirituality_festival_historical_significance" id="rsFestivalHistoricalSignificance" class="form-control religion-spirituality-flow-field" rows="2" maxlength="3000">{{ old('religion_spirituality_festival_historical_significance', data_get($post->meta, 'religion_spirituality_festival_historical_significance')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsFestivalTraditionalPractices">Traditional practices</label>
            <textarea name="religion_spirituality_festival_traditional_practices" id="rsFestivalTraditionalPractices" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_festival_traditional_practices', data_get($post->meta, 'religion_spirituality_festival_traditional_practices')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsFestivalCelebrationMethods">Celebration methods</label>
            <textarea name="religion_spirituality_festival_celebration_methods" id="rsFestivalCelebrationMethods" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_festival_celebration_methods', data_get($post->meta, 'religion_spirituality_festival_celebration_methods')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="rsFestivalRegionalVariations">Regional variations</label>
            <textarea name="religion_spirituality_festival_regional_variations" id="rsFestivalRegionalVariations" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_festival_regional_variations', data_get($post->meta, 'religion_spirituality_festival_regional_variations')) }}</textarea>
        </div>
    </div>
</div>

<div id="rsPilgrimageSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Pilgrimage section</h5>
            <p class="text-muted mb-0 small">If applicable — guide pilgrims with practical, respectful information.</p>
        </div>
        <span class="badge bg-warning text-dark">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="rsPilgrimageName">Pilgrimage name</label>
            <input type="text" name="religion_spirituality_pilgrimage_name" id="rsPilgrimageName" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_pilgrimage_name', data_get($post->meta, 'religion_spirituality_pilgrimage_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsPilgrimageLocation">Location</label>
            <input type="text" name="religion_spirituality_pilgrimage_location" id="rsPilgrimageLocation" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_pilgrimage_location', data_get($post->meta, 'religion_spirituality_pilgrimage_location')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsPilgrimageBestTime">Best time to visit</label>
            <input type="text" name="religion_spirituality_pilgrimage_best_time" id="rsPilgrimageBestTime" class="form-control religion-spirituality-flow-field" maxlength="120" value="{{ old('religion_spirituality_pilgrimage_best_time', data_get($post->meta, 'religion_spirituality_pilgrimage_best_time')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="rsPilgrimageHistory">History</label>
            <textarea name="religion_spirituality_pilgrimage_history" id="rsPilgrimageHistory" class="form-control religion-spirituality-flow-field" rows="2" maxlength="3000">{{ old('religion_spirituality_pilgrimage_history', data_get($post->meta, 'religion_spirituality_pilgrimage_history')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsPilgrimageFacilities">Facilities</label>
            <textarea name="religion_spirituality_pilgrimage_facilities" id="rsPilgrimageFacilities" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_pilgrimage_facilities', data_get($post->meta, 'religion_spirituality_pilgrimage_facilities')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsPilgrimageTravelTips">Travel tips</label>
            <textarea name="religion_spirituality_pilgrimage_travel_tips" id="rsPilgrimageTravelTips" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_pilgrimage_travel_tips', data_get($post->meta, 'religion_spirituality_pilgrimage_travel_tips')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="rsPilgrimageAccommodation">Accommodation</label>
            <textarea name="religion_spirituality_pilgrimage_accommodation" id="rsPilgrimageAccommodation" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_pilgrimage_accommodation', data_get($post->meta, 'religion_spirituality_pilgrimage_accommodation')) }}</textarea>
        </div>
    </div>
</div>

<div id="rsPlaceOfWorshipSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Place of worship</h5>
            <p class="text-muted mb-0 small">Optional — describe the sacred site respectfully.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="rsPlaceOfWorshipType">Place of worship type</label>
    <select name="religion_spirituality_place_of_worship_type" id="rsPlaceOfWorshipType" class="form-select religion-spirituality-flow-field mb-0">
        <option value="">Select type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityPlaceOfWorshipTypes() as $placeType)
            <option value="{{ $placeType }}" @selected(old('religion_spirituality_place_of_worship_type', data_get($post->meta, 'religion_spirituality_place_of_worship_type')) === $placeType)>{{ $placeType }}</option>
        @endforeach
    </select>
</div>

<div id="rsLocationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Useful for pilgrimage guides and places of worship.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="rsLocationCountry">Country</label>
            <input type="text" name="religion_spirituality_location_country" id="rsLocationCountry" class="form-control religion-spirituality-flow-field" maxlength="120" value="{{ old('religion_spirituality_location_country', data_get($post->meta, 'religion_spirituality_location_country')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsLocationState">State</label>
            <input type="text" name="religion_spirituality_location_state" id="rsLocationState" class="form-control religion-spirituality-flow-field" maxlength="120" value="{{ old('religion_spirituality_location_state', data_get($post->meta, 'religion_spirituality_location_state')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsLocationDistrict">District</label>
            <input type="text" name="religion_spirituality_location_district" id="rsLocationDistrict" class="form-control religion-spirituality-flow-field" maxlength="120" value="{{ old('religion_spirituality_location_district', data_get($post->meta, 'religion_spirituality_location_district')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rsLocationCity">City</label>
            <input type="text" name="religion_spirituality_location_city" id="rsLocationCity" class="form-control religion-spirituality-flow-field" maxlength="120" value="{{ old('religion_spirituality_location_city', data_get($post->meta, 'religion_spirituality_location_city')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="rsLocationGps">GPS / map reference</label>
            <input type="text" name="religion_spirituality_location_gps" id="rsLocationGps" class="form-control religion-spirituality-flow-field" maxlength="255" value="{{ old('religion_spirituality_location_gps', data_get($post->meta, 'religion_spirituality_location_gps')) }}" placeholder="e.g. 27.1751, 78.0421 or Google Maps link">
        </div>
    </div>
</div>

<div id="rsMeditationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Meditation &amp; wellness</h5>
            <p class="text-muted mb-0 small">If applicable — select relevant practices.</p>
        </div>
        <span class="badge bg-warning text-dark">If applicable</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityMeditationTopics() as $topic)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="religion_spirituality_meditation_topics[]" value="{{ $topic }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($topic, (array) old('religion_spirituality_meditation_topics', data_get($post->meta, 'religion_spirituality_meditation_topics', [])), true))>
                <span class="form-check-label">{{ $topic }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="rsCommunityServiceSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community service</h5>
            <p class="text-muted mb-0 small">If applicable — aligns with SoilnWater's community mission.</p>
        </div>
        <span class="badge bg-success text-white">If applicable</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityCommunityServiceActivities() as $activity)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="religion_spirituality_community_service_activities[]" value="{{ $activity }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($activity, (array) old('religion_spirituality_community_service_activities', data_get($post->meta, 'religion_spirituality_community_service_activities', [])), true))>
                <span class="form-check-label">{{ $activity }}</span>
            </label>
        @endforeach
    </div>
</div>
