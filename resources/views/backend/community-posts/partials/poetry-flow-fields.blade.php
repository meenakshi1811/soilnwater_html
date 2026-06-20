@php
    $selectedPoetryThemes = old('poetry_themes', data_get($post->meta, 'poetry_themes', []));
    $selectedPoetryAudiences = old('poetry_target_audience', data_get($post->meta, 'poetry_target_audience', []));
    $existingPoetryAudio = data_get($post->meta, 'poetry_audio');
    $poetryAudioSourceType = old('poetry_audio_source_type', filled($existingPoetryAudio) ? (($existingPoetryAudio['type'] ?? '') === 'recording' ? 'recording' : 'upload') : 'none');
    $poetryPartOfSeries = old('poetry_part_of_series', data_get($post->meta, 'poetry_part_of_series', 'No'));
    $authorUser = auth()->user();
    $authorBioPreview = old('author_bio', data_get($post->meta, 'author_bio'));
    $poetName = $post->exists
        ? $post->authorDisplayName()
        : ($authorUser?->name ?? $authorUser?->full_name ?? 'Your name');
    $poetPhoto = $post->exists
        ? $post->authorAvatarUrl()
        : ($authorUser?->authorImageUrl());
    $poetInitials = $post->exists
        ? $post->authorInitials()
        : (collect(preg_split('/\s+/', trim($poetName)) ?: [])->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: 'P');
    $flowPlacement = $placement ?? 'all';
    $showPoetrySetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showPoetryRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showPoetrySetup)
<div class="news-flow-card story-flow-card story-flow-card--theme border rounded-3 p-3 p-md-4 bg-light mb-3 community-flow-checklist">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Theme</h5>
            <p class="text-muted mb-0 small">Select all themes that apply to your poem.</p>
        </div>
        <span class="badge bg-primary text-white">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::poetryThemes() as $theme)
            <div class="col-md-6 col-lg-4">
                <label class="form-check border rounded p-2 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="poetry_themes[]"
                        value="{{ $theme }}"
                        class="form-check-input"
                        @checked(in_array($theme, (array) $selectedPoetryThemes, true))
                    >
                    <span class="form-check-label">{{ $theme }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Optional — who is this poem meant for?</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::poetryTargetAudiences() as $audience)
            <div class="col-md-6 col-lg-4">
                <label class="form-check border rounded p-2 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="poetry_target_audience[]"
                        value="{{ $audience }}"
                        class="form-check-input"
                        @checked(in_array($audience, (array) $selectedPoetryAudiences, true))
                    >
                    <span class="form-check-label">{{ $audience }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
@endif

@if($showPoetryRest)
<div class="news-flow-card story-flow-card story-flow-card--audio border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio recitation</h5>
            <p class="text-muted mb-0 small">One of the best features — many poets prefer readers to hear the poem.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly recommended</span>
    </div>
    <ul class="story-audio-uses list-unstyled small text-muted mb-3">
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>MP3 upload</li>
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Voice recording in the browser</li>
    </ul>
    <div class="community-audio-field border rounded-3 p-3 bg-light">
        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Poetry audio source type">
            <input type="radio" class="btn-check" name="poetry_audio_source_type" id="poetryAudioSourceNone" value="none" @checked($poetryAudioSourceType === 'none')>
            <label class="btn btn-outline-secondary" for="poetryAudioSourceNone">No audio</label>
            <input type="radio" class="btn-check" name="poetry_audio_source_type" id="poetryAudioSourceUpload" value="upload" @checked($poetryAudioSourceType === 'upload')>
            <label class="btn btn-outline-secondary" for="poetryAudioSourceUpload">MP3 upload</label>
            <input type="radio" class="btn-check" name="poetry_audio_source_type" id="poetryAudioSourceRecording" value="recording" @checked($poetryAudioSourceType === 'recording')>
            <label class="btn btn-outline-secondary" for="poetryAudioSourceRecording">Voice recording</label>
        </div>

        <div id="poetryAudioUploadWrap" class="audio-source-panel">
            <label class="form-label" for="poetryAudioFile">MP3 file</label>
            <input type="file" name="poetry_audio_file" id="poetryAudioFile" class="form-control" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
            <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
        </div>

        <div id="poetryAudioRecordingWrap" class="audio-source-panel">
            <div class="story-audio-recorder border rounded-3 p-3 bg-white">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-danger" id="poetryAudioRecordBtn">
                        <i class="fa-solid fa-microphone me-1"></i>Start recording
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="poetryAudioStopBtn" disabled>Stop</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="poetryAudioClearRecordingBtn" disabled>Clear</button>
                    <span class="small text-muted" id="poetryAudioRecordingStatus">Ready to record.</span>
                </div>
                <audio id="poetryAudioRecordingPreview" controls class="w-100" style="display:none;"></audio>
                <small class="text-muted d-block">Record your poem recitation. The recording is saved when you submit the form.</small>
            </div>
        </div>

        @if(filled($existingPoetryAudio))
            <input type="hidden" name="keep_existing_poetry_audio" id="keepExistingPoetryAudio" value="1">
            <div id="existingPoetryAudioPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                <div class="small">
                    <strong>Current recitation:</strong> {{ $existingPoetryAudio['name'] ?? 'Audio recitation' }}
                    @if(filled($existingPoetryAudio['url'] ?? null))
                        <audio controls class="d-block mt-2 w-100" src="{{ $existingPoetryAudio['url'] }}"></audio>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger align-self-start" id="removeExistingPoetryAudioBtn">Remove</button>
            </div>
        @endif
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Useful for regional discovery.</p>
        </div>
        <span class="badge bg-success text-white">Regional discovery</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="poetryLocationCountry">Country</label>
            <input type="text" name="location_country" id="poetryLocationCountry" class="form-control" value="{{ old('location_country', data_get($post->meta, 'location_country')) }}" maxlength="120" placeholder="e.g. India">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="poetryLocationState">State</label>
            <input type="text" name="location_state" id="poetryLocationState" class="form-control" value="{{ old('location_state', data_get($post->meta, 'location_state')) }}" maxlength="120" placeholder="e.g. Rajasthan">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="poetryLocationDistrict">District</label>
            <input type="text" name="location_district" id="poetryLocationDistrict" class="form-control" value="{{ old('location_district', data_get($post->meta, 'location_district')) }}" maxlength="120" placeholder="e.g. Jaipur">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="poetryLocationCity">City</label>
            <input type="text" name="location_city" id="poetryLocationCity" class="form-control" value="{{ old('location_city', data_get($post->meta, 'location_city')) }}" maxlength="120" placeholder="e.g. Jaipur">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Author information</h5>
            <p class="text-muted mb-0 small">Auto-filled from your profile and publish settings.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Auto-filled</span>
    </div>
    <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="poetry-author-preview__avatar">
            @if(filled($poetPhoto))
                <img src="{{ $poetPhoto }}" alt="{{ $poetName }}" class="rounded-circle">
            @else
                <span class="poetry-author-preview__initials rounded-circle">{{ $poetInitials }}</span>
            @endif
        </div>
        <div class="flex-grow-1">
            <div class="mb-2"><strong>Poet name:</strong> {{ $poetName }}</div>
            <label class="form-label" for="author_bio">Bio</label>
            <input type="text" name="author_bio" id="author_bio" class="form-control general-extra-field" value="{{ $authorBioPreview }}" maxlength="500" placeholder="Short author bio shown on the poem page">
            <small class="text-muted d-block mt-1">Displayed on the public poem page with your photo and name.</small>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Inspiration</h5>
            <p class="text-muted mb-0 small">What inspired this poem? This increases reader engagement.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="poetry_inspiration">What inspired this poem?</label>
    <textarea
        name="poetry_inspiration"
        id="poetry_inspiration"
        class="form-control"
        rows="3"
        maxlength="2000"
        placeholder="Inspired by a childhood visit to a village pond."
    >{{ old('poetry_inspiration', data_get($post->meta, 'poetry_inspiration')) }}</textarea>
    <small class="text-muted d-block mt-1">Example: Inspired by a childhood visit to a village pond.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Series option</h5>
            <p class="text-muted mb-0 small">Is this poem part of a poetry collection?</p>
        </div>
        <span class="badge bg-info text-white">Collection</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Part of a poetry collection?</label>
            <div class="d-flex flex-wrap gap-3">
                <label class="form-check">
                    <input type="radio" name="poetry_part_of_series" value="Yes" class="form-check-input js-poetry-series-toggle" @checked($poetryPartOfSeries === 'Yes')>
                    <span class="form-check-label">Yes</span>
                </label>
                <label class="form-check">
                    <input type="radio" name="poetry_part_of_series" value="No" class="form-check-input js-poetry-series-toggle" @checked($poetryPartOfSeries !== 'Yes')>
                    <span class="form-check-label">No</span>
                </label>
            </div>
        </div>
        <div class="col-md-4" id="poetrySeriesNameWrap">
            <label class="form-label" for="poetry_series_name">Collection name</label>
            <input type="text" name="poetry_series_name" id="poetry_series_name" class="form-control" value="{{ old('poetry_series_name', data_get($post->meta, 'poetry_series_name')) }}" maxlength="160" placeholder="Nature Collection">
        </div>
        <div class="col-md-4" id="poetrySeriesPartWrap">
            <label class="form-label" for="poetry_series_part">Part</label>
            <input type="text" name="poetry_series_part" id="poetry_series_part" class="form-control" value="{{ old('poetry_series_part', data_get($post->meta, 'poetry_series_part')) }}" maxlength="40" placeholder="Part 1">
            <small class="text-muted d-block mt-1">Example: Part 1, Part 2, Part 3</small>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div>
            <h5 class="mb-1">Rating system</h5>
            <p class="text-muted mb-0 small">Readers can rate this poem from 1 to 5 stars on the public page.</p>
        </div>
        <span class="badge bg-warning text-dark">Optional for readers</span>
    </div>
</div>
@endif
