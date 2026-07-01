<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary text-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-2">
        <div>
            <h5 class="mb-1">Unique features for SoilnWater</h5>
            <p class="mb-0 small opacity-75">Flag posts for discovery in SoilnWater&apos;s Religion &amp; Spirituality flagship programs.</p>
        </div>
        <span class="badge bg-warning text-dark">SoilnWater unique</span>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-3" for="rsEnableDigitalPilgrimageGuide">
        <input type="checkbox" name="religion_spirituality_enable_digital_pilgrimage_guide" value="1" class="form-check-input religion-spirituality-flow-field" id="rsEnableDigitalPilgrimageGuide" @checked(old('religion_spirituality_enable_digital_pilgrimage_guide', data_get($post->meta, 'religion_spirituality_enable_digital_pilgrimage_guide', false)))>
        <span class="form-check-label fw-semibold">1. Digital Pilgrimage Guide</span>
        <span class="d-block small text-muted">Verified information about sacred sites — maps, facilities, accommodation, and local SoilnWater businesses.</span>
    </label>
    <div id="rsDigitalPilgrimageGuideFields" style="display:none;">
        <label class="form-label d-block">Sacred site types</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityDigitalPilgrimageSiteTypes() as $siteType)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="religion_spirituality_digital_pilgrimage_site_types[]" value="{{ $siteType }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($siteType, (array) old('religion_spirituality_digital_pilgrimage_site_types', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_site_types', [])), true))>
                    <span class="form-check-label">{{ $siteType }}</span>
                </label>
            @endforeach
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="rsDigitalPilgrimageSiteName">Site name</label>
                <input type="text" name="religion_spirituality_digital_pilgrimage_site_name" id="rsDigitalPilgrimageSiteName" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_digital_pilgrimage_site_name', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_site_name')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="rsDigitalPilgrimageMapUrl">Map / GPS link</label>
                <input type="text" name="religion_spirituality_digital_pilgrimage_map_url" id="rsDigitalPilgrimageMapUrl" class="form-control religion-spirituality-flow-field" maxlength="255" value="{{ old('religion_spirituality_digital_pilgrimage_map_url', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_map_url')) }}" placeholder="Google Maps URL or coordinates">
            </div>
            <div class="col-12">
                <label class="form-label" for="rsDigitalPilgrimageVerifiedInfo">Verified information summary</label>
                <textarea name="religion_spirituality_digital_pilgrimage_verified_info" id="rsDigitalPilgrimageVerifiedInfo" class="form-control religion-spirituality-flow-field" rows="2" maxlength="3000">{{ old('religion_spirituality_digital_pilgrimage_verified_info', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_verified_info')) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="rsDigitalPilgrimageNearbyFacilities">Nearby facilities</label>
                <textarea name="religion_spirituality_digital_pilgrimage_nearby_facilities" id="rsDigitalPilgrimageNearbyFacilities" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_digital_pilgrimage_nearby_facilities', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_nearby_facilities')) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="rsDigitalPilgrimageAccommodation">Accommodation</label>
                <textarea name="religion_spirituality_digital_pilgrimage_accommodation" id="rsDigitalPilgrimageAccommodation" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_digital_pilgrimage_accommodation', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_accommodation')) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="rsDigitalPilgrimageLocalBusinesses">Local businesses on SoilnWater</label>
                <textarea name="religion_spirituality_digital_pilgrimage_local_businesses" id="rsDigitalPilgrimageLocalBusinesses" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000" placeholder="List nearby shops, lodges, or services listed on SoilnWater">{{ old('religion_spirituality_digital_pilgrimage_local_businesses', data_get($post->meta, 'religion_spirituality_digital_pilgrimage_local_businesses')) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-check border rounded p-3 bg-white mb-3" for="rsEnableFestivalCalendar">
        <input type="checkbox" name="religion_spirituality_enable_festival_calendar" value="1" class="form-check-input religion-spirituality-flow-field" id="rsEnableFestivalCalendar" @checked(old('religion_spirituality_enable_festival_calendar', data_get($post->meta, 'religion_spirituality_enable_festival_calendar', false)))>
        <span class="form-check-label fw-semibold">2. Festival Calendar</span>
        <span class="d-block small text-muted">Interactive calendar entry with educational article links for festivals, holidays, and community celebrations.</span>
    </label>
    <div id="rsFestivalCalendarFields" style="display:none;">
        <label class="form-label d-block">Event types</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityFestivalCalendarEventTypes() as $eventType)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="religion_spirituality_festival_calendar_event_types[]" value="{{ $eventType }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($eventType, (array) old('religion_spirituality_festival_calendar_event_types', data_get($post->meta, 'religion_spirituality_festival_calendar_event_types', [])), true))>
                    <span class="form-check-label">{{ $eventType }}</span>
                </label>
            @endforeach
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="rsFestivalCalendarEventName">Event name</label>
                <input type="text" name="religion_spirituality_festival_calendar_event_name" id="rsFestivalCalendarEventName" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_festival_calendar_event_name', data_get($post->meta, 'religion_spirituality_festival_calendar_event_name')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="rsFestivalCalendarEventDate">Event date</label>
                <input type="date" name="religion_spirituality_festival_calendar_event_date" id="rsFestivalCalendarEventDate" class="form-control religion-spirituality-flow-field" value="{{ old('religion_spirituality_festival_calendar_event_date', data_get($post->meta, 'religion_spirituality_festival_calendar_event_date')) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="rsFestivalCalendarDescription">Calendar description</label>
                <textarea name="religion_spirituality_festival_calendar_description" id="rsFestivalCalendarDescription" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_festival_calendar_description', data_get($post->meta, 'religion_spirituality_festival_calendar_description')) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="rsFestivalCalendarLinkedArticleUrl">Linked educational article URL</label>
                <input type="url" name="religion_spirituality_festival_calendar_linked_article_url" id="rsFestivalCalendarLinkedArticleUrl" class="form-control religion-spirituality-flow-field" maxlength="255" value="{{ old('religion_spirituality_festival_calendar_linked_article_url', data_get($post->meta, 'religion_spirituality_festival_calendar_linked_article_url')) }}" placeholder="https://soilnwater.com/community/...">
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-check border rounded p-3 bg-light mb-3" for="rsEnableCommunityServiceDirectory">
        <input type="checkbox" name="religion_spirituality_enable_community_service_directory" value="1" class="form-check-input religion-spirituality-flow-field" id="rsEnableCommunityServiceDirectory" @checked(old('religion_spirituality_enable_community_service_directory', data_get($post->meta, 'religion_spirituality_enable_community_service_directory', false)))>
        <span class="form-check-label fw-semibold">3. Community Service Directory</span>
        <span class="d-block small text-muted">Highlight volunteer opportunities organized by religious and community organizations.</span>
    </label>
    <div id="rsCommunityServiceDirectoryFields" style="display:none;">
        <label class="form-label d-block">Volunteer opportunities</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityServiceDirectoryOpportunities() as $opportunity)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="religion_spirituality_service_directory_opportunities[]" value="{{ $opportunity }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($opportunity, (array) old('religion_spirituality_service_directory_opportunities', data_get($post->meta, 'religion_spirituality_service_directory_opportunities', [])), true))>
                    <span class="form-check-label">{{ $opportunity }}</span>
                </label>
            @endforeach
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="rsServiceDirectoryOrganization">Organization</label>
                <input type="text" name="religion_spirituality_service_directory_organization" id="rsServiceDirectoryOrganization" class="form-control religion-spirituality-flow-field" maxlength="160" value="{{ old('religion_spirituality_service_directory_organization', data_get($post->meta, 'religion_spirituality_service_directory_organization')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="rsServiceDirectoryWhenWhere">When &amp; where</label>
                <input type="text" name="religion_spirituality_service_directory_when_where" id="rsServiceDirectoryWhenWhere" class="form-control religion-spirituality-flow-field" maxlength="255" value="{{ old('religion_spirituality_service_directory_when_where', data_get($post->meta, 'religion_spirituality_service_directory_when_where')) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="rsServiceDirectoryVolunteerNotes">Volunteer notes</label>
                <textarea name="religion_spirituality_service_directory_volunteer_notes" id="rsServiceDirectoryVolunteerNotes" class="form-control religion-spirituality-flow-field" rows="2" maxlength="2000">{{ old('religion_spirituality_service_directory_volunteer_notes', data_get($post->meta, 'religion_spirituality_service_directory_volunteer_notes')) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-check border rounded p-3 bg-white mb-3" for="rsEnableWisdomLibrary">
        <input type="checkbox" name="religion_spirituality_enable_wisdom_library" value="1" class="form-check-input religion-spirituality-flow-field" id="rsEnableWisdomLibrary" @checked(old('religion_spirituality_enable_wisdom_library', data_get($post->meta, 'religion_spirituality_enable_wisdom_library', false)))>
        <span class="form-check-label fw-semibold">4. Wisdom Library</span>
        <span class="d-block small text-muted">Searchable teachings on universal themes organized across traditions to promote shared human values.</span>
    </label>
    <div id="rsWisdomLibraryFields" style="display:none;">
        <label class="form-label d-block">Universal themes</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityWisdomLibraryThemes() as $theme)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="religion_spirituality_wisdom_themes[]" value="{{ $theme }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($theme, (array) old('religion_spirituality_wisdom_themes', data_get($post->meta, 'religion_spirituality_wisdom_themes', [])), true))>
                    <span class="form-check-label">{{ $theme }}</span>
                </label>
            @endforeach
        </div>
        <label class="form-label d-block">Traditions covered</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityTraditions() as $tradition)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="religion_spirituality_wisdom_traditions[]" value="{{ $tradition }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($tradition, (array) old('religion_spirituality_wisdom_traditions', data_get($post->meta, 'religion_spirituality_wisdom_traditions', [])), true))>
                    <span class="form-check-label">{{ $tradition }}</span>
                </label>
            @endforeach
        </div>
        <label class="form-label" for="rsWisdomCollectionSummary">Collection summary</label>
        <textarea name="religion_spirituality_wisdom_collection_summary" id="rsWisdomCollectionSummary" class="form-control religion-spirituality-flow-field" rows="3" maxlength="3000" placeholder="How this teaching connects values across traditions">{{ old('religion_spirituality_wisdom_collection_summary', data_get($post->meta, 'religion_spirituality_wisdom_collection_summary')) }}</textarea>
    </div>
</div>
