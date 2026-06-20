@php
    $childShareType = old('child_share_type', data_get($post->meta, 'child_share_type', $post->category));
    $childContentMode = \App\Support\CommunityContentTaxonomy::childrensCornerContentMode(is_string($childShareType) ? $childShareType : null);
    $existingArtImage = data_get($post->meta, 'childrens_corner_art');
    $existingProjectFiles = array_values((array) data_get($post->meta, 'childrens_corner_project_files', []));
    $initialQuizQuestions = old('childrens_corner_quiz', data_get($post->meta, 'childrens_corner_quiz', []));
    if (! is_array($initialQuizQuestions)) {
        $initialQuizQuestions = [];
    }
    $communityChildrensQuizForJs = collect($initialQuizQuestions)->map(function ($question) {
        return [
            'question' => (string) data_get($question, 'question', ''),
            'options' => array_values(array_map(
                fn (mixed $option): string => trim((string) $option),
                (array) data_get($question, 'options', [])
            )),
            'correct_answer' => (string) data_get($question, 'correct_answer', ''),
        ];
    })->values()->all();
    $existingChildrensVideo = data_get($post->meta, 'childrens_corner_video');
    $existingChildrensAudio = data_get($post->meta, 'childrens_corner_audio');
    $existingChildrensCertificate = data_get($post->meta, 'childrens_corner_certificate');
    $childrensVideoSourceType = old('childrens_corner_video_source_type', filled($existingChildrensVideo) ? ($existingChildrensVideo['type'] ?? 'none') : 'none');
    $childrensAudioSourceType = old('childrens_corner_audio_source_type', filled($existingChildrensAudio) ? (($existingChildrensAudio['type'] ?? '') === 'recording' ? 'recording' : 'upload') : 'none');
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Child profile</h5>
            <p class="text-muted mb-0 small">Display name only — first name is shown publicly.</p>
        </div>
        <span class="badge bg-primary text-white">Children's Corner</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="childFirstName">First name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="child_first_name"
                id="childFirstName"
                class="form-control childrens-corner-required"
                value="{{ old('child_first_name', data_get($post->meta, 'child_first_name')) }}"
                maxlength="80"
                placeholder="e.g. Aarav, Riya, Ananya"
                required
            >
            <small class="text-muted">Display name only</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="childAgeGroup">Age group <span class="text-danger">*</span></label>
            <select name="child_age_group" id="childAgeGroup" class="form-select childrens-corner-required" required>
                <option value="">Select age group</option>
                @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerAgeGroups() as $ageGroup)
                    <option value="{{ $ageGroup }}" @selected(old('child_age_group', data_get($post->meta, 'child_age_group')) === $ageGroup)>{{ $ageGroup }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="childGradeLevel">Class / grade</label>
            <select name="child_grade_level" id="childGradeLevel" class="form-select">
                <option value="">Optional</option>
                @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerGradeLevels() as $gradeLevel)
                    <option value="{{ $gradeLevel }}" @selected(old('child_grade_level', data_get($post->meta, 'child_grade_level')) === $gradeLevel)>{{ $gradeLevel }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="childSchoolName">School name</label>
            <input
                type="text"
                name="child_school_name"
                id="childSchoolName"
                class="form-control"
                value="{{ old('child_school_name', data_get($post->meta, 'child_school_name', data_get($post->meta, 'school_name'))) }}"
                maxlength="160"
                placeholder="Optional — helps schools participate"
            >
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Parent / guardian details</h5>
            <p class="text-muted mb-0 small">Parent mobile is kept private and not shown publicly.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="parentName">Parent name <span class="text-danger">*</span></label>
            <input type="text" name="parent_name" id="parentName" class="form-control childrens-corner-required" maxlength="120" value="{{ old('parent_name', data_get($post->meta, 'parent_name')) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="parentMobile">Parent mobile number <span class="text-danger">*</span></label>
            <input type="tel" name="parent_mobile" id="parentMobile" class="form-control childrens-corner-required" maxlength="20" value="{{ old('parent_mobile', data_get($post->meta, 'parent_mobile')) }}" placeholder="Not publicly displayed" required>
            <small class="text-muted">Not publicly displayed</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="parentEmail">Parent email</label>
            <input type="email" name="parent_email" id="parentEmail" class="form-control" maxlength="160" value="{{ old('parent_email', data_get($post->meta, 'parent_email')) }}" placeholder="Optional">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="parentRelationship">Relationship <span class="text-danger">*</span></label>
            <select name="parent_relationship" id="parentRelationship" class="form-select childrens-corner-required" required>
                <option value="">Select relationship</option>
                @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerParentRelationships() as $relationship)
                    <option value="{{ $relationship }}" @selected(old('parent_relationship', data_get($post->meta, 'parent_relationship')) === $relationship)>{{ $relationship }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Parent consent</h5>
            <p class="text-muted mb-0 small">All three confirmations are required before submission.</p>
        </div>
        <span class="badge bg-danger text-white">Mandatory</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-light mb-0">
            <input type="checkbox" name="child_parent_consent_identity" id="childParentConsentIdentity" class="form-check-input childrens-corner-consent-required" value="1" @checked(old('child_parent_consent_identity', data_get($post->meta, 'child_parent_consent_identity', false))) required>
            <span class="form-check-label">I am the parent/guardian of the child.</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0">
            <input type="checkbox" name="child_parent_consent_publication" id="childParentConsentPublication" class="form-check-input childrens-corner-consent-required" value="1" @checked(old('child_parent_consent_publication', data_get($post->meta, 'child_parent_consent_publication', false))) required>
            <span class="form-check-label">I consent to the publication of this content on SoilnWater.</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0">
            <input type="checkbox" name="child_parent_consent_original" id="childParentConsentOriginal" class="form-check-input childrens-corner-consent-required" value="1" @checked(old('child_parent_consent_original', data_get($post->meta, 'child_parent_consent_original', false))) required>
            <span class="form-check-label">I confirm that the submission is original and safe for publication.</span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--theme border rounded-3 p-3 p-md-4 bg-light mb-3 community-flow-checklist">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Theme</h5>
            <p class="text-muted mb-0 small">Select all themes that fit this submission.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    @php
        $selectedChildrensThemes = old('childrens_corner_themes', data_get($post->meta, 'childrens_corner_themes', []));
    @endphp
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerThemes() as $theme)
            <div class="col-md-6 col-lg-4">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="childrens_corner_themes[]"
                        value="{{ $theme }}"
                        class="form-check-input"
                        @checked(in_array($theme, (array) $selectedChildrensThemes, true))
                    >
                    <span class="form-check-label">{{ $theme }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Talent category</h5>
            <p class="text-muted mb-0 small">Useful for competitions and talent showcases.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    @php
        $selectedTalentCategories = old('childrens_corner_talent_categories', data_get($post->meta, 'childrens_corner_talent_categories', []));
    @endphp
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerTalentCategories() as $category)
            <div class="col-md-6 col-lg-4">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input type="checkbox" name="childrens_corner_talent_categories[]" value="{{ $category }}" class="form-check-input" @checked(in_array($category, (array) $selectedTalentCategories, true))>
                    <span class="form-check-label">{{ $category }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Achievement</h5>
            <p class="text-muted mb-0 small">Optional recognition or competition results.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="childrensCornerAchievement">Achievements</label>
    <textarea
        name="childrens_corner_achievement"
        id="childrensCornerAchievement"
        class="form-control"
        rows="3"
        maxlength="1000"
        placeholder="e.g. School Winner, District Winner, Science Fair Participant, Art Competition Winner"
    >{{ old('childrens_corner_achievement', data_get($post->meta, 'childrens_corner_achievement')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">School participation</h5>
            <p class="text-muted mb-0 small">How this submission was shared with SoilnWater.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="childrensCornerSubmittedThrough">Submitted through</label>
            <select name="childrens_corner_submitted_through" id="childrensCornerSubmittedThrough" class="form-select">
                <option value="">Optional</option>
                @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerSubmittedThroughOptions() as $submittedThrough)
                    <option value="{{ $submittedThrough }}" @selected(old('childrens_corner_submitted_through', data_get($post->meta, 'childrens_corner_submitted_through')) === $submittedThrough)>{{ $submittedThrough }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="childrensCornerSchoolCompetitionEntry">School competition entry</label>
            <select name="childrens_corner_school_competition_entry" id="childrensCornerSchoolCompetitionEntry" class="form-select">
                <option value="">Optional</option>
                <option value="Yes" @selected(old('childrens_corner_school_competition_entry', data_get($post->meta, 'childrens_corner_school_competition_entry')) === 'Yes')>Yes</option>
                <option value="No" @selected(old('childrens_corner_school_competition_entry', data_get($post->meta, 'childrens_corner_school_competition_entry')) === 'No')>No</option>
            </select>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Only broad location is shown publicly — city, district, and state. Do not enter an exact address.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="childrensCornerCity">City</label>
            <input type="text" name="childrens_corner_city" id="childrensCornerCity" class="form-control" maxlength="120" value="{{ old('childrens_corner_city', data_get($post->meta, 'childrens_corner_city')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="childrensCornerDistrict">District</label>
            <input type="text" name="childrens_corner_district" id="childrensCornerDistrict" class="form-control" maxlength="120" value="{{ old('childrens_corner_district', data_get($post->meta, 'childrens_corner_district')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="childrensCornerState">State</label>
            <input type="text" name="childrens_corner_state" id="childrensCornerState" class="form-control" maxlength="120" value="{{ old('childrens_corner_state', data_get($post->meta, 'childrens_corner_state')) }}">
        </div>
    </div>
</div>

<div id="childrensCornerFeaturedPanel" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Optional cover image for stories and essays.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div id="communityChildrensCornerFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Gallery</h5>
            <p class="text-muted mb-0 small">Upload multiple photos — useful for project photos, drawings, experiments, and craft work.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <input type="file" name="childrens_corner_gallery[]" id="childrensCornerGalleryInput" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif" multiple>
    <small class="text-muted d-block mt-1">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
    @php
        $existingChildrensGallery = array_values((array) data_get($post->meta, 'childrens_corner_gallery', []));
    @endphp
    @if($existingChildrensGallery !== [])
        <div class="mt-3 community-flow-stack d-flex flex-column gap-2">
            @foreach($existingChildrensGallery as $galleryImage)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_childrens_corner_gallery[]" value="{{ data_get($galleryImage, 'path') }}" class="form-check-input">
                    <span class="form-check-label">Remove {{ data_get($galleryImage, 'name', 'gallery image') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video submission</h5>
            <p class="text-muted mb-0 small">Optional — poetry recitation, speech, talent show, science demonstration, dance, music, and more.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="community-video-field border rounded-3 p-3 bg-light">
        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Children's Corner video source type">
            <input type="radio" class="btn-check" name="childrens_corner_video_source_type" id="childrensCornerVideoSourceNone" value="none" @checked($childrensVideoSourceType === 'none')>
            <label class="btn btn-outline-secondary" for="childrensCornerVideoSourceNone">No video</label>
            <input type="radio" class="btn-check" name="childrens_corner_video_source_type" id="childrensCornerVideoSourceYoutube" value="youtube" @checked($childrensVideoSourceType === 'youtube')>
            <label class="btn btn-outline-secondary" for="childrensCornerVideoSourceYoutube">YouTube link</label>
            <input type="radio" class="btn-check" name="childrens_corner_video_source_type" id="childrensCornerVideoSourceUpload" value="upload" @checked($childrensVideoSourceType === 'upload')>
            <label class="btn btn-outline-secondary" for="childrensCornerVideoSourceUpload">Upload file</label>
        </div>
        <div id="childrensCornerVideoYoutubeWrap" class="video-source-panel">
            <label class="form-label" for="childrensCornerVideoYoutubeUrl">YouTube URL</label>
            <input type="url" name="childrens_corner_video_youtube_url" id="childrensCornerVideoYoutubeUrl" class="form-control" value="{{ old('childrens_corner_video_youtube_url', ($existingChildrensVideo['type'] ?? null) === 'youtube' ? ($existingChildrensVideo['url'] ?? '') : '') }}" placeholder="https://www.youtube.com/watch?v=..." maxlength="500">
        </div>
        <div id="childrensCornerVideoUploadWrap" class="video-source-panel">
            <label class="form-label" for="childrensCornerVideoFile">Video file</label>
            <input type="file" name="childrens_corner_video_file" id="childrensCornerVideoFile" class="form-control" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska,.mp4,.mov,.avi,.webm,.mkv">
            <small class="text-muted d-block mt-2">MP4, MOV, AVI, WebM, or MKV. Maximum size: 50 MB.</small>
            @if(($existingChildrensVideo['type'] ?? null) === 'upload')
                <input type="hidden" name="keep_existing_childrens_corner_video" id="keepExistingChildrensCornerVideo" value="1">
                <div id="existingChildrensCornerVideoPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="small"><strong>Current video:</strong> {{ $existingChildrensVideo['name'] ?? 'Uploaded video' }}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeExistingChildrensCornerVideoBtn">Remove</button>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio submission</h5>
            <p class="text-muted mb-0 small">Optional — story reading, poem recitation, speech, and more.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="border rounded-3 p-3 bg-white">
        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Children's Corner audio source type">
            <input type="radio" class="btn-check" name="childrens_corner_audio_source_type" id="childrensCornerAudioSourceNone" value="none" @checked($childrensAudioSourceType === 'none')>
            <label class="btn btn-outline-secondary" for="childrensCornerAudioSourceNone">No audio</label>
            <input type="radio" class="btn-check" name="childrens_corner_audio_source_type" id="childrensCornerAudioSourceUpload" value="upload" @checked($childrensAudioSourceType === 'upload')>
            <label class="btn btn-outline-secondary" for="childrensCornerAudioSourceUpload">Upload MP3</label>
            <input type="radio" class="btn-check" name="childrens_corner_audio_source_type" id="childrensCornerAudioSourceRecording" value="recording" @checked($childrensAudioSourceType === 'recording')>
            <label class="btn btn-outline-secondary" for="childrensCornerAudioSourceRecording">Record voice</label>
        </div>
        <div id="childrensCornerAudioUploadWrap" class="story-audio-panel">
            <input type="file" name="childrens_corner_audio_file" id="childrensCornerAudioFile" class="form-control" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
            <small class="text-muted d-block mt-2">MP3, WAV, WebM, or OGG. Maximum size: 20 MB.</small>
        </div>
        <div id="childrensCornerAudioRecordingWrap" class="story-audio-panel">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="childrensCornerAudioRecordBtn"><i class="fa-solid fa-microphone me-1"></i>Start recording</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="childrensCornerAudioStopBtn" disabled>Stop</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="childrensCornerAudioClearRecordingBtn" disabled>Clear recording</button>
            </div>
            <p class="small text-muted mb-2" id="childrensCornerAudioRecordingStatus">Ready to record.</p>
            <audio id="childrensCornerAudioRecordingPreview" controls class="w-100" style="display:none;"></audio>
        </div>
        @if(filled($existingChildrensAudio))
            <input type="hidden" name="keep_existing_childrens_corner_audio" id="keepExistingChildrensCornerAudio" value="1">
            <div id="existingChildrensCornerAudioPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
                <div class="small">
                    <strong>Current audio:</strong> {{ $existingChildrensAudio['name'] ?? 'Audio submission' }}
                    @if(filled($existingChildrensAudio['url'] ?? null))
                        <audio controls class="d-block mt-2 w-100" src="{{ $existingChildrensAudio['url'] }}"></audio>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger align-self-start" id="removeExistingChildrensCornerAudioBtn">Remove</button>
            </div>
        @endif
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Certificate upload</h5>
            <p class="text-muted mb-0 small">Optional — upload an award or participation certificate.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="childrensCornerCertificateFile">Upload certificate</label>
    <input type="file" name="childrens_corner_certificate_file" id="childrensCornerCertificateFile" class="form-control" accept="application/pdf,image/jpeg,image/png,image/webp,.pdf,.jpg,.jpeg,.png,.webp">
    <small class="text-muted d-block mt-1">PDF or image (JPG, PNG, WEBP). Max 4 MB.</small>
    @if(filled($existingChildrensCertificate))
        <input type="hidden" name="keep_existing_childrens_corner_certificate" id="keepExistingChildrensCornerCertificate" value="1">
        <div id="existingChildrensCornerCertificatePreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
            <div class="small">
                <strong>Current certificate:</strong> {{ $existingChildrensCertificate['name'] ?? 'Uploaded certificate' }}
                @if(filled($existingChildrensCertificate['url'] ?? null) && str_starts_with((string) ($existingChildrensCertificate['type'] ?? ''), 'image/'))
                    <img src="{{ $existingChildrensCertificate['url'] }}" alt="Certificate preview" class="d-block mt-2 rounded border" style="max-height:160px;">
                @elseif(filled($existingChildrensCertificate['url'] ?? null))
                    <a href="{{ $existingChildrensCertificate['url'] }}" class="d-block mt-2" target="_blank" rel="noopener">View certificate</a>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger align-self-start" id="removeExistingChildrensCornerCertificateBtn">Remove</button>
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Privacy settings</h5>
            <p class="text-muted mb-0 small">Control who can view this Children's Corner submission and how much child information is shown publicly.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    @php
        $selectedPrivacy = old(
            'childrens_corner_privacy_setting',
            data_get($post->meta, 'childrens_corner_privacy_setting', \App\Support\CommunityContentTaxonomy::childrensCornerDefaultPrivacySetting())
        );
    @endphp
    <div class="community-flow-stack d-flex flex-column gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerPrivacySettings() as $value => $label)
            <label class="form-check border rounded p-3 mb-0 {{ $value === 'public_limited' ? 'bg-success-subtle border-success' : 'bg-light' }}">
                <input
                    type="radio"
                    name="childrens_corner_privacy_setting"
                    class="form-check-input"
                    value="{{ $value }}"
                    @checked($selectedPrivacy === $value)
                    required
                >
                <span class="form-check-label">
                    <strong>{{ $label }}</strong>
                    @if($value === 'public_limited')
                        <span class="badge bg-success ms-1">Recommended default</span>
                        <small class="text-muted d-block mt-1">Visible to everyone, but school name, grade, and broad location are hidden on the public page.</small>
                    @elseif($value === 'public')
                        <small class="text-muted d-block mt-1">Visible to everyone with the child details you chose to share (first name, age group, school, etc.).</small>
                    @elseif($value === 'registered_users')
                        <small class="text-muted d-block mt-1">Only logged-in SoilnWater members can open this post.</small>
                    @elseif($value === 'school_community')
                        <small class="text-muted d-block mt-1">Only logged-in members can view this post. School name is required for this option.</small>
                    @endif
                </span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Safety declaration</h5>
            <p class="text-muted mb-0 small">Confirm that this submission follows our children's safety guidelines.</p>
        </div>
        <span class="badge bg-primary text-white">Mandatory</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerSafetyDeclarations() as $field => $label)
            <label class="form-check border rounded p-3 bg-white mb-0">
                <input
                    type="checkbox"
                    name="{{ $field }}"
                    id="{{ $field }}"
                    class="form-check-input childrens-corner-safety-required"
                    value="1"
                    @checked(old($field, data_get($post->meta, $field, false)))
                    required
                >
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Highly recommended for children's submissions.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0">
            <input
                type="checkbox"
                class="form-check-input"
                id="childrensCornerEnableComments"
                @checked(old('allow_comments', $post->allow_comments ?? true))
            >
            <span class="form-check-label">Enable comments on this post</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0">
        <input
            type="checkbox"
            name="childrens_corner_comments_moderated"
            id="childrensCornerCommentsModerated"
            class="form-check-input"
            value="1"
            @checked(old('childrens_corner_comments_moderated', data_get($post->meta, 'childrens_corner_comments_moderated', true)))
        >
        <span class="form-check-label">Comments moderated <small class="text-muted">(default — new comments require your approval before they appear publicly)</small></span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Child-friendly reactions only — negative reactions are not available on Children's Corner posts.</p>
        </div>
        <span class="badge bg-success text-white">Child-friendly</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>

<div id="childrensCornerArtPanel" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Drawing / painting / photo</h5>
            <p class="text-muted mb-0 small">Upload your artwork. Formats: JPG, PNG, WEBP (max 4 MB).</p>
        </div>
        <span class="badge bg-secondary text-white">Image upload</span>
    </div>
    <label class="form-label" for="childrensCornerArtFile">Upload image <span class="text-danger">*</span></label>
    <input type="file" name="childrens_corner_art_file" id="childrensCornerArtFile" class="form-control" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
    @if(filled($existingArtImage))
        <input type="hidden" name="keep_existing_childrens_corner_art" id="keepExistingChildrensCornerArt" value="1">
        <div id="existingChildrensCornerArtPreview" class="alert alert-light border mt-3 mb-0 py-2 px-3 d-flex align-items-center justify-content-between gap-2">
            <div>
                <strong>Current artwork:</strong> {{ $existingArtImage['name'] ?? 'Uploaded image' }}
                @if(filled($existingArtImage['url'] ?? null))
                    <img src="{{ $existingArtImage['url'] }}" alt="Current artwork" class="d-block mt-2 rounded border" style="max-height:160px;">
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger align-self-start" id="removeExistingChildrensCornerArtBtn">Remove</button>
        </div>
    @endif
</div>

<div id="childrensCornerProjectPanel" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Project submission</h5>
            <p class="text-muted mb-0 small">Add a description and upload photos, PDF, or presentation files.</p>
        </div>
        <span class="badge bg-info text-dark">Projects</span>
    </div>
    <div class="mb-3">
        <label class="form-label" for="childrensCornerProjectDescription">Project description <span class="text-danger">*</span></label>
        <textarea
            name="childrens_corner_project_description"
            id="childrensCornerProjectDescription"
            class="form-control"
            rows="4"
            maxlength="5000"
            placeholder="Describe the project, materials used, and what you learned."
        >{{ old('childrens_corner_project_description', data_get($post->meta, 'childrens_corner_project_description')) }}</textarea>
    </div>
    <label class="form-label" for="childrensCornerProjectFiles">Upload files</label>
    <input type="file" name="childrens_corner_project_files[]" id="childrensCornerProjectFiles" class="form-control" multiple accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp,.pdf,.ppt,.pptx,.doc,.docx,application/pdf">
    <small class="text-muted d-block mt-1">Photos, PDF, or presentation files. Max 6 files, 20 MB each.</small>
    @if($existingProjectFiles !== [])
        <div class="mt-3">
            <strong class="small d-block mb-2">Existing project files</strong>
            <ul class="list-unstyled mb-0 small">
                @foreach($existingProjectFiles as $index => $file)
                    <li class="mb-1">
                        <label class="form-check">
                            <input type="hidden" name="existing_childrens_corner_project_files[{{ $index }}][path]" value="{{ data_get($file, 'path') }}">
                            <input type="checkbox" class="form-check-input" name="keep_childrens_corner_project_files[]" value="{{ data_get($file, 'path') }}" checked>
                            <span class="form-check-label">{{ data_get($file, 'name', 'Project file') }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<div id="childrensCornerQuizPanel" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Quiz / puzzle</h5>
            <p class="text-muted mb-0 small">Add questions with options and mark the correct answer.</p>
        </div>
        <span class="badge bg-warning text-dark">Quiz builder</span>
    </div>
    <div id="childrensCornerQuizEntries"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addChildrensCornerQuizBtn">
        <i class="fa-solid fa-plus me-1"></i>Add question
    </button>
</div>

<template id="childrensCornerQuizTemplate">
    <div class="childrens-corner-quiz-entry border rounded-3 p-3 bg-white mb-3" data-quiz-index="">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h6 class="mb-0 childrens-corner-quiz-entry__title">Question</h6>
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-childrens-quiz-entry">Remove</button>
        </div>
        <div class="mb-3">
            <label class="form-label">Question <span class="text-danger">*</span></label>
            <input type="text" class="form-control js-quiz-question" maxlength="500" placeholder="Enter the question" data-name="childrens_corner_quiz[__INDEX__][question]">
        </div>
        <div class="mb-3">
            <label class="form-label">Options <span class="text-danger">*</span></label>
            <div class="js-quiz-options">
                <input type="text" class="form-control mb-2 js-quiz-option" maxlength="255" placeholder="Option 1" data-name="childrens_corner_quiz[__INDEX__][options][0]">
                <input type="text" class="form-control mb-2 js-quiz-option" maxlength="255" placeholder="Option 2" data-name="childrens_corner_quiz[__INDEX__][options][1]">
                <input type="text" class="form-control js-quiz-option" maxlength="255" placeholder="Option 3" data-name="childrens_corner_quiz[__INDEX__][options][2]">
            </div>
        </div>
        <div>
            <label class="form-label">Correct answer <span class="text-danger">*</span></label>
            <input type="text" class="form-control js-quiz-correct-answer" maxlength="255" placeholder="Must match one of the options exactly" data-name="childrens_corner_quiz[__INDEX__][correct_answer]">
        </div>
    </div>
</template>

<script>
    window.communityChildrensQuiz = @json($communityChildrensQuizForJs);
    window.communityChildrensCornerShareModes = @json(\App\Support\CommunityContentTaxonomy::childrensCornerShareTypesByContentMode());
    window.communityChildrensCornerFeaturedShareTypes = @json(\App\Support\CommunityContentTaxonomy::childrensCornerShareTypesWithFeaturedImage());
    window.childrensCornerAudioBlob = null;

    (function initChildrensCornerMediaFields() {
        const maxVideoFileBytes = 52428800;
        const maxAudioFileBytes = 20971520;
        const videoYoutubeWrap = document.getElementById('childrensCornerVideoYoutubeWrap');
        const videoUploadWrap = document.getElementById('childrensCornerVideoUploadWrap');
        const videoFileInput = document.getElementById('childrensCornerVideoFile');
        const keepExistingVideoInput = document.getElementById('keepExistingChildrensCornerVideo');
        const removeExistingVideoBtn = document.getElementById('removeExistingChildrensCornerVideoBtn');
        const audioUploadWrap = document.getElementById('childrensCornerAudioUploadWrap');
        const audioRecordingWrap = document.getElementById('childrensCornerAudioRecordingWrap');
        const audioFileInput = document.getElementById('childrensCornerAudioFile');
        const keepExistingAudioInput = document.getElementById('keepExistingChildrensCornerAudio');
        const removeExistingAudioBtn = document.getElementById('removeExistingChildrensCornerAudioBtn');
        const audioRecordBtn = document.getElementById('childrensCornerAudioRecordBtn');
        const audioStopBtn = document.getElementById('childrensCornerAudioStopBtn');
        const audioClearBtn = document.getElementById('childrensCornerAudioClearRecordingBtn');
        const audioStatus = document.getElementById('childrensCornerAudioRecordingStatus');
        const audioPreview = document.getElementById('childrensCornerAudioRecordingPreview');
        let audioRecorder = null;
        let audioStream = null;
        let audioChunks = [];

        function refreshChildrensCornerVideoPanels() {
            const selected = document.querySelector('input[name="childrens_corner_video_source_type"]:checked')?.value || 'none';
            videoYoutubeWrap?.classList.toggle('is-active', selected === 'youtube');
            videoUploadWrap?.classList.toggle('is-active', selected === 'upload');
        }

        function refreshChildrensCornerAudioPanels() {
            const selected = document.querySelector('input[name="childrens_corner_audio_source_type"]:checked')?.value || 'none';
            audioUploadWrap?.classList.toggle('is-active', selected === 'upload');
            audioRecordingWrap?.classList.toggle('is-active', selected === 'recording');
        }

        document.querySelectorAll('input[name="childrens_corner_video_source_type"]').forEach((input) => {
            input.addEventListener('change', refreshChildrensCornerVideoPanels);
        });

        document.querySelectorAll('input[name="childrens_corner_audio_source_type"]').forEach((input) => {
            input.addEventListener('change', refreshChildrensCornerAudioPanels);
        });

        removeExistingVideoBtn?.addEventListener('click', function () {
            document.getElementById('childrensCornerVideoSourceNone')?.click();
            document.getElementById('existingChildrensCornerVideoPreview')?.remove();
            keepExistingVideoInput?.remove();
            let removeInput = document.getElementById('removeChildrensCornerVideoInput');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_childrens_corner_video';
                removeInput.id = 'removeChildrensCornerVideoInput';
                removeInput.value = '1';
                document.getElementById('community-post-form')?.appendChild(removeInput);
            }
            if (videoFileInput) {
                videoFileInput.value = '';
            }
        });

        removeExistingAudioBtn?.addEventListener('click', function () {
            document.getElementById('childrensCornerAudioSourceNone')?.click();
            document.getElementById('existingChildrensCornerAudioPreview')?.remove();
            keepExistingAudioInput?.remove();
            let removeInput = document.getElementById('removeChildrensCornerAudioInput');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_childrens_corner_audio';
                removeInput.id = 'removeChildrensCornerAudioInput';
                removeInput.value = '1';
                document.getElementById('community-post-form')?.appendChild(removeInput);
            }
            if (audioFileInput) {
                audioFileInput.value = '';
            }
        });

        videoFileInput?.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) {
                return;
            }
            if (file.size > maxVideoFileBytes) {
                window.toastr?.error?.('Video file must be 50 MB or smaller.') || alert('Video file must be 50 MB or smaller.');
                this.value = '';
                return;
            }
            if (keepExistingVideoInput) {
                keepExistingVideoInput.value = '0';
            }
        });

        audioFileInput?.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) {
                return;
            }
            if (file.size > maxAudioFileBytes) {
                window.toastr?.error?.('Audio file must be 20 MB or smaller.') || alert('Audio file must be 20 MB or smaller.');
                this.value = '';
                return;
            }
            if (keepExistingAudioInput) {
                keepExistingAudioInput.value = '0';
            }
        });

        function resetChildrensCornerAudioRecordingUi() {
            if (audioStream) {
                audioStream.getTracks().forEach((track) => track.stop());
                audioStream = null;
            }
            audioRecorder = null;
            audioChunks = [];
            window.childrensCornerAudioBlob = null;
            if (audioPreview) {
                audioPreview.removeAttribute('src');
                audioPreview.style.display = 'none';
                audioPreview.load();
            }
            if (audioRecordBtn) {
                audioRecordBtn.disabled = false;
            }
            if (audioStopBtn) {
                audioStopBtn.disabled = true;
            }
            if (audioClearBtn) {
                audioClearBtn.disabled = true;
            }
            if (audioStatus) {
                audioStatus.textContent = 'Ready to record.';
            }
        }

        audioRecordBtn?.addEventListener('click', async function () {
            if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
                window.toastr?.error?.('Voice recording is not supported in this browser.') || alert('Voice recording is not supported in this browser.');
                return;
            }
            try {
                resetChildrensCornerAudioRecordingUi();
                audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                audioChunks = [];
                audioRecorder = new MediaRecorder(audioStream);
                audioRecorder.addEventListener('dataavailable', function (event) {
                    if (event.data && event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                });
                audioRecorder.addEventListener('stop', function () {
                    window.childrensCornerAudioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    if (audioPreview) {
                        audioPreview.src = URL.createObjectURL(window.childrensCornerAudioBlob);
                        audioPreview.style.display = '';
                    }
                    if (audioStatus) {
                        audioStatus.textContent = 'Recording saved. Submit the form to upload it.';
                    }
                    if (audioClearBtn) {
                        audioClearBtn.disabled = false;
                    }
                });
                audioRecorder.start();
                if (audioStatus) {
                    audioStatus.textContent = 'Recording...';
                }
                audioRecordBtn.disabled = true;
                audioStopBtn.disabled = false;
            } catch (error) {
                window.toastr?.error?.('Unable to access microphone.') || alert('Unable to access microphone.');
            }
        });

        audioStopBtn?.addEventListener('click', function () {
            if (audioRecorder && audioRecorder.state !== 'inactive') {
                audioRecorder.stop();
            }
            if (audioStream) {
                audioStream.getTracks().forEach((track) => track.stop());
                audioStream = null;
            }
            audioRecordBtn.disabled = false;
            audioStopBtn.disabled = true;
        });

        audioClearBtn?.addEventListener('click', function () {
            resetChildrensCornerAudioRecordingUi();
        });

        document.getElementById('removeExistingChildrensCornerCertificateBtn')?.addEventListener('click', function () {
            document.getElementById('keepExistingChildrensCornerCertificate')?.remove();
            document.getElementById('existingChildrensCornerCertificatePreview')?.remove();
            let removeInput = document.getElementById('removeChildrensCornerCertificateInput');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_childrens_corner_certificate';
                removeInput.id = 'removeChildrensCornerCertificateInput';
                removeInput.value = '1';
                document.getElementById('community-post-form')?.appendChild(removeInput);
            }
        });

        document.getElementById('childrensCornerEnableComments')?.addEventListener('change', function () {
            const allowComments = document.getElementById('allowComments');
            if (allowComments) {
                allowComments.checked = this.checked;
            }
        });

        document.querySelectorAll('input[name="childrens_corner_privacy_setting"]').forEach((radio) => {
            radio.addEventListener('change', function () {
                const childSchoolName = document.getElementById('childSchoolName');
                if (childSchoolName) {
                    childSchoolName.required = this.value === 'school_community';
                }
            });
        });

        refreshChildrensCornerVideoPanels();
        refreshChildrensCornerAudioPanels();
    })();
</script>
