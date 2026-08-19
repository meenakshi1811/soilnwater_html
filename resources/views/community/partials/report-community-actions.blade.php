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

@if($post->isReportContent() && $post->isPubliclyVisible())
    <section class="report-community-panel about-box mt-4" id="reportCommunityActions">
        <div class="report-community-panel__header">
            <div>
                <span class="report-community-panel__kicker">Community Reporting Features</span>
                <h4 class="mb-1">Help verify and strengthen this report</h4>
                <p class="report-community-panel__intro mb-0">Support credible reporting and stay informed — a unique SoilnWater community workflow.</p>
            </div>
            <div class="report-community-panel__stats">
                <span class="report-community-panel__stat"><strong data-report-stat="supports">{{ number_format($reportEngagement['supports_count']) }}</strong> supports</span>
                <span class="report-community-panel__stat"><strong data-report-stat="agreements">{{ number_format($reportEngagement['agreements_count']) }}</strong> agree</span>
                <span class="report-community-panel__stat"><strong data-report-stat="follows">{{ number_format($reportEngagement['follows_count']) }}</strong> following</span>
            </div>
        </div>

        @if($isAuthor)
            <div class="report-community-panel__author-note">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span>You're viewing your own report. Other community members can support, agree, and follow from here.</span>
            </div>
        @endif

        <div class="report-community-panel__grid report-community-panel__grid--three">
            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--support">
                    <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="report-community-action-card__title">Support This Report</h5>
                    <p class="report-community-action-card__text">Show that this issue matters and should receive attention.</p>
                </div>
                @unless($isAuthor)
                    <div class="report-community-action-card__footer">
                        @auth
                            @if($canEngage)
                                <button
                                    type="button"
                                    class="btn btn-sm w-100 {{ $reportEngagement['user_supported'] ? 'btn-success' : 'btn-outline-success' }} js-report-engagement-toggle"
                                    data-url="{{ route('community.report-engagement.support', $post) }}"
                                    data-action="support"
                                    data-active="{{ $reportEngagement['user_supported'] ? '1' : '0' }}"
                                >
                                    <i class="fa-solid fa-hand-holding-heart me-1" aria-hidden="true"></i>
                                    <span class="js-report-action-label">{{ $reportEngagement['user_supported'] ? 'Supported' : 'Support report' }}</span>
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success w-100">Login to support</a>
                        @endauth
                    </div>
                @endunless
            </div>

            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--agree">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="report-community-action-card__title">I Agree</h5>
                    <p class="report-community-action-card__text">Confirm that you agree with the observations shared in this report.</p>
                </div>
                @unless($isAuthor)
                    <div class="report-community-action-card__footer">
                        @auth
                            @if($canEngage)
                                <button
                                    type="button"
                                    class="btn btn-sm w-100 {{ $reportEngagement['user_agreed'] ? 'btn-success' : 'btn-outline-success' }} js-report-engagement-toggle"
                                    data-url="{{ route('community.report-engagement.agree', $post) }}"
                                    data-action="agree"
                                    data-active="{{ $reportEngagement['user_agreed'] ? '1' : '0' }}"
                                >
                                    <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>
                                    <span class="js-report-action-label">{{ $reportEngagement['user_agreed'] ? 'Agreed' : 'I agree' }}</span>
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success w-100">Login to agree</a>
                        @endauth
                    </div>
                @endunless
            </div>

            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--follow">
                    <i class="fa-solid fa-bell" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="report-community-action-card__title">Follow Issue</h5>
                    <p class="report-community-action-card__text">Receive portal updates when this report changes or progresses.</p>
                </div>
                @unless($isAuthor)
                    <div class="report-community-action-card__footer">
                        @auth
                            @if($canEngage)
                                <button
                                    type="button"
                                    class="btn btn-sm w-100 {{ $reportEngagement['user_following'] ? 'btn-success' : 'btn-outline-success' }} js-report-engagement-toggle"
                                    data-url="{{ route('community.report-engagement.follow', $post) }}"
                                    data-action="follow"
                                    data-active="{{ $reportEngagement['user_following'] ? '1' : '0' }}"
                                >
                                    <i class="fa-solid fa-bell me-1" aria-hidden="true"></i>
                                    <span class="js-report-action-label">{{ $reportEngagement['user_following'] ? 'Following' : 'Follow issue' }}</span>
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success w-100">Login to follow</a>
                        @endauth
                    </div>
                @endunless
            </div>
        </div>
    </section>
@endif
