@if($post->content_type === 'autobiography')
    @php
        $birthPlace = data_get($post->meta, 'birth_place');
        $currentLocation = data_get($post->meta, 'current_location');
        $placesMentioned = array_values(array_filter((array) data_get($post->meta, 'places_mentioned', [])));
        $keyLessons = array_values(array_filter((array) data_get($post->meta, 'key_lessons_learned', [])));
        $lifeTimeline = array_values((array) data_get($post->meta, 'life_timeline', []));
        $chapterCount = count($post->bookPages());
        $hasLocationDetails = filled($birthPlace) || filled($currentLocation) || $placesMentioned !== [];
    @endphp

    @if(filled(data_get($post->meta, 'autobiography_type')) || filled($post->category))
        <div class="autobiography-hero-strip mb-4">
            @if(filled(data_get($post->meta, 'autobiography_type')))
                <div class="autobiography-hero-strip__item">
                    <span class="autobiography-hero-strip__label">Autobiography type</span>
                    <span class="autobiography-hero-strip__value">{{ data_get($post->meta, 'autobiography_type') }}</span>
                </div>
            @endif
            @if(filled($post->category))
                <div class="autobiography-hero-strip__item">
                    <span class="autobiography-hero-strip__label">Journey</span>
                    <span class="autobiography-hero-strip__value">{{ $post->category }}</span>
                </div>
            @endif
            @if($chapterCount > 0)
                <div class="autobiography-hero-strip__item">
                    <span class="autobiography-hero-strip__label">Chapters</span>
                    <span class="autobiography-hero-strip__value">{{ $chapterCount }}</span>
                </div>
            @endif
        </div>
    @endif

    @include('community.partials.autobiography-author-panel', ['post' => $post])

    @if($hasLocationDetails)
        <div class="autobiography-section-panel about-box mb-4">
            <div class="autobiography-section-panel__header">
                <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                <h4 class="mb-0">Location details</h4>
            </div>
            <div class="row g-3">
                @if(filled($birthPlace))
                    <div class="col-md-6">
                        <div class="autobiography-meta-item">
                            <span class="autobiography-meta-item__label">Birth place</span>
                            <span class="autobiography-meta-item__value">{{ $birthPlace }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($currentLocation))
                    <div class="col-md-6">
                        <div class="autobiography-meta-item">
                            <span class="autobiography-meta-item__label">Current location</span>
                            <span class="autobiography-meta-item__value">{{ $currentLocation }}</span>
                        </div>
                    </div>
                @endif
            </div>
            @if($placesMentioned !== [])
                <div class="mt-3">
                    <span class="autobiography-meta-item__label d-block mb-2">Places mentioned</span>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($placesMentioned as $place)
                            <span class="badge autobiography-place-pill">{{ $place }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($lifeTimeline !== [])
        <div class="autobiography-section-panel about-box mb-4">
            <div class="autobiography-section-panel__header">
                <i class="fa-solid fa-timeline" aria-hidden="true"></i>
                <h4 class="mb-0">Life timeline</h4>
            </div>
            <div class="autobiography-timeline">
                @foreach($lifeTimeline as $entry)
                    <div class="autobiography-timeline__item">
                        <div class="autobiography-timeline__marker" aria-hidden="true"></div>
                        <div class="autobiography-timeline__body">
                            <div class="autobiography-timeline__year">{{ data_get($entry, 'year') }}</div>
                            <h5 class="h6 mb-1">{{ data_get($entry, 'title') }}</h5>
                            @if(filled(data_get($entry, 'description')))
                                <p class="text-muted mb-2">{{ data_get($entry, 'description') }}</p>
                            @endif
                            @if(filled(data_get($entry, 'photo.url')))
                                <img src="{{ data_get($entry, 'photo.url') }}" alt="{{ data_get($entry, 'title') }}" class="rounded border autobiography-timeline__photo">
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->autobiographyAudioUrl())
        <div class="autobiography-section-panel autobiography-audio-player about-box mb-4">
            <div class="autobiography-section-panel__header mb-0">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-microphone-lines" aria-hidden="true"></i>
                        <h4 class="mb-0">Audio memories</h4>
                    </div>
                    <p class="text-muted small mb-0 mt-2">
                        {{ data_get($post->autobiographyAudioData(), 'type') === 'recording' ? 'Voice recording' : 'Uploaded MP3' }}
                        @if(filled(data_get($post->autobiographyAudioData(), 'name')))
                            — {{ data_get($post->autobiographyAudioData(), 'name') }}
                        @endif
                    </p>
                </div>
                <span class="badge autobiography-audio-badge">Listen</span>
            </div>
            <audio controls class="w-100 mt-3" preload="metadata" src="{{ $post->autobiographyAudioUrl() }}">
                Your browser does not support embedded audio playback.
            </audio>
        </div>
    @endif

    @if($keyLessons !== [])
        <div class="autobiography-section-panel autobiography-lessons-panel about-box mb-4">
            <div class="autobiography-section-panel__header">
                <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                <h4 class="mb-0">Inspirational lessons</h4>
            </div>
            <ul class="autobiography-lessons-list mb-0">
                @foreach($keyLessons as $lesson)
                    <li>{{ $lesson }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($chapterCount > 0)
        <div class="autobiography-chapters-intro about-box mb-4">
            <div class="autobiography-section-panel__header mb-2">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                <h4 class="mb-0">Life chapters</h4>
            </div>
            <p class="text-muted small mb-0">Navigate through {{ $chapterCount }} chapter{{ $chapterCount === 1 ? '' : 's' }} of this life story below.</p>
        </div>
    @endif
@endif
