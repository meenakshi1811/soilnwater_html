@php
    $post = $post ?? null;
    if (! $post?->isSeniorCitizensForumPost()) {
        return;
    }

    $achievements = array_values((array) data_get($post->meta, 'senior_citizens_forum_achievements', []));
@endphp

@if($achievements !== [])
    <div class="scf-section-panel about-box mt-4 mb-0">
        <div class="scf-section-panel__header">
            <i class="fa-solid fa-award" aria-hidden="true"></i>
            <h4 class="mb-0">Achievements</h4>
        </div>
        <div class="row g-3">
            @foreach($achievements as $achievement)
                <div class="col-md-6">
                    <div class="scf-achievement-card h-100">
                        <div class="d-flex gap-3">
                            @if(filled(data_get($achievement, 'photo.url')))
                                <img src="{{ data_get($achievement, 'photo.url') }}" alt="{{ data_get($achievement, 'award_name', 'Achievement') }}" class="scf-achievement-card__image rounded">
                            @else
                                <div class="scf-achievement-card__icon" aria-hidden="true">
                                    <i class="fa-solid fa-trophy"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="h6 mb-1">{{ data_get($achievement, 'award_name', 'Achievement') }}</h5>
                                @if(filled(data_get($achievement, 'year')))
                                    <div class="scf-achievement-card__year mb-2">{{ data_get($achievement, 'year') }}</div>
                                @endif
                                @if(filled(data_get($achievement, 'description')))
                                    <p class="small mb-2">{{ data_get($achievement, 'description') }}</p>
                                @endif
                                @if(filled(data_get($achievement, 'certificate.url')))
                                    <a href="{{ data_get($achievement, 'certificate.url') }}" target="_blank" rel="noopener" class="scf-certificate-link">
                                        <i class="fa-solid fa-file-certificate" aria-hidden="true"></i>
                                        {{ data_get($achievement, 'certificate.name', 'View certificate') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
