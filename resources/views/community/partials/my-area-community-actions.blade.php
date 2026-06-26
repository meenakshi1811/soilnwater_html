@php
    $reportEngagement = $reportEngagement ?? [
        'supports_count' => 0,
        'agreements_count' => 0,
        'follows_count' => 0,
        'evidence_count' => 0,
        'user_supported' => false,
        'user_agreed' => false,
        'user_following' => false,
    ];
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canEngage = auth()->check() && $post->isPubliclyVisible() && ! $isAuthor;
@endphp

@if($post->isMyAreaPost() && $post->isPubliclyVisible())
    <section class="report-community-panel about-box mt-4 my-area-community-panel" id="myAreaCommunityActions">
        <div class="report-community-panel__header">
            <div>
                <span class="report-community-panel__kicker">My Area · Civic engagement</span>
                <h4 class="mb-1">Stand with your neighbourhood</h4>
                <p class="text-muted mb-0">Support local issues, verify observations, and follow resolution updates in your area.</p>
            </div>
            <div class="report-community-panel__stats">
                <span class="report-community-panel__stat"><strong data-report-stat="supports">{{ number_format($reportEngagement['supports_count']) }}</strong> supports</span>
                <span class="report-community-panel__stat"><strong data-report-stat="agreements">{{ number_format($reportEngagement['agreements_count']) }}</strong> agree</span>
                <span class="report-community-panel__stat"><strong data-report-stat="follows">{{ number_format($reportEngagement['follows_count']) }}</strong> following</span>
            </div>
        </div>

        <div class="report-community-panel__grid report-community-panel__grid--three">
            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--support">
                    <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="mb-1">Support this post</h5>
                    <p class="text-muted small mb-3">Show neighbours and authorities that this local matter deserves attention.</p>
                    @auth
                        @if($canEngage)
                            <button
                                type="button"
                                class="btn btn-sm {{ $reportEngagement['user_supported'] ? 'btn-success' : 'btn-outline-success' }} js-report-engagement-toggle"
                                data-url="{{ route('community.report-engagement.support', $post) }}"
                                data-action="support"
                                data-active="{{ $reportEngagement['user_supported'] ? '1' : '0' }}"
                            >
                                <i class="fa-solid fa-hand-holding-heart me-1" aria-hidden="true"></i>
                                <span class="js-report-action-label">{{ $reportEngagement['user_supported'] ? 'Supported' : 'I support this' }}</span>
                            </button>
                        @elseif($isAuthor)
                            <span class="badge bg-light text-dark border">Your post</span>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to support</a>
                    @endauth
                </div>
            </div>

            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--agree">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="mb-1">I agree</h5>
                    <p class="text-muted small mb-3">Confirm that you agree with what is described about your local area.</p>
                    @auth
                        @if($canEngage)
                            <button
                                type="button"
                                class="btn btn-sm {{ $reportEngagement['user_agreed'] ? 'btn-success' : 'btn-outline-success' }} js-report-engagement-toggle"
                                data-url="{{ route('community.report-engagement.agree', $post) }}"
                                data-action="agree"
                                data-active="{{ $reportEngagement['user_agreed'] ? '1' : '0' }}"
                            >
                                <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>
                                <span class="js-report-action-label">{{ $reportEngagement['user_agreed'] ? 'Agreed' : 'I agree' }}</span>
                            </button>
                        @elseif($isAuthor)
                            <span class="badge bg-light text-dark border">Your post</span>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to agree</a>
                    @endauth
                </div>
            </div>

            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--follow">
                    <i class="fa-solid fa-bell" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="mb-1">Follow resolution</h5>
                    <p class="text-muted small mb-3">Get portal and email updates when status or details change.</p>
                    @auth
                        @if($canEngage)
                            <button
                                type="button"
                                class="btn btn-sm {{ $reportEngagement['user_following'] ? 'btn-success' : 'btn-outline-success' }} js-report-engagement-toggle"
                                data-url="{{ route('community.report-engagement.follow', $post) }}"
                                data-action="follow"
                                data-active="{{ $reportEngagement['user_following'] ? '1' : '0' }}"
                            >
                                <i class="fa-solid fa-bell me-1" aria-hidden="true"></i>
                                <span class="js-report-action-label">{{ $reportEngagement['user_following'] ? 'Following' : 'Follow updates' }}</span>
                            </button>
                        @elseif($isAuthor)
                            <span class="badge bg-light text-dark border">Your post</span>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to follow</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
@endif
