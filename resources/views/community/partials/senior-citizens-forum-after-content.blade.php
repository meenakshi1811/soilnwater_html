@if($post->isSeniorCitizensForumPost())
    @php
        $adviceToYouth = trim((string) data_get($post->meta, 'senior_citizens_forum_advice_to_youth', ''));
        $achievements = array_values((array) data_get($post->meta, 'senior_citizens_forum_achievements', []));
        $askCommunity = trim((string) data_get($post->meta, 'senior_citizens_forum_ask_community', ''));
        $heritageFields = \App\Support\CommunityContentTaxonomy::seniorCitizensForumFamilyHeritageFields();
        $hasHeritage = collect($heritageFields)->keys()->contains(fn (string $key): bool => filled(data_get($post->meta, $key)));
        $railLayout = $railLayout ?? false;
        $panelClass = $railLayout ? 'community-news-rail__card community-news-rail__card--detail mb-3' : 'about-box mt-4 mb-0';
    @endphp

    @if(filled($adviceToYouth))
        <div class="{{ $railLayout ? 'community-news-rail__card community-news-rail__card--detail mb-3' : 'scf-advice-panel about-box mt-4 mb-0' }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Advice to youth</h3>
                <blockquote class="community-news-rail__quote mb-0">{!! nl2br(e($adviceToYouth)) !!}</blockquote>
            @else
                <div class="scf-advice-panel__kicker">Advice to youth</div>
                <blockquote class="scf-advice-panel__quote">{!! nl2br(e($adviceToYouth)) !!}</blockquote>
            @endif
        </div>
    @endif

    @if($achievements !== [] && ! ($railLayout ?? false))
        @include('community.partials.senior-citizens-forum-achievements-section', ['post' => $post])
    @endif

    @if($hasHeritage)
        <div class="scf-section-panel about-box mt-4 mb-0">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-people-roof" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Family heritage</h4>
                    <p class="text-muted small mb-0">Preserving family and cultural heritage for future generations.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($heritageFields as $fieldKey => $fieldLabel)
                    @if(filled(data_get($post->meta, $fieldKey)))
                        <div class="col-md-6">
                            <div class="scf-heritage-card h-100">
                                <div class="scf-heritage-card__title">{{ $fieldLabel }}</div>
                                <p class="scf-heritage-card__text">{!! nl2br(e(data_get($post->meta, $fieldKey))) !!}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @include('community.partials.senior-citizens-forum-media-sections', ['post' => $post])

    @if(filled($askCommunity))
        <div class="scf-section-panel about-box mt-4 mb-0">
            <div class="scf-section-panel__header">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Ask the community</h4>
                    <p class="text-muted small mb-0">Share your experience or answer the author's question below.</p>
                </div>
            </div>
            <p class="scf-ask-panel__lead mb-3">{{ $askCommunity }}</p>
            @if($post->allow_questions || $post->allow_comments || $post->allow_suggestions)
                <div class="d-flex flex-wrap gap-2">
                    @if($post->allow_questions)
                        <a href="#communityAuthorQuestions" class="btn btn-sm btn-outline-primary">Ask a question</a>
                    @endif
                    @if($post->allow_comments)
                        <a href="#participation-comments" class="btn btn-sm btn-outline-secondary">Join the discussion</a>
                    @endif
                    @if($post->allow_suggestions)
                        <a href="#public-participation" class="btn btn-sm btn-outline-success">Share a suggestion</a>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="scf-section-panel about-box mt-4 mb-0">
        <div class="scf-section-panel__header">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <h4 class="mb-0">Community reactions</h4>
        </div>
        <p class="text-muted small mb-2">Positive reactions only — respond with respect and appreciation.</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumReactionOptions() as $reaction => $icon)
                <span class="badge bg-light text-dark border">
                    <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                </span>
            @endforeach
        </div>
    </div>
@endif
