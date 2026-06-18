@if($post->content_type === 'autobiography')
    @php
        $achievements = array_values((array) data_get($post->meta, 'autobiography_achievements', []));
        $documents = array_values((array) data_get($post->meta, 'autobiography_documents', []));
        $relatedPeople = array_values(array_filter((array) data_get($post->meta, 'related_people', []), fn ($person) => filled(data_get($person, 'name'))));
    @endphp

    @if($achievements !== [])
        <div class="autobiography-section-panel about-box mt-4 mb-0">
            <div class="autobiography-section-panel__header">
                <i class="fa-solid fa-award" aria-hidden="true"></i>
                <h4 class="mb-0">Achievements</h4>
            </div>
            <div class="row g-3">
                @foreach($achievements as $achievement)
                    <div class="col-md-6">
                        <div class="autobiography-achievement-card h-100">
                            <div class="d-flex gap-3">
                                @if(filled(data_get($achievement, 'image.url')))
                                    <img src="{{ data_get($achievement, 'image.url') }}" alt="{{ data_get($achievement, 'award_name', 'Achievement') }}" class="autobiography-achievement-card__image rounded">
                                @else
                                    <div class="autobiography-achievement-card__icon" aria-hidden="true">
                                        <i class="fa-solid fa-trophy"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h5 class="h6 mb-1">{{ data_get($achievement, 'award_name', 'Achievement') }}</h5>
                                    @if(filled(data_get($achievement, 'year')))
                                        <div class="autobiography-achievement-card__year mb-2">{{ data_get($achievement, 'year') }}</div>
                                    @endif
                                    @if(filled(data_get($achievement, 'description')))
                                        <p class="small mb-0">{{ data_get($achievement, 'description') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($documents !== [])
        <div class="autobiography-section-panel about-box mt-4 mb-0">
            <div class="autobiography-section-panel__header">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <h4 class="mb-0">Supporting documents</h4>
            </div>
            <div class="row g-3">
                @foreach($documents as $document)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="autobiography-document-card text-decoration-none h-100 d-block">
                            <i class="fa-solid fa-file-arrow-down autobiography-document-card__icon" aria-hidden="true"></i>
                            <span class="autobiography-document-card__name">{{ data_get($document, 'name', 'Document') }}</span>
                            <span class="autobiography-document-card__action">Open file</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($relatedPeople !== [])
        <div class="autobiography-section-panel about-box mt-4 mb-0">
            <div class="autobiography-section-panel__header">
                <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                <h4 class="mb-0">Related people</h4>
            </div>
            <div class="row g-3">
                @foreach($relatedPeople as $person)
                    <div class="col-md-6 col-lg-4">
                        <div class="autobiography-person-card h-100">
                            <div class="autobiography-person-card__avatar" aria-hidden="true">
                                {{ strtoupper(substr((string) data_get($person, 'name'), 0, 1)) }}
                            </div>
                            <div>
                                <div class="autobiography-person-card__name">{{ data_get($person, 'name') }}</div>
                                @if(filled(data_get($person, 'relationship')))
                                    <div class="autobiography-person-card__relationship">{{ data_get($person, 'relationship') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
