@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $reportEngagement = $reportEngagement ?? ['supports_count' => 0, 'agreements_count' => 0, 'follows_count' => 0];
    $verificationCount = (int) ($reportEngagement['agreements_count'] ?? 0);
    $capabilities = [
        ['label' => 'Support campaign', 'enabled' => (bool) data_get($post->meta, 'community_issue_allow_campaign', true), 'icon' => 'fa-people-group'],
        ['label' => 'I support this issue', 'enabled' => $post->allowsCommunityIssueSupport(), 'icon' => 'fa-hand-holding-heart'],
        ['label' => 'Community verification', 'enabled' => $post->allowsCommunityIssueVerification(), 'icon' => 'fa-circle-check'],
        ['label' => 'Follow issue', 'enabled' => $post->allowsCommunityIssueFollow(), 'icon' => 'fa-bell'],
        ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
        ['label' => 'Add evidence', 'enabled' => (bool) $post->allow_feedback, 'icon' => 'fa-camera'],
        ['label' => 'Suggestions', 'enabled' => (bool) $post->allow_suggestions, 'icon' => 'fa-lightbulb'],
        ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
        ['label' => 'Poll', 'enabled' => (bool) $post->allow_poll, 'icon' => 'fa-square-poll-vertical'],
    ];
@endphp

<div class="community-news-rail__community-issues-extras" aria-label="Community issue location and extras">
    @include('community.partials.portal-rail-location-card', [
        'post' => $post,
        'title' => 'Location',
        'mapTitle' => 'Community issue location map',
        'landmark' => data_get($post->meta, 'location_landmark'),
    ])

    @include('community.partials.community-issues-evidence', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.community-issues-community-actions', [
        'post' => $post,
        'reportEngagement' => $reportEngagement,
        'railLayout' => true,
    ])

    @include('community.partials.community-issues-suggested-solution', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.community-issues-support-requests', [
        'post' => $post,
        'railLayout' => true,
    ])

    @include('community.partials.community-issues-additional-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
        <div class="community-detail-card__head">
            <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
            <div>
                <h4 class="community-detail-card__title">Community actions enabled</h4>
            </div>
        </div>
        <div class="ci-capability-grid ci-capability-grid--sidebar">
            @foreach($capabilities as $capability)
                <span class="ci-capability-pill {{ $capability['enabled'] ? 'is-on' : 'is-off' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>{{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    @if($post->allowsCommunityIssueVerification() && $verificationCount > 0)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="alert alert-success py-2 px-3 mb-0">
                <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>
                Verified by <strong>{{ number_format($verificationCount) }}</strong> {{ \Illuminate\Support\Str::plural('resident', $verificationCount) }}
            </div>
        </div>
    @endif

    <div class="community-news-rail__card community-news-rail__card--detail">
        <h4 class="community-detail-card__title h6 mb-2">Explore more civic issues</h4>
        <p class="small text-muted mb-3">View the SoilnWater Issues Hub heat map, dashboard, and community champions.</p>
        <a href="{{ route('community.community-issues.index') }}" class="btn btn-outline-danger btn-sm w-100">
            <i class="fa-solid fa-map-location-dot me-1"></i>Open Issues Hub
        </a>
    </div>
</div>
