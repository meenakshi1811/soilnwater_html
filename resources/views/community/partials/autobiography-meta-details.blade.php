@php
    $orderedAutobiographyMeta = \App\Support\CommunityPostFormFields::orderedAutobiographyMetaForDisplay($post)
        ->except([
            'autobiography_type',
            'birth_place',
            'current_location',
            'places_mentioned',
            'key_lessons_learned',
            'related_people',
        ]);
    $autobiographyMetaLabels = \App\Support\CommunityPostFormFields::autobiographyDetailMetaOrder();
    $lifeTimelineCount = count((array) data_get($post->meta, 'life_timeline', []));
    $chapterCount = count($post->bookPages());
    $achievementCount = count((array) data_get($post->meta, 'autobiography_achievements', []));
    $documentCount = count((array) data_get($post->meta, 'autobiography_documents', []));
    $lessonCount = count(array_filter((array) data_get($post->meta, 'key_lessons_learned', [])));
    $relatedPeopleCount = count(array_filter((array) data_get($post->meta, 'related_people', []), fn ($person) => filled(data_get($person, 'name'))));
    $hasStats = $lifeTimelineCount > 0 || $chapterCount > 0 || $achievementCount > 0 || $documentCount > 0 || $lessonCount > 0 || $relatedPeopleCount > 0 || $post->autobiographyAudioUrl();
    $hasContent = $orderedAutobiographyMeta->isNotEmpty() || $hasStats || filled($post->category);
@endphp

@if($hasContent)
    <div class="about-box mt-4 autobiography-meta-panel">
        <h4>{{ $heading ?? 'Autobiography details' }}</h4>

        @if(filled($post->category) || filled(data_get($post->meta, 'autobiography_type')))
            <div class="row g-3 {{ $hasStats || $orderedAutobiographyMeta->isNotEmpty() ? 'mb-3' : '' }}">
                @if(filled($post->category))
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-1">Journey category</strong>
                            <span>{{ $post->category }}</span>
                        </div>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'autobiography_type')))
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-1">Autobiography type</strong>
                            <span>{{ data_get($post->meta, 'autobiography_type') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($hasStats)
            <div class="autobiography-stats-grid mb-3">
                @if($chapterCount > 0)
                    <div class="autobiography-stat-card">
                        <span class="autobiography-stat-card__value">{{ $chapterCount }}</span>
                        <span class="autobiography-stat-card__label">Chapter{{ $chapterCount === 1 ? '' : 's' }}</span>
                    </div>
                @endif
                @if($lifeTimelineCount > 0)
                    <div class="autobiography-stat-card">
                        <span class="autobiography-stat-card__value">{{ $lifeTimelineCount }}</span>
                        <span class="autobiography-stat-card__label">Timeline events</span>
                    </div>
                @endif
                @if($lessonCount > 0)
                    <div class="autobiography-stat-card">
                        <span class="autobiography-stat-card__value">{{ $lessonCount }}</span>
                        <span class="autobiography-stat-card__label">Lessons</span>
                    </div>
                @endif
                @if($achievementCount > 0)
                    <div class="autobiography-stat-card">
                        <span class="autobiography-stat-card__value">{{ $achievementCount }}</span>
                        <span class="autobiography-stat-card__label">Achievements</span>
                    </div>
                @endif
                @if($documentCount > 0)
                    <div class="autobiography-stat-card">
                        <span class="autobiography-stat-card__value">{{ $documentCount }}</span>
                        <span class="autobiography-stat-card__label">Documents</span>
                    </div>
                @endif
                @if($relatedPeopleCount > 0)
                    <div class="autobiography-stat-card">
                        <span class="autobiography-stat-card__value">{{ $relatedPeopleCount }}</span>
                        <span class="autobiography-stat-card__label">Related people</span>
                    </div>
                @endif
                <div class="autobiography-stat-card">
                    <span class="autobiography-stat-card__value">{{ $post->autobiographyAudioUrl() ? 'Yes' : 'No' }}</span>
                    <span class="autobiography-stat-card__label">Audio memories</span>
                </div>
            </div>
        @endif

        @if($orderedAutobiographyMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedAutobiographyMeta as $key => $value)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-1">{{ $autobiographyMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            <span>{!! nl2br(e($value)) !!}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
