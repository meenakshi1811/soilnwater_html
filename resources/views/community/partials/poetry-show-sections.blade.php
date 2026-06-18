@if($post->content_type === 'poetry')
    @php
        $hasClassification = filled($post->category)
            || filled(data_get($post->meta, 'sub_category'))
            || filled(data_get($post->meta, 'poetry_type'))
            || filled(data_get($post->meta, 'poem_language'))
            || filled(data_get($post->meta, 'reading_time'));
        $isSeries = data_get($post->meta, 'poetry_part_of_series') === 'Yes'
            && filled(data_get($post->meta, 'poetry_series_name'));
    @endphp

    @if($hasClassification)
        <div class="poetry-classification-strip mb-4">
            <div class="d-flex flex-wrap gap-4">
                @if(filled($post->category))
                    <div class="poetry-classification-strip__item">
                        <span class="poetry-classification-strip__label">Main category</span>
                        <span class="poetry-classification-strip__value">{{ $post->category }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'sub_category')))
                    <div class="poetry-classification-strip__item">
                        <span class="poetry-classification-strip__label">Sub category</span>
                        <span class="poetry-classification-strip__value">{{ data_get($post->meta, 'sub_category') }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'poetry_type')))
                    <div class="poetry-classification-strip__item">
                        <span class="poetry-classification-strip__label">Poetry type</span>
                        <span class="poetry-classification-strip__value">{{ data_get($post->meta, 'poetry_type') }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'poem_language')))
                    <div class="poetry-classification-strip__item">
                        <span class="poetry-classification-strip__label">Language</span>
                        <span class="poetry-classification-strip__value">{{ data_get($post->meta, 'poem_language') }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'reading_time')))
                    <div class="poetry-classification-strip__item">
                        <span class="poetry-classification-strip__label">Reading time</span>
                        <span class="poetry-classification-strip__value">{{ data_get($post->meta, 'reading_time') }} min</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($isSeries)
        <div class="poetry-series-banner mb-4">
            <div class="poetry-series-banner__kicker mb-1">Poetry collection</div>
            <h4 class="h5 mb-1">{{ data_get($post->meta, 'poetry_series_name') }}</h4>
            @if(filled(data_get($post->meta, 'poetry_series_part')))
                <p class="text-muted mb-0">{{ data_get($post->meta, 'poetry_series_part') }}</p>
            @endif
        </div>
    @endif

    @include('community.partials.poetry-author-panel', ['post' => $post])

    @if($post->poetryAudioUrl())
        <div class="poetry-audio-player about-box mb-4">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                <div>
                    <h4 class="mb-1">Audio recitation</h4>
                    <p class="text-muted small mb-0">
                        {{ data_get($post->poetryAudioData(), 'type') === 'recording' ? 'Voice recording' : 'Uploaded MP3' }}
                        @if(filled(data_get($post->poetryAudioData(), 'name')))
                            — {{ data_get($post->poetryAudioData(), 'name') }}
                        @endif
                    </p>
                </div>
                <span class="badge bg-info text-white">Listen</span>
            </div>
            <audio controls class="w-100" preload="metadata" src="{{ $post->poetryAudioUrl() }}">
                Your browser does not support embedded audio playback.
            </audio>
        </div>
    @endif
@endif
