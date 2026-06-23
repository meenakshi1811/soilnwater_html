@php
    $existingSeniorCitizensForumAudio = data_get($post->meta, 'senior_citizens_forum_audio');
    $seniorCitizensForumAudioSourceType = old('senior_citizens_forum_audio_source_type', filled($existingSeniorCitizensForumAudio) ? (($existingSeniorCitizensForumAudio['type'] ?? '') === 'recording' ? 'recording' : 'upload') : 'none');
@endphp

<div class="news-flow-card story-flow-card story-flow-card--audio border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio memories</h5>
            <p class="text-muted mb-0 small">Many senior citizens are more comfortable speaking than typing. Upload MP3 or record your voice directly in the browser.</p>
        </div>
        <span class="badge bg-info text-white">Highly recommended</span>
    </div>
    <p class="small text-muted mb-1">Examples:</p>
    <ul class="small text-muted mb-3 ps-3">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumAudioMemoryExamples() as $example)
            <li>{{ $example }}</li>
        @endforeach
    </ul>
    <div class="community-audio-field border rounded-3 p-3 bg-white">
        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Senior Citizens Forum audio source type">
            <input type="radio" class="btn-check" name="senior_citizens_forum_audio_source_type" id="seniorCitizensForumAudioSourceNone" value="none" @checked($seniorCitizensForumAudioSourceType === 'none')>
            <label class="btn btn-outline-secondary" for="seniorCitizensForumAudioSourceNone">No audio</label>
            <input type="radio" class="btn-check" name="senior_citizens_forum_audio_source_type" id="seniorCitizensForumAudioSourceUpload" value="upload" @checked($seniorCitizensForumAudioSourceType === 'upload')>
            <label class="btn btn-outline-secondary" for="seniorCitizensForumAudioSourceUpload">MP3 upload</label>
            <input type="radio" class="btn-check" name="senior_citizens_forum_audio_source_type" id="seniorCitizensForumAudioSourceRecording" value="recording" @checked($seniorCitizensForumAudioSourceType === 'recording')>
            <label class="btn btn-outline-secondary" for="seniorCitizensForumAudioSourceRecording">Voice recording</label>
        </div>

        <div id="seniorCitizensForumAudioUploadWrap" class="audio-source-panel">
            <label class="form-label" for="seniorCitizensForumAudioFile">Audio file</label>
            <input type="file" name="senior_citizens_forum_audio_file" id="seniorCitizensForumAudioFile" class="form-control" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
            <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
        </div>

        <div id="seniorCitizensForumAudioRecordingWrap" class="audio-source-panel">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-danger" id="seniorCitizensForumAudioRecordBtn">
                    <i class="fa-solid fa-microphone me-1" aria-hidden="true"></i>Record
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="seniorCitizensForumAudioStopBtn" disabled>Stop</button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="seniorCitizensForumAudioClearRecordingBtn" disabled>Clear</button>
                <span class="small text-muted" id="seniorCitizensForumAudioRecordingStatus">Ready to record.</span>
            </div>
            <audio id="seniorCitizensForumAudioRecordingPreview" controls class="w-100" style="display:none;"></audio>
        </div>

        @if(filled($existingSeniorCitizensForumAudio))
            <div class="mt-3 border rounded p-3 bg-light" id="existingSeniorCitizensForumAudioWrap">
                <p class="small mb-2 fw-semibold">Current audio memory</p>
                <audio controls class="w-100" src="{{ data_get($existingSeniorCitizensForumAudio, 'url') }}"></audio>
                <div class="form-check mt-2">
                    <input type="checkbox" name="remove_senior_citizens_forum_audio" value="1" class="form-check-input" id="removeSeniorCitizensForumAudio">
                    <label class="form-check-label" for="removeSeniorCitizensForumAudio">Remove existing audio</label>
                </div>
            </div>
            <input type="hidden" name="keep_existing_senior_citizens_forum_audio" id="keepExistingSeniorCitizensForumAudio" value="1">
        @endif
    </div>
</div>
