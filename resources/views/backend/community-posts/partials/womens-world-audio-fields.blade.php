@php
    $existingWomensWorldAudio = data_get($post->meta, 'womens_world_audio');
    $womensWorldAudioSourceType = old('womens_world_audio_source_type', filled($existingWomensWorldAudio) ? (($existingWomensWorldAudio['type'] ?? '') === 'recording' ? 'recording' : 'upload') : 'none');
@endphp

<div class="news-flow-card story-flow-card story-flow-card--audio border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio message</h5>
            <p class="text-muted mb-0 small">Optional voice message — upload MP3 or record directly in the browser.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <p class="small text-muted mb-1">Useful for:</p>
    <ul class="small text-muted mb-3 ps-3">
        <li>Life Experiences, Poetry</li>
        <li>Motivational Messages, Interviews</li>
    </ul>
    <div class="community-audio-field border rounded-3 p-3 bg-white">
        <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Women's World audio source type">
            <input type="radio" class="btn-check" name="womens_world_audio_source_type" id="womensWorldAudioSourceNone" value="none" @checked($womensWorldAudioSourceType === 'none')>
            <label class="btn btn-outline-secondary" for="womensWorldAudioSourceNone">No audio</label>
            <input type="radio" class="btn-check" name="womens_world_audio_source_type" id="womensWorldAudioSourceUpload" value="upload" @checked($womensWorldAudioSourceType === 'upload')>
            <label class="btn btn-outline-secondary" for="womensWorldAudioSourceUpload">MP3 upload</label>
            <input type="radio" class="btn-check" name="womens_world_audio_source_type" id="womensWorldAudioSourceRecording" value="recording" @checked($womensWorldAudioSourceType === 'recording')>
            <label class="btn btn-outline-secondary" for="womensWorldAudioSourceRecording">Voice recording</label>
        </div>

        <div id="womensWorldAudioUploadWrap" class="audio-source-panel">
            <label class="form-label" for="womensWorldAudioFile">Audio file</label>
            <input type="file" name="womens_world_audio_file" id="womensWorldAudioFile" class="form-control" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
            <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
        </div>

        <div id="womensWorldAudioRecordingWrap" class="audio-source-panel">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-danger" id="womensWorldAudioRecordBtn">
                    <i class="fa-solid fa-microphone me-1" aria-hidden="true"></i>Record
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="womensWorldAudioStopBtn" disabled>Stop</button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="womensWorldAudioClearRecordingBtn" disabled>Clear</button>
                <span class="small text-muted" id="womensWorldAudioRecordingStatus">Ready to record.</span>
            </div>
            <audio id="womensWorldAudioRecordingPreview" controls class="w-100" style="display:none;"></audio>
        </div>

        @if(filled($existingWomensWorldAudio))
            <div class="mt-3 border rounded p-3 bg-light" id="existingWomensWorldAudioWrap">
                <p class="small mb-2 fw-semibold">Current audio message</p>
                <audio controls class="w-100" src="{{ data_get($existingWomensWorldAudio, 'url') }}"></audio>
                <div class="form-check mt-2">
                    <input type="checkbox" name="remove_womens_world_audio" value="1" class="form-check-input" id="removeWomensWorldAudio">
                    <label class="form-check-label" for="removeWomensWorldAudio">Remove existing audio</label>
                </div>
            </div>
            <input type="hidden" name="keep_existing_womens_world_audio" id="keepExistingWomensWorldAudio" value="1">
        @endif
    </div>
</div>
