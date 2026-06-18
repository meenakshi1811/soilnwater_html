@php
    $authorUser = auth()->user();
    $authorBioPreview = old('author_bio', data_get($post->meta, 'author_bio'));
    $authorName = $post->exists
        ? $post->authorDisplayName()
        : ($authorUser?->name ?? $authorUser?->full_name ?? 'Your name');
    $authorPhoto = $post->exists
        ? $post->authorAvatarUrl()
        : ($authorUser?->authorImageUrl());
    $authorLocation = collect([
        $authorUser?->city,
        $authorUser?->address,
    ])->filter()->implode(', ');
    $initialLifeTimeline = old('life_timeline', data_get($post->meta, 'life_timeline', []));
    if (! is_array($initialLifeTimeline)) {
        $initialLifeTimeline = [];
    }
    $existingAutobiographyAudio = data_get($post->meta, 'autobiography_audio');
    $autobiographyAudioSourceType = old('autobiography_audio_source_type', filled($existingAutobiographyAudio) ? (($existingAutobiographyAudio['type'] ?? '') === 'recording' ? 'recording' : 'upload') : 'none');
    $initialPlacesMentioned = old('places_mentioned', data_get($post->meta, 'places_mentioned', []));
    if (! is_array($initialPlacesMentioned)) {
        $initialPlacesMentioned = [];
    }
    $initialKeyLessons = old('key_lessons_learned', data_get($post->meta, 'key_lessons_learned', []));
    if (! is_array($initialKeyLessons)) {
        $initialKeyLessons = [];
    }
    $initialRelatedPeople = old('related_people', data_get($post->meta, 'related_people', []));
    if (! is_array($initialRelatedPeople)) {
        $initialRelatedPeople = [];
    }
    $initialAchievements = old('autobiography_achievements', data_get($post->meta, 'autobiography_achievements', []));
    if (! is_array($initialAchievements)) {
        $initialAchievements = [];
    }
@endphp

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
            @if(filled($authorPhoto))
                <img src="{{ $authorPhoto }}" alt="{{ $authorName }}" class="rounded-circle">
            @else
                <span class="poetry-author-preview__initials rounded-circle">{{ $post->exists ? $post->authorInitials() : ($authorUser?->authorInitials() ?? 'A') }}</span>
            @endif
        </div>
        <div class="flex-grow-1">
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <div><strong>Author name:</strong> {{ $authorName }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Location:</strong> {{ filled($authorLocation) ? $authorLocation : 'Add city or address in your profile' }}</div>
                </div>
            </div>
            <label class="form-label" for="autobiography_author_bio">Bio <span class="text-danger">*</span></label>
            <input
                type="text"
                name="author_bio"
                id="autobiography_author_bio"
                class="form-control autobiography-required"
                value="{{ $authorBioPreview }}"
                maxlength="500"
                placeholder="Short author bio shown on your autobiography page"
            >
            <small class="text-muted d-block mt-1">Displayed on the public page with your photo and name.</small>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location details</h5>
            <p class="text-muted mb-0 small">Help readers understand where your story began and where it unfolded.</p>
        </div>
        <span class="badge bg-success text-white">Regional context</span>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label" for="birth_place">Birth place</label>
            <input type="text" name="birth_place" id="birth_place" class="form-control" value="{{ old('birth_place', data_get($post->meta, 'birth_place')) }}" maxlength="160" placeholder="e.g. Dehradun, Uttarakhand">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="current_location">Current location</label>
            <input type="text" name="current_location" id="current_location" class="form-control" value="{{ old('current_location', data_get($post->meta, 'current_location')) }}" maxlength="160" placeholder="Where you live now">
        </div>
    </div>
    <label class="form-label">Places mentioned</label>
    <p class="text-muted small mb-2">Add multiple locations that shaped your journey.</p>
    <div class="autobiography-list-example small text-muted mb-2">
        <strong>Example:</strong> Dehradun · Delhi · Mumbai
    </div>
    <div id="placesMentionedEntries" class="d-flex flex-column gap-2 mb-2"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addPlaceMentionedBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add place
    </button>
    <template id="placeMentionedTemplate">
        <div class="autobiography-list-entry d-flex gap-2 align-items-center">
            <input type="text" class="form-control js-place-mentioned-input" maxlength="120" placeholder="City or region" data-name="places_mentioned[__INDEX__]">
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-list-entry">Remove</button>
        </div>
    </template>
</div>

<div class="news-flow-card story-flow-card story-flow-card--timeline border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Life timeline</h5>
            <p class="text-muted mb-0 small">Add important milestones to make your autobiography more engaging.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly recommended</span>
    </div>
    <div class="autobiography-timeline-example small text-muted mb-3">
        <strong>Example:</strong>
        <div class="mt-2">
            <div><strong>1985</strong> — Born in Dehradun</div>
            <div><strong>2006</strong> — Completed Engineering</div>
            <div><strong>2015</strong> — Started Business</div>
            <div><strong>2023</strong> — Expanded Operations</div>
        </div>
    </div>
    <div id="lifeTimelineEntries" class="d-flex flex-column gap-3"></div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="addLifeTimelineEntryBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add milestone
    </button>
    <template id="lifeTimelineEntryTemplate">
        <div class="autobiography-timeline-entry border rounded-3 p-3 bg-white" data-timeline-index="">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h6 class="mb-0 autobiography-timeline-entry__title">Milestone</h6>
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-timeline-entry">Remove</button>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Year <span class="text-danger">*</span></label>
                    <input type="text" class="form-control js-timeline-year" maxlength="10" placeholder="e.g. 1985" data-name="life_timeline[__INDEX__][year]">
                </div>
                <div class="col-md-9">
                    <label class="form-label">Event title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control js-timeline-title" maxlength="160" placeholder="Born in Dehradun" data-name="life_timeline[__INDEX__][title]">
                </div>
                <div class="col-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control js-timeline-description" rows="2" maxlength="2000" placeholder="Briefly describe this milestone." data-name="life_timeline[__INDEX__][description]"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Photo</label>
                    <input type="file" class="form-control js-timeline-photo" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif" data-name="life_timeline[__INDEX__][photo]">
                    <input type="hidden" class="js-timeline-existing-photo-path" data-name="life_timeline[__INDEX__][existing_photo_path]">
                    <div class="js-timeline-photo-preview mt-2" style="display:none;">
                        <img src="" alt="Milestone photo preview" class="rounded border" style="max-width:120px;max-height:120px;object-fit:cover;">
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audio border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio memories</h5>
            <p class="text-muted mb-0 small">Share your voice — personal stories become much more powerful when heard.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly valuable</span>
    </div>
    <ul class="story-audio-uses list-unstyled small text-muted mb-3">
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>MP3 upload</li>
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Voice recording in the browser</li>
    </ul>
    <div class="community-audio-field border rounded-3 p-3 bg-light">
        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Autobiography audio source type">
            <input type="radio" class="btn-check" name="autobiography_audio_source_type" id="autobiographyAudioSourceNone" value="none" @checked($autobiographyAudioSourceType === 'none')>
            <label class="btn btn-outline-secondary" for="autobiographyAudioSourceNone">No audio</label>
            <input type="radio" class="btn-check" name="autobiography_audio_source_type" id="autobiographyAudioSourceUpload" value="upload" @checked($autobiographyAudioSourceType === 'upload')>
            <label class="btn btn-outline-secondary" for="autobiographyAudioSourceUpload">MP3 upload</label>
            <input type="radio" class="btn-check" name="autobiography_audio_source_type" id="autobiographyAudioSourceRecording" value="recording" @checked($autobiographyAudioSourceType === 'recording')>
            <label class="btn btn-outline-secondary" for="autobiographyAudioSourceRecording">Voice recording</label>
        </div>

        <div id="autobiographyAudioUploadWrap" class="audio-source-panel">
            <label class="form-label" for="autobiographyAudioFile">MP3 file</label>
            <input type="file" name="autobiography_audio_file" id="autobiographyAudioFile" class="form-control" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
            <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
        </div>

        <div id="autobiographyAudioRecordingWrap" class="audio-source-panel">
            <div class="story-audio-recorder border rounded-3 p-3 bg-white">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-danger" id="autobiographyAudioRecordBtn">
                        <i class="fa-solid fa-microphone me-1"></i>Start recording
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="autobiographyAudioStopBtn" disabled>Stop</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="autobiographyAudioClearRecordingBtn" disabled>Clear</button>
                    <span class="small text-muted" id="autobiographyAudioRecordingStatus">Ready to record.</span>
                </div>
                <audio id="autobiographyAudioRecordingPreview" controls class="w-100" style="display:none;"></audio>
                <small class="text-muted d-block">Record a personal memory or message. The recording is saved when you submit the form.</small>
            </div>
        </div>

        @if(filled($existingAutobiographyAudio))
            <input type="hidden" name="keep_existing_autobiography_audio" id="keepExistingAutobiographyAudio" value="1">
            <div id="existingAutobiographyAudioPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                <div class="small">
                    <strong>Current audio memory:</strong> {{ $existingAutobiographyAudio['name'] ?? 'Audio memory' }}
                    @if(filled($existingAutobiographyAudio['url'] ?? null))
                        <audio controls class="d-block mt-2 w-100" src="{{ $existingAutobiographyAudio['url'] }}"></audio>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger align-self-start" id="removeExistingAutobiographyAudioBtn">Remove</button>
            </div>
        @endif
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--lessons border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Inspirational lessons</h5>
            <p class="text-muted mb-0 small">Key lessons learned — highlighted separately on your public page.</p>
        </div>
        <span class="badge bg-primary text-white">Dedicated section</span>
    </div>
    <div class="autobiography-list-example small text-muted mb-3">
        <strong>Example:</strong>
        <ul class="mb-0 mt-2">
            <li>Never stop learning.</li>
            <li>Hard work eventually pays off.</li>
            <li>Family support is priceless.</li>
        </ul>
    </div>
    <div id="keyLessonsEntries" class="d-flex flex-column gap-2 mb-2"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addKeyLessonBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add lesson
    </button>
    <template id="keyLessonTemplate">
        <div class="autobiography-list-entry d-flex gap-2 align-items-center">
            <input type="text" class="form-control js-key-lesson-input" maxlength="300" placeholder="Never stop learning." data-name="key_lessons_learned[__INDEX__]">
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-list-entry">Remove</button>
        </div>
    </template>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Achievements</h5>
            <p class="text-muted mb-0 small">Optional awards and recognitions from your life journey.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div id="autobiographyAchievementEntries" class="d-flex flex-column gap-3"></div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="addAutobiographyAchievementBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add achievement
    </button>
    <template id="autobiographyAchievementTemplate">
        <div class="autobiography-achievement-entry border rounded-3 p-3 bg-white" data-achievement-index="">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h6 class="mb-0 autobiography-achievement-entry__title">Achievement</h6>
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-achievement-entry">Remove</button>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Award name</label>
                    <input type="text" class="form-control js-achievement-award-name" maxlength="160" placeholder="Community Service Award" data-name="autobiography_achievements[__INDEX__][award_name]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <input type="text" class="form-control js-achievement-year" maxlength="10" placeholder="2018" data-name="autobiography_achievements[__INDEX__][year]">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control js-achievement-description" rows="2" maxlength="1000" placeholder="Briefly describe this achievement." data-name="autobiography_achievements[__INDEX__][description]"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supporting image</label>
                    <input type="file" class="form-control js-achievement-image" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif" data-name="autobiography_achievements[__INDEX__][image]">
                    <input type="hidden" class="js-achievement-existing-image-path" data-name="autobiography_achievements[__INDEX__][existing_image_path]">
                    <div class="js-achievement-image-preview mt-2" style="display:none;">
                        <img src="" alt="Achievement image preview" class="rounded border" style="max-width:120px;max-height:120px;object-fit:cover;">
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Upload certificates, awards, press coverage, books, or publications.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <input type="file" name="autobiography_documents[]" id="autobiographyDocumentsInput" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple>
    <small class="text-muted d-block mt-2">Supported: PDF, DOC, DOCX. Up to 10 files, 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'autobiography_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'autobiography_documents', []) as $document)
                <label class="form-check border rounded p-2 bg-light mb-0">
                    <input type="checkbox" name="removed_autobiography_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input">
                    <span class="form-check-label">Remove: {{ data_get($document, 'name', 'Document') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Related people</h5>
            <p class="text-muted mb-0 small">Mention important individuals who shaped your journey.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="autobiography-list-example small text-muted mb-3">
        <strong>Example:</strong> Parents · Teachers · Mentors · Friends · Business Partners
    </div>
    <div id="relatedPeopleEntries" class="d-flex flex-column gap-2 mb-2"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addRelatedPersonBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add person
    </button>
    <template id="relatedPersonTemplate">
        <div class="autobiography-related-person-entry border rounded-3 p-3 bg-white">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control js-related-person-name" maxlength="120" placeholder="Person name" data-name="related_people[__INDEX__][name]">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Relationship</label>
                    <input type="text" class="form-control js-related-person-relationship" maxlength="80" placeholder="Parent, Teacher, Mentor" data-name="related_people[__INDEX__][relationship]">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 js-remove-related-person">Remove</button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    window.communityLifeTimeline = @json(collect($initialLifeTimeline)->map(function ($entry) {
        return [
            'year' => (string) data_get($entry, 'year', ''),
            'title' => (string) data_get($entry, 'title', ''),
            'description' => (string) data_get($entry, 'description', ''),
            'existing_photo_path' => (string) data_get($entry, 'photo.path', data_get($entry, 'photo_path', '')),
            'existing_photo_url' => (string) data_get($entry, 'photo.url', ''),
        ];
    })->values()->all());
    window.communityPlacesMentioned = @json(array_values($initialPlacesMentioned));
    window.communityKeyLessons = @json(array_values($initialKeyLessons));
    window.communityRelatedPeople = @json(collect($initialRelatedPeople)->map(function ($person) {
        return [
            'name' => (string) data_get($person, 'name', ''),
            'relationship' => (string) data_get($person, 'relationship', ''),
        ];
    })->values()->all());
    window.communityAutobiographyAchievements = @json(collect($initialAchievements)->map(function ($entry) {
        return [
            'award_name' => (string) data_get($entry, 'award_name', ''),
            'year' => (string) data_get($entry, 'year', ''),
            'description' => (string) data_get($entry, 'description', ''),
            'existing_image_path' => (string) data_get($entry, 'image.path', ''),
            'existing_image_url' => (string) data_get($entry, 'image.url', ''),
        ];
    })->values()->all());
</script>
