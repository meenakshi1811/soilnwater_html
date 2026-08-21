@php
    $railLayout = $railLayout ?? false;
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
    $allowsSupport = $post->allowsCommunityIssueSupport();
    $allowsVerification = $post->allowsCommunityIssueVerification();
    $allowsFollow = $post->allowsCommunityIssueFollow();
    $allowsCampaign = (bool) data_get($post->meta, 'community_issue_allow_campaign', true);
    $showPanel = $post->isCommunityIssuesPost()
        && $post->isPubliclyVisible()
        && ($allowsSupport || $allowsVerification || $allowsFollow);
@endphp

@if($showPanel)
    <section @class([
        'report-community-panel about-box mt-4 community-issues-community-panel' => ! $railLayout,
        'community-issues-community-panel community-issues-community-panel--rail' => $railLayout,
    ]) id="communityIssuesCommunityActions">
        <div @class([
            'report-community-panel__header' => ! $railLayout,
            'community-issues-community-panel__rail-head' => $railLayout,
        ])>
            <div>
                <span class="report-community-panel__kicker">Community Issues · Civic engagement</span>
                @if($railLayout)
                    <h4 class="community-detail-card__title mb-1">Stand with your community</h4>
                @else
                    <h4 class="mb-1">Stand with your community</h4>
                @endif
                <p class="text-muted small mb-0">Support this issue, confirm what you see, and follow resolution updates.</p>
            </div>
            @if($allowsSupport || $allowsVerification || $allowsFollow)
                <div @class([
                    'report-community-panel__stats' => ! $railLayout,
                    'community-issues-community-panel__rail-stats' => $railLayout,
                ])>
                @if($allowsSupport)
                    <span class="report-community-panel__stat">
                        <strong data-report-stat="supports">{{ number_format($reportEngagement['supports_count']) }}</strong>
                        {{ $allowsCampaign ? 'supporters' : 'supports' }}
                    </span>
                @endif
                @if($allowsVerification)
                    <span class="report-community-panel__stat"><strong data-report-stat="agreements">{{ number_format($reportEngagement['agreements_count']) }}</strong> verified</span>
                @endif
                @if($allowsFollow)
                    <span class="report-community-panel__stat"><strong data-report-stat="follows">{{ number_format($reportEngagement['follows_count']) }}</strong> following</span>
                @endif
            </div>
            @endif
        </div>

        @if($allowsCampaign && $allowsSupport && ($reportEngagement['supports_count'] ?? 0) > 0)
            <p class="mb-3">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="fa-solid fa-people-group me-1" aria-hidden="true"></i>
                    {{ number_format($reportEngagement['supports_count']) }} community {{ \Illuminate\Support\Str::plural('supporter', $reportEngagement['supports_count']) }}
                </span>
            </p>
        @endif

        <div @class([
            'report-community-panel__grid report-community-panel__grid--three' => ! $railLayout,
            'report-community-panel__grid report-community-panel__grid--rail' => $railLayout,
        ])>
            @if($allowsSupport)
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--support">
                        <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">{{ $railLayout ? 'Support' : 'I support this issue' }}</h5>
                        <p class="text-muted small {{ $railLayout ? 'mb-2' : 'mb-3' }}">{{ $railLayout ? 'Show this issue deserves attention.' : 'Show authorities and neighbours that this issue deserves attention.' }}</p>
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
                                    <span class="js-report-action-label">{{ $reportEngagement['user_supported'] ? 'Supported' : 'I support this issue' }}</span>
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

            @if($allowsVerification)
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--agree">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">Confirm issue</h5>
                        <p class="text-muted small {{ $railLayout ? 'mb-2' : 'mb-3' }}">{{ $railLayout ? 'Verify you observed this in your area.' : 'Verify that you have observed this issue in your community.' }}</p>
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
                                    <span class="js-report-action-label">{{ $reportEngagement['user_agreed'] ? 'Confirmed' : 'Confirm issue' }}</span>
                                </button>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Your post</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to confirm</a>
                        @endauth
                    </div>
                </div>
            @endif

            @if($allowsFollow)
                <div class="report-community-action-card">
                    <div class="report-community-action-card__icon report-community-action-card__icon--follow">
                        <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    </div>
                    <div class="report-community-action-card__body">
                        <h5 class="mb-1">Follow issue</h5>
                        <p class="text-muted small {{ $railLayout ? 'mb-2' : 'mb-3' }}">{{ $railLayout ? 'Get updates when status changes.' : 'Subscribe to updates when status or resolution details change.' }}</p>
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
                                    <span class="js-report-action-label">{{ $reportEngagement['user_following'] ? 'Following' : 'Follow issue' }}</span>
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
