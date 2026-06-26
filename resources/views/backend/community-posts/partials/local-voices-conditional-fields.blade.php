@php
    $selectedInitiatives = old('local_voice_initiatives', data_get($post->meta, 'local_voice_initiatives', []));
    $voiceType = old('local_voice_type', data_get($post->meta, 'local_voice_type'));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="localVoiceHeroSection" style="{{ $voiceType === 'Local Hero' ? '' : 'display:none;' }}">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Local hero section</h5>
            <p class="text-muted mb-0 small">Shown when voice type is Local Hero.</p>
        </div>
        <span class="badge bg-warning text-dark">Local Hero</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="localVoiceHeroName">Person name</label>
            <input type="text" name="local_voice_hero_name" id="localVoiceHeroName" class="form-control local-voices-flow-field" maxlength="160" value="{{ old('local_voice_hero_name', data_get($post->meta, 'local_voice_hero_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="localVoiceHeroLocation">Location</label>
            <input type="text" name="local_voice_hero_location" id="localVoiceHeroLocation" class="form-control local-voices-flow-field" maxlength="160" value="{{ old('local_voice_hero_location', data_get($post->meta, 'local_voice_hero_location')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="localVoiceHeroContribution">Contribution</label>
            <textarea name="local_voice_hero_contribution" id="localVoiceHeroContribution" class="form-control local-voices-flow-field" rows="3" maxlength="2000">{{ old('local_voice_hero_contribution', data_get($post->meta, 'local_voice_hero_contribution')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="localVoiceHeroAchievements">Achievements</label>
            <textarea name="local_voice_hero_achievements" id="localVoiceHeroAchievements" class="form-control local-voices-flow-field" rows="3" maxlength="2000">{{ old('local_voice_hero_achievements', data_get($post->meta, 'local_voice_hero_achievements')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="localVoiceHeroImages">Hero images</label>
            <input type="file" name="local_voice_hero_images[]" id="localVoiceHeroImages" class="form-control local-voices-flow-field" accept="image/*" multiple>
            <small class="text-muted d-block mt-2">Optional images. Up to 6 files, max 4 MB each.</small>
            @if(!empty(data_get($post->meta, 'local_voice_hero_images')))
                <div class="mt-3 d-flex flex-column gap-2">
                    @foreach(data_get($post->meta, 'local_voice_hero_images', []) as $image)
                        <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                            <input type="checkbox" name="removed_local_voice_hero_images[]" value="{{ data_get($image, 'path') }}" class="form-check-input local-voices-flow-field">
                            <span class="form-check-label">Remove {{ data_get($image, 'name', 'image') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="localVoiceInitiativeSection" style="{{ $voiceType === 'Community Initiative' ? '' : 'display:none;' }}">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community initiative section</h5>
            <p class="text-muted mb-0 small">Shown when voice type is Community Initiative.</p>
        </div>
        <span class="badge bg-primary text-white">Initiative</span>
    </div>
    <label class="form-label d-block">Initiative examples</label>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceInitiativeExamples() as $initiative)
            <div class="col-md-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="local_voice_initiatives[]"
                        value="{{ $initiative }}"
                        class="form-check-input local-voices-flow-field"
                        @checked(in_array($initiative, (array) $selectedInitiatives, true))
                    >
                    <span class="form-check-label">{{ $initiative }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="localVoiceEventSection">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Event details</h5>
            <p class="text-muted mb-0 small">If applicable — date, time, venue, and organizer.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="localVoiceEventDate">Date</label>
            <input type="date" name="local_voice_event_date" id="localVoiceEventDate" class="form-control local-voices-flow-field" value="{{ old('local_voice_event_date', data_get($post->meta, 'local_voice_event_date')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="localVoiceEventTime">Time</label>
            <input type="text" name="local_voice_event_time" id="localVoiceEventTime" class="form-control local-voices-flow-field" maxlength="40" value="{{ old('local_voice_event_time', data_get($post->meta, 'local_voice_event_time')) }}" placeholder="e.g. 10:00 AM">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="localVoiceEventOrganizer">Organizer</label>
            <input type="text" name="local_voice_event_organizer" id="localVoiceEventOrganizer" class="form-control local-voices-flow-field" maxlength="160" value="{{ old('local_voice_event_organizer', data_get($post->meta, 'local_voice_event_organizer')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="localVoiceEventVenue">Venue</label>
            <input type="text" name="local_voice_event_venue" id="localVoiceEventVenue" class="form-control local-voices-flow-field" maxlength="160" value="{{ old('local_voice_event_venue', data_get($post->meta, 'local_voice_event_venue')) }}" placeholder="e.g. Community Hall, Prem Nagar">
        </div>
    </div>
</div>
