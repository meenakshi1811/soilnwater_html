@php
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $askCommunity = data_get($post->meta, 'agriculture_ask_community');
    $showPanel = $post->isAgriculturePost()
        && $post->isPubliclyVisible()
        && (
            filled($askCommunity)
            || $post->enablesAgricultureCropDoctor()
            || $post->enablesAgricultureKnowledgeExchange()
        );
@endphp

@if($showPanel)
    <section class="report-community-panel about-box mt-4 ag-community-panel" id="agricultureCommunityActions">
        <div class="report-community-panel__header">
            <div>
                <span class="report-community-panel__kicker">Agriculture · Community support</span>
                <h4 class="mb-1">Help this farmer grow smarter</h4>
                <p class="text-muted mb-0">Share practical advice, answer their question, or support Crop Doctor diagnosis.</p>
            </div>
        </div>

        <div class="report-community-panel__grid report-community-panel__grid--three">
            @if(filled($askCommunity))
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--support">
                        <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">Answer their question</h5>
                        <p class="text-muted small mb-3">“{{ \Illuminate\Support\Str::limit($askCommunity, 120) }}”</p>
                        @auth
                            @if(! $isAuthor && $post->allow_comments)
                                <a href="#communityComments" class="btn btn-sm btn-outline-success">Reply in comments</a>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Your post</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to respond</a>
                        @endauth
                    </div>
                </div>
            @endif

            @if($post->enablesAgricultureKnowledgeExchange())
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--agree">
                        <i class="fa-solid fa-people-arrows" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">Knowledge exchange</h5>
                        <p class="text-muted small mb-3">Compare methods, share local tips, and react with practical feedback.</p>
                        @auth
                            @if(! $isAuthor)
                                <a href="#communityReactionButtons" class="btn btn-sm btn-outline-success">Add a reaction</a>
                            @else
                                <span class="badge bg-light text-dark border">Your post</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to participate</a>
                        @endauth
                    </div>
                </div>
            @endif

            @if($post->enablesAgricultureCropDoctor())
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--follow">
                        <i class="fa-solid fa-stethoscope" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">Crop Doctor</h5>
                        <p class="text-muted small mb-3">
                            @if($post->agricultureNeedsExpertAssistance())
                                Expert assistance requested — agronomists and experienced farmers are especially welcome.
                            @else
                                Help diagnose crop issues with comments, suggestions, or evidence uploads.
                            @endif
                        </p>
                        @auth
                            @if(! $isAuthor && ($post->allow_comments || $post->allow_suggestions))
                                <a href="#communityComments" class="btn btn-sm btn-outline-success">Offer advice</a>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Awaiting community help</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login as expert</a>
                        @endauth
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
