@php
    $localVoiceEngagement = $localVoiceEngagement ?? [
        'supports_count' => 0,
        'follows_count' => 0,
        'user_supported' => false,
        'user_following' => false,
    ];
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canEngage = auth()->check() && $post->isPubliclyVisible() && ! $isAuthor;
@endphp

@if($post->isLocalVoicesPost() && $post->isPubliclyVisible() && ($post->allowsLocalVoiceSupport() || $post->allowsLocalVoiceFollow()))
    <section class="report-community-panel about-box mt-4" id="localVoiceCommunityActions">
        <div class="report-community-panel__header">
            <div>
                <span class="report-community-panel__kicker">Local Voices Community Features</span>
                <h4 class="mb-1">Stand with your community</h4>
                <p class="text-muted mb-0">Support this local voice and follow updates — unique SoilnWater civic engagement.</p>
            </div>
            <div class="report-community-panel__stats">
                @if($post->allowsLocalVoiceSupport())
                    <span class="report-community-panel__stat"><strong data-local-voice-stat="supports">{{ number_format($localVoiceEngagement['supports_count']) }}</strong> supporters</span>
                @endif
                @if($post->allowsLocalVoiceFollow())
                    <span class="report-community-panel__stat"><strong data-local-voice-stat="follows">{{ number_format($localVoiceEngagement['follows_count']) }}</strong> following</span>
                @endif
            </div>
        </div>

        <div class="report-community-panel__grid report-community-panel__grid--three">
            @if($post->allowsLocalVoiceSupport())
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--support">
                        <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">I Support This</h5>
                        <p class="text-muted small mb-3">Show community backing for this local voice or initiative.</p>
                        @auth
                            @if($canEngage)
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $localVoiceEngagement['user_supported'] ? 'btn-success' : 'btn-outline-success' }} js-local-voice-engagement-toggle"
                                    data-url="{{ route('community.local-voice-engagement.support', $post) }}"
                                    data-action="support"
                                    data-active="{{ $localVoiceEngagement['user_supported'] ? '1' : '0' }}"
                                >
                                    <i class="fa-solid fa-hand-holding-heart me-1" aria-hidden="true"></i>
                                    <span class="js-local-voice-action-label">{{ $localVoiceEngagement['user_supported'] ? 'Supported' : 'I support this' }}</span>
                                </button>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Your post</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to support</a>
                        @endauth
                    </div>
                </div>
            @endif

            @if($post->allowsLocalVoiceFollow())
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--follow">
                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">Follow This Issue</h5>
                        <p class="text-muted small mb-3">Subscribe to updates when this local voice progresses.</p>
                        @auth
                            @if($canEngage)
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $localVoiceEngagement['user_following'] ? 'btn-success' : 'btn-outline-success' }} js-local-voice-engagement-toggle"
                                    data-url="{{ route('community.local-voice-engagement.follow', $post) }}"
                                    data-action="follow"
                                    data-active="{{ $localVoiceEngagement['user_following'] ? '1' : '0' }}"
                                >
                                    <i class="fa-solid fa-bell me-1" aria-hidden="true"></i>
                                    <span class="js-local-voice-action-label">{{ $localVoiceEngagement['user_following'] ? 'Following' : 'Follow issue' }}</span>
                                </button>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Your post</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to follow</a>
                        @endauth
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
